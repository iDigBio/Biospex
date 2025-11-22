<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Jobs\ZooniverseExportBatchResultJob;
use Aws\Sqs\SqsClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Listens for updates from SQS queue and processes them.
 * Handles reconnection attempts and monitoring of the connection health.
 */
class ListenBatchUpdateQueue extends Command
{
    /** @var string Command signature */
    protected $signature = 'batch:listen';

    /** @var string Command description */
    protected $description = 'Robust SQS listener for batch update queue with reconnections and alerts';

    /** @var SqsClient AWS SQS client instance */
    private SqsClient $sqs;

    /** @var bool Indicates if the listener should exit */
    private bool $shouldExit = false;

    /** @var int Number of reconnection attempts */
    private int $reconnectAttempts = 0;

    /** @var int Maximum number of reconnection attempts */
    private int $maxReconnectAttempts = 10;

    /** @var string Admin email address for notifications */
    private string $adminEmail;

    /** @var int Timestamp of the last notification email sent */
    private int $lastEmailSent = 0;

    public function __construct(SqsClient $sqs)
    {
        parent::__construct();
        $this->adminEmail = config('mail.from.address', 'admin@biospex.org');
        $this->sqs = $sqs;
    }

    /**
     * Execute the console command.
     *
     * @return int Command exit code
     */
    public function handle(): int
    {
        try {
            $this->info('Starting Batch Update SQS Listener...');
            $this->validateConfiguration();
            $this->runWorker();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->handleCriticalError('Failed to start batch updates listener', $e);

            return self::FAILURE;
        }
    }

    /**
     * Validate required AWS configuration settings.
     *
     * @throws \RuntimeException When the required configuration is missing
     */
    private function validateConfiguration(): void
    {
        $required = [
            'services.aws.queues.queue_batch_update' => 'AWS_SQS_BATCH_UPDATE_QUEUE',
            'services.aws.export_credentials' => 'AWS_EXPORT_CREDENTIALS',
        ];

        foreach ($required as $key => $env) {
            if (empty(Config::get($key))) {
                throw new \RuntimeException("Missing config: {$key} (env: {$env})");
            }
        }
    }

    /**
     * Run the worker loop.
     */
    private function runWorker(): void
    {
        $this->setupSignalHandlers();
        $idleStart = null;
        $gracePeriod = (int) config('services.aws.batch_idle_grace', 60);

        $this->info('Worker started. Listening...');

        while (true) {
            if (extension_loaded('pcntl')) {
                pcntl_signal_dispatch();
            }

            if ($this->shouldExit) {
                break;
            }

            // Check active batches logic
            if (! $this->hasActiveBatches()) {
                if ($idleStart === null) {
                    $idleStart = time();
                    $this->info("No pending batch messages. Monitoring for {$gracePeriod} seconds before shutdown.");
                } elseif ((time() - $idleStart) > $gracePeriod) {
                    $this->info('No pending batch messages. Shutting down listener.');
                    break;
                }
                sleep(5);

                continue;
            } else {
                if ($idleStart !== null) {
                    $idleStart = null;
                    $this->info('Batch messages detected—continuing polling.');
                }
            }

            try {
                $this->pollSqsSync();
            } catch (\Throwable $e) {
                $this->handleConnectionError($e);
                sleep(min(60, pow(2, $this->reconnectAttempts)));
            }
        }

        $this->info('Shutdown complete');
    }

    /**
     * Setup handlers for system signals (SIGINT, SIGTERM).
     */
    private function setupSignalHandlers(): void
    {
        if (! extension_loaded('pcntl')) {
            return;
        }

        $handler = function () {
            $this->info('Shutdown signal received...');
            $this->shouldExit = true;
        };

        pcntl_signal(SIGINT, $handler);
        pcntl_signal(SIGTERM, $handler);
    }

    /**
     * Poll SQS queue synchronously.
     */
    private function pollSqsSync(): void
    {
        $queueUrl = $this->getQueueUrl('queue_batch_update');

        $result = $this->sqs->receiveMessage([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => 10,
            'WaitTimeSeconds' => 20,
            'VisibilityTimeout' => 60,
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        $messages = $result['Messages'] ?? [];

        if (! empty($messages)) {
            $this->reconnectAttempts = 0;
            $this->info('📦 Received batch of '.count($messages).' messages from SQS');
            $this->processBatchMessages($messages, $queueUrl);
        }
    }

    /**
     * Process a batch of SQS messages.
     *
     * @param  array  $messages  Array of SQS messages
     * @param  string  $queueUrl  URL of the SQS queue
     */
    private function processBatchMessages(array $messages, string $queueUrl): void
    {
        $processedMessages = [];

        foreach ($messages as $message) {
            $messageId = $message['MessageId'] ?? 'unknown';
            $receiveCount = $message['Attributes']['ApproximateReceiveCount'] ?? 1;

            if ($receiveCount > 3) {
                $this->warn("⚠️  Message {$messageId} has been retried {$receiveCount} times");
            }

            try {
                $this->processSingleMessage($message);
                $processedMessages[] = $message;
            } catch (\InvalidArgumentException $e) {
                $this->error("❌ Invalid message format: {$e->getMessage()} (Message ID: {$messageId})");
                $processedMessages[] = $message;
            } catch (\Throwable $e) {
                $this->handleError('Failed to process message in batch', $e, [
                    'message_id' => $messageId,
                    'message_body_preview' => substr($message['Body'] ?? '', 0, 200),
                ]);
                if ($this->isPermanentError($e)) {
                    $processedMessages[] = $message;
                }
            }
        }

        if (! empty($processedMessages)) {
            $this->batchDeleteMessages($queueUrl, $processedMessages);
        }
    }

    /**
     * Process a single SQS message.
     *
     * @param  array  $message  SQS message data
     *
     * @throws \Throwable When processing fails
     */
    private function processSingleMessage(array $message): void
    {
        if (empty($message['Body'])) {
            throw new \InvalidArgumentException('Message body is empty');
        }

        $body = json_decode($message['Body'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON in message body: '.json_last_error_msg());
        }

        if (! is_array($body)) {
            throw new \InvalidArgumentException('Message body must be a JSON object, got: '.gettype($body));
        }

        $this->routeMessage($body);
    }

    /**
     * Determine if an error is permanent and message should be deleted.
     */
    private function isPermanentError(\Throwable $e): bool
    {
        $permanentErrorPatterns = [
            'Unknown function:',
            'Invalid JSON',
            'Missing function',
            'Class.*not found',
        ];

        foreach ($permanentErrorPatterns as $pattern) {
            if (stripos($e->getMessage(), $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Route message to the appropriate job based on the function name.
     *
     * @param  array  $data  Message data
     *
     * @throws \InvalidArgumentException|\Throwable When a function is missing or unknown
     */
    private function routeMessage(array $data): void
    {
        if (! isset($data['function'])) {
            throw new \InvalidArgumentException('Message missing required "function" field');
        }

        $function = $data['function'];

        try {
            match ($function) {
                'BiospexBatchCreator' => $this->dispatchBatchCreatorJob($data),
                default => throw new \InvalidArgumentException("Unknown function: {$function}"),
            };
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch job', [
                'function' => $function,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Dispatch a batch creator job based on message data.
     *
     * @param  array  $data  Message data containing batch processing results
     *
     * @throws \InvalidArgumentException When required fields are missing
     * @throws \RuntimeException When batch processing failed
     */
    private function dispatchBatchCreatorJob(array $data): void
    {
        $status = $data['status'] ?? throw new \InvalidArgumentException('Missing status');
        $downloadId = $data['downloadId'] ?? throw new \InvalidArgumentException('Missing downloadId');

        if ($status === 'failed') {
            $error = $data['error'] ?? 'Unknown error';
            Log::error('BiospexBatchCreator failed', $data);
            throw new \RuntimeException("Batch export failed for download #{$downloadId}: {$error}");
        }

        ZooniverseExportBatchResultJob::dispatch($data);
    }

    /**
     * Handle non-critical errors with logging and notifications.
     *
     * @param  string  $msg  Error message
     * @param  \Throwable|null  $e  Exception if any
     * @param  array  $ctx  Additional context
     */
    private function handleError(string $msg, ?\Throwable $e = null, array $ctx = []): void
    {
        $context = array_merge($ctx, [
            'timestamp' => now()->toISOString(),
            'command' => 'batch:listen',
            'reconnect_attempts' => $this->reconnectAttempts,
        ]);

        if ($e) {
            $context['error'] = $e->getMessage();
            $context['trace'] = $e->getTraceAsString();
            $context['file'] = $e->getFile();
            $context['line'] = $e->getLine();
        }

        Log::error($msg, $context);
        $this->error($msg.($e ? ': '.$e->getMessage() : ''));

        if ($this->shouldSendEmail($msg)) {
            $this->sendEmail($msg, $e, $context);
        }
    }

    /**
     * Handle critical errors with logging and immediate notification.
     *
     * @param  string  $msg  Error message
     * @param  \Throwable  $e  Exception that occurred
     */
    private function handleCriticalError(string $msg, \Throwable $e): void
    {
        $context = [
            'timestamp' => now()->toISOString(),
            'command' => 'batch:listen',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'reconnect_attempts' => $this->reconnectAttempts,
        ];

        Log::critical($msg, $context);
        $this->error("🚨 CRITICAL: {$msg}: {$e->getMessage()}");

        $this->sendEmail($msg, $e, $context, true);
    }

    /**
     * Handle SQS connection errors with exponential backoff.
     *
     * @param  \Throwable  $e  Exception that occurred
     */
    private function handleConnectionError(\Throwable $e): void
    {
        $this->reconnectAttempts++;
        $this->handleError("SQS connection failed (attempt {$this->reconnectAttempts})", $e);

        if ($this->reconnectAttempts > $this->maxReconnectAttempts) {
            $this->handleCriticalError("Max reconnection attempts ({$this->maxReconnectAttempts}) exceeded", $e);
            $this->shouldExit = true;
        }
    }

    /**
     * Get SQS queue URL from queue name.
     *
     * @param  string  $key  Configuration key for queue name
     * @return string SQS queue URL
     */
    private function getQueueUrl(string $key): string
    {
        $name = Config::get("services.aws.queues.{$key}");

        return $this->sqs->getQueueUrl(['QueueName' => $name])['QueueUrl'];
    }

    /**
     * Batch delete messages from SQS queue.
     *
     * @param  string  $queueUrl  URL of the SQS queue
     * @param  array  $messages  Array of messages to delete
     */
    private function batchDeleteMessages(string $queueUrl, array $messages): void
    {
        if (empty($messages)) {
            return;
        }

        $batches = array_chunk($messages, 10);

        foreach ($batches as $batchIndex => $batch) {
            $entries = [];

            foreach ($batch as $index => $message) {
                $entries[] = [
                    'Id' => (string) $index,
                    'ReceiptHandle' => $message['ReceiptHandle'],
                ];
            }

            try {
                $result = $this->sqs->deleteMessageBatch([
                    'QueueUrl' => $queueUrl,
                    'Entries' => $entries,
                ]);

                $successCount = count($result['Successful'] ?? []);
                $this->info("🗑️  Batch deleted {$successCount} messages");

                if (! empty($result['Failed'])) {
                    foreach ($result['Failed'] as $failed) {
                        $this->error("Failed to delete message in batch: {$failed['Code']} - {$failed['Message']}");
                    }
                }
            } catch (\Throwable $e) {
                $this->error('Batch delete failed, falling back to individual deletion: '.$e->getMessage());

                foreach ($batch as $message) {
                    $this->deleteMessage($queueUrl, $message['ReceiptHandle']);
                }
            }
        }
    }

    /**
     * Delete processed message from SQS queue.
     *
     * @param  string  $queueUrl  URL of the SQS queue
     * @param  string  $receipt  Message receipt handle
     */
    private function deleteMessage(string $queueUrl, string $receipt): void
    {
        try {
            $this->sqs->deleteMessage(['QueueUrl' => $queueUrl, 'ReceiptHandle' => $receipt]);
        } catch (\Throwable $e) {
            $this->error('Failed to delete message: '.$e->getMessage());
        }
    }

    /**
     * Determine if an error notification email should be sent.
     *
     * @param  string  $msg  Error message
     * @return bool True if email should be sent
     */
    private function shouldSendEmail(string $msg): bool
    {
        $now = time();
        if (($now - $this->lastEmailSent) < 1800) {
            return false;
        }
        $keywords = ['failed', 'critical', 'connection', 'max attempts'];
        foreach ($keywords as $kw) {
            if (stripos($msg, $kw) !== false) {
                $this->lastEmailSent = $now;

                return true;
            }
        }

        return false;
    }

    /**
     * Send error notification email.
     *
     * @param  string  $msg  Error message
     * @param  \Throwable|null  $e  Exception if any
     * @param  array  $ctx  Additional context
     * @param  bool  $critical  Whether error is critical
     */
    private function sendEmail(string $msg, ?\Throwable $e, array $ctx, bool $critical = false): void
    {
        if (! $this->adminEmail) {
            return;
        }

        try {
            $subject = $critical
                ? '[CRITICAL] Batch Update Queue Listener - '.config('app.name')
                : '[ERROR] Batch Update Queue Listener - '.config('app.name');

            $body = $this->buildEmailBody($msg, $e, $ctx, $critical);

            Mail::raw($body, function ($m) use ($subject) {
                $m->to($this->adminEmail)->subject($subject);
            });

            Log::info('Error notification email sent', ['subject' => $subject]);
        } catch (\Throwable $me) {
            Log::error('Failed to send alert email', [
                'mail_error' => $me->getMessage(),
                'original_error' => $e?->getMessage(),
            ]);
        }
    }

    /**
     * Build detailed error email body.
     */
    private function buildEmailBody(string $msg, ?\Throwable $e, array $ctx, bool $critical): string
    {
        $body = "Batch Update Queue Listener Error Report\n";
        $body .= str_repeat('=', 50)."\n\n";

        if ($critical) {
            $body .= "🚨 CRITICAL ERROR - Immediate attention required!\n\n";
        }

        $body .= 'Time: '.now()->format('Y-m-d H:i:s T')."\n";
        $body .= 'Server: '.php_uname('n')."\n";
        $body .= 'Application: '.config('app.name')."\n";
        $body .= 'Environment: '.config('app.env')."\n";
        $body .= 'Reconnect Attempts: '.$this->reconnectAttempts."\n\n";

        $body .= "Error Message:\n{$msg}\n\n";

        if ($e) {
            $body .= "Exception Details:\n";
            $body .= 'Type: '.get_class($e)."\n";
            $body .= 'Message: '.$e->getMessage()."\n";
            $body .= 'File: '.$e->getFile().':'.$e->getLine()."\n\n";

            $body .= "Stack Trace:\n".$e->getTraceAsString()."\n\n";
        }

        if (! empty($ctx)) {
            $body .= "Context:\n";
            $body .= json_encode($ctx, JSON_PRETTY_PRINT)."\n\n";
        }

        $body .= "Configuration:\n";
        $body .= 'Queue: '.Config::get('services.aws.queues.queue_batch_update')."\n";
        $body .= 'Region: '.Config::get('services.aws.region', 'us-east-1')."\n";

        return $body;
    }

    /**
     * Check if the batch queue has pending messages.
     *
     * @return bool True if messages > 0
     */
    private function hasActiveBatches(): bool
    {
        try {
            $queueUrl = $this->getQueueUrl('queue_batch_update');
            $result = $this->sqs->getQueueAttributes([
                'QueueUrl' => $queueUrl,
                'AttributeNames' => ['ApproximateNumberOfMessages'],
            ]);

            return (int) ($result['Attributes']['ApproximateNumberOfMessages'] ?? 0) > 0;
        } catch (\Throwable $e) {
            $this->warn('Failed to check queue attributes: '.$e->getMessage());

            return true;  // Assume active on error to avoid false shutdown
        }
    }
}
