<?php

namespace App\Services;

use Aws\Sqs\SqsClient;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SqsListenerService
{
    private SqsClient $sqs;

    private string $adminEmail;

    private bool $shouldExit = false;

    private int $reconnectAttempts = 0;

    private int $maxReconnectAttempts = 10;

    private int $lastEmailSent = 0;

    public function __construct(SqsClient $sqs)
    {
        $this->sqs = $sqs;
        $this->adminEmail = config('mail.from.address', 'admin@biospex.org');
    }

    /**
     * Run the main listener loop.
     *
     * @param  Closure  $idleChecker  Callback to check for active work (returns bool)
     * @param  string  $queueKey  Config key for the queue name (e.g., 'reconcile_update')
     * @param  string  $graceKey  Config key for idle grace period (e.g., 'services.aws.reconciliation_idle_grace')
     * @param  Closure  $routeCallback  Callback to route/process a decoded message body (receives array $body, returns void or throws)
     * @param  Command  $command  The console command instance (for logging/output)
     */
    public function run(
        Closure $idleChecker,
        string $queueKey,
        string $graceKey,
        Closure $routeCallback,
        Command $command
    ): void {
        $this->setupSignalHandlers($command);
        $idleStart = null;
        $gracePeriod = (int) config($graceKey, 60);

        $command->info('Worker started. Listening...');

        while (true) {
            if (extension_loaded('pcntl')) {
                pcntl_signal_dispatch();
            }

            if ($this->shouldExit) {
                break;
            }

            // Check idle logic
            if (! $idleChecker()) {
                if ($idleStart === null) {
                    $idleStart = time();
                    $command->info("No pending messages. Monitoring for {$gracePeriod} seconds before shutdown.");
                } elseif ((time() - $idleStart) > $gracePeriod) {
                    $command->info('No pending messages. Shutting down listener.');
                    break;
                }
                sleep(5);

                continue;
            } else {
                if ($idleStart !== null) {
                    $idleStart = null;
                    $command->info('Messages detected—continuing polling.');
                }
            }

            try {
                $this->pollAndProcess($queueKey, $routeCallback, $command);
                $this->reconnectAttempts = 0;
            } catch (Throwable $e) {
                $this->handleConnectionError($e, $command);
                sleep(min(60, pow(2, $this->reconnectAttempts)));
            }
        }

        $command->info('Shutdown complete');
    }

    /**
     * Poll SQS and process messages.
     */
    private function pollAndProcess(string $queueKey, Closure $routeCallback, Command $command): void
    {
        $queueUrl = $this->getQueueUrl($queueKey);

        $result = $this->sqs->receiveMessage([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => 10,
            'WaitTimeSeconds' => 20,
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        $messages = $result['Messages'] ?? [];

        if (! empty($messages)) {
            $this->processBatchMessages($messages, $queueUrl, $routeCallback, $command);
        }
    }

    /**
     * Process a batch of messages.
     */
    private function processBatchMessages(
        array $messages,
        string $queueUrl,
        Closure $routeCallback,
        Command $command
    ): void {
        $processedMessages = [];

        foreach ($messages as $message) {
            $messageId = $message['MessageId'] ?? 'unknown';
            $receiveCount = $message['Attributes']['ApproximateReceiveCount'] ?? 1;

            if ($receiveCount > 3) {
                $command->warn("⚠️  Message {$messageId} has been retried {$receiveCount} times");
            }

            try {
                $this->processSingleMessage($message, $routeCallback);
                $processedMessages[] = $message;
            } catch (Throwable $e) {
                if ($e instanceof \InvalidArgumentException) {
                    $command->error("❌ Invalid message format: {$e->getMessage()} (Message ID: {$messageId})");
                } else {
                    $this->handleError('Failed to process message in batch', $e, [
                        'message_id' => $messageId,
                        'message_body_preview' => substr($message['Body'] ?? '', 0, 200),
                    ], $command);
                }

                if ($this->isPermanentError($e)) {
                    $processedMessages[] = $message;
                }
            }
        }

        if (! empty($processedMessages)) {
            $this->batchDeleteMessages($queueUrl, $processedMessages, $command);
        }
    }

    /**
     * Process a single message.
     */
    private function processSingleMessage(array $message, Closure $routeCallback): void
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

        $routeCallback($body);
    }

    /**
     * Determine if an error is permanent and message should be deleted.
     */
    private function isPermanentError(Throwable $e): bool
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
     * Setup signal handlers on the command.
     */
    private function setupSignalHandlers(Command $command): void
    {
        if (! extension_loaded('pcntl')) {
            return;
        }

        $handler = function () {
            $this->shouldExit = true;
        };

        pcntl_signal(SIGINT, $handler);
        pcntl_signal(SIGTERM, $handler);
    }

    /**
     * Get queue URL from config key.
     */
    private function getQueueUrl(string $key): string
    {
        $name = Config::get("services.aws.queues.{$key}");

        return $this->sqs->getQueueUrl(['QueueName' => $name])['QueueUrl'];
    }

    /**
     * Check if queue has pending messages.
     */
    public function hasPendingMessages(string $queueKey): bool
    {
        try {
            $queueUrl = $this->getQueueUrl($queueKey);
            $result = $this->sqs->getQueueAttributes([
                'QueueUrl' => $queueUrl,
                'AttributeNames' => ['ApproximateNumberOfMessages'],
            ]);

            return (int) ($result['Attributes']['ApproximateNumberOfMessages'] ?? 0) > 0;
        } catch (Throwable $e) {
            Log::warning('Failed to check queue attributes: '.$e->getMessage());

            return true;  // Assume pending on error to avoid false shutdown
        }
    }

    /**
     * Batch delete messages.
     */
    private function batchDeleteMessages(string $queueUrl, array $messages, Command $command): void
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

                if (! empty($result['Failed'])) {
                    foreach ($result['Failed'] as $failed) {
                        $command->error("Failed to delete message in batch: {$failed['Code']} - {$failed['Message']}");
                    }
                }
            } catch (Throwable $e) {
                $command->error('Batch delete failed, falling back to individual deletion: '.$e->getMessage());

                foreach ($batch as $message) {
                    $this->deleteMessage($queueUrl, $message['ReceiptHandle'], $command);
                }
            }
        }
    }

    /**
     * Delete single message.
     */
    private function deleteMessage(string $queueUrl, string $receipt, Command $command): void
    {
        try {
            $this->sqs->deleteMessage(['QueueUrl' => $queueUrl, 'ReceiptHandle' => $receipt]);
        } catch (Throwable $e) {
            $command->error('Failed to delete message: '.$e->getMessage());
        }
    }

    /**
     * Handle non-critical errors.
     */
    public function handleError(string $msg, ?Throwable $e, array $ctx, Command $command): void
    {
        $context = array_merge($ctx, [
            'timestamp' => now()->toISOString(),
            'service' => 'SqsListenerService',
            'reconnect_attempts' => $this->reconnectAttempts,
        ]);

        if ($e) {
            $context['error'] = $e->getMessage();
            $context['trace'] = $e->getTraceAsString();
            $context['file'] = $e->getFile();
            $context['line'] = $e->getLine();
        }

        Log::error($msg, $context);
        $command->error($msg.($e ? ': '.$e->getMessage() : ''));

        if ($this->shouldSendEmail($msg)) {
            $this->sendEmail($msg, $e, $context, false, $command);
        }
    }

    /**
     * Handle critical errors.
     */
    public function handleCriticalError(string $msg, Throwable $e, Command $command): void
    {
        $context = [
            'timestamp' => now()->toISOString(),
            'service' => 'SqsListenerService',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'reconnect_attempts' => $this->reconnectAttempts,
        ];

        Log::critical($msg, $context);
        $command->error("🚨 CRITICAL: {$msg}: {$e->getMessage()}");

        $this->sendEmail($msg, $e, $context, true, $command);
    }

    /**
     * Handle connection errors with backoff.
     */
    public function handleConnectionError(Throwable $e, Command $command): void
    {
        $this->reconnectAttempts++;
        $this->handleError("SQS connection failed (attempt {$this->reconnectAttempts})", $e, [], $command);

        if ($this->reconnectAttempts > $this->maxReconnectAttempts) {
            $this->handleCriticalError("Max reconnection attempts ({$this->maxReconnectAttempts}) exceeded", $e, $command);
            $this->shouldExit = true;
        }
    }

    /**
     * Check if email should be sent (throttled).
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
     * Send email notification.
     */
    public function sendEmail(string $msg, ?Throwable $e, array $ctx, bool $critical, Command $command): void
    {
        if (! $this->adminEmail) {
            return;
        }

        try {
            $subject = $critical
                ? '[CRITICAL] SQS Listener Service - '.config('app.name')
                : '[ERROR] SQS Listener Service - '.config('app.name');

            $body = $this->buildEmailBody($msg, $e, $ctx, $critical);

            Mail::raw($body, function ($m) use ($subject) {
                $m->to($this->adminEmail)->subject($subject);
            });

        } catch (Throwable $me) {
            Log::error('Failed to send alert email', [
                'mail_error' => $me->getMessage(),
                'original_error' => $e?->getMessage(),
            ]);
        }
    }

    /**
     * Build email body.
     */
    private function buildEmailBody(string $msg, ?Throwable $e, array $ctx, bool $critical): string
    {
        $body = "SQS Listener Service Error Report\n";
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
        $body .= 'Region: '.Config::get('services.aws.region', 'us-east-1')."\n";

        return $body;
    }
}
