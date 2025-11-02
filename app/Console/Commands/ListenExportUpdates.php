<?php

namespace App\Console\Commands;

use App\Jobs\ExportImageUpdateJob;
use App\Jobs\ZooniverseExportZipResultJob;
use Aws\Sqs\SqsClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use JetBrains\PhpStorm\NoReturn;

/**
 * Listens for export updates from SQS queue and processes them.
 * Handles reconnection attempts and monitoring of the connection health.
 */
class ListenExportUpdates extends Command
{
    /** @var string Command signature */
    protected $signature = 'export:listen-updates';

    /** @var string Command description */
    protected $description = 'Robust SQS listener for export updates with reconnections and alerts';

    /** @var SqsClient AWS SQS client instance */
    private SqsClient $sqs;

    /** @var \React\EventLoop\LoopInterface Event loop instance */
    private \React\EventLoop\LoopInterface $loop;

    /** @var bool Indicates if the listener is shutting down */
    private bool $isShuttingDown = false;

    /** @var int Number of reconnection attempts */
    private int $reconnectAttempts = 0;

    /** @var int Maximum number of reconnection attempts */
    private int $maxReconnectAttempts = 10;

    /** @var float Delay between reconnection attempts */
    private float $reconnectDelay = 1.0;

    /** @var int|null Timestamp of last received message */
    private ?int $lastMessageTime;

    /** @var mixed Timer for heartbeat checks */
    private mixed $heartbeatTimer;

    /** @var string Admin email address for notifications */
    private string $adminEmail;

    /** @var int Timestamp of last notification email sent */
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
        if (config('app.env') !== 'production' && config('app.env') !== 'local') {

            return self::SUCCESS;
        }

        try {
            $this->info('Starting Export Updates SQS Listener...');
            $this->validateConfiguration();
            $this->initializeListener();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->handleCriticalError('Failed to start export updates listener', $e);

            return self::FAILURE;
        }
    }

    /**
     * Validate required AWS configuration settings.
     *
     * @throws \RuntimeException When required configuration is missing
     */
    private function validateConfiguration(): void
    {
        $required = [
            'services.aws.queue_updates' => 'AWS_SQS_UPDATES_QUEUE',
            'services.aws.export_credentials' => 'AWS_EXPORT_CREDENTIALS',
        ];

        foreach ($required as $key => $env) {
            if (empty(Config::get($key))) {
                throw new \RuntimeException("Missing config: {$key} (env: {$env})");
            }
        }
    }

    /**
     * Initialize the SQS listener and event loop.
     *
     * @throws \Throwable On initialization failure
     */
    private function initializeListener(): void
    {
        $this->loop = \React\EventLoop\Loop::get();
        $this->lastMessageTime = time();
        $this->setupHeartbeat();
        $this->setupSignalHandlers();
        $this->connectToSqs();

        $this->info('Event loop started. Listening...');
        $this->loop->run();
    }

    /**
     * Setup periodic heartbeat to monitor connection health.
     */
    private function setupHeartbeat(): void
    {
        $this->heartbeatTimer = $this->loop->addPeriodicTimer(30, function () {
            if ($this->isShuttingDown) {
                return;
            }

            $now = time();
            if (($now - $this->lastMessageTime) > 300) { // 5 min
                $this->warn('No SQS messages in 5 minutes — checking connection...');
                $this->forceReconnection();
            }
        });
    }

    /**
     * Setup handlers for system signals (SIGINT, SIGTERM).
     */
    private function setupSignalHandlers(): void
    {
        if (! extension_loaded('pcntl')) {
            return;
        }

        foreach ([SIGINT, SIGTERM] as $signal) {
            $this->loop->addSignal($signal, function () {
                $this->info('Shutdown signal received...');
                $this->shutdown(0);
            });
        }
    }

    /**
     * Establish connection to AWS SQS service.
     */
    private function connectToSqs(): void
    {
        if ($this->isShuttingDown) {
            return;
        }

        $this->lastMessageTime = time();
        $this->info('Connecting to SQS (attempt '.($this->reconnectAttempts + 1).')...');

        $this->pollSqs();
    }

    /**
     * Poll SQS queue for messages in batches.
     */
    private function pollSqs(): void
    {
        $queueUrl = $this->getQueueUrl('queue_updates');

        $this->sqs->receiveMessageAsync([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => 10, // Get up to 10 messages in batch
            'WaitTimeSeconds' => 20,
            'VisibilityTimeout' => 30,
            'AttributeNames' => ['ApproximateReceiveCount'], // Track retry attempts
        ])->then(
            function ($response) {
                $this->reconnectAttempts = 0;
                $this->reconnectDelay = 1.0;

                if (! empty($response['Messages'])) {
                    $this->lastMessageTime = time();
                    $messageCount = count($response['Messages']);

                    $this->info("📦 Received batch of {$messageCount} messages from SQS");

                    $this->processBatchMessages($response['Messages'], $this->getQueueUrl('queue_updates'));
                }

                if (! $this->isShuttingDown) {
                    $this->loop->addTimer(0.1, function () {
                        $this->pollSqs();
                    });
                }
            },
            function ($e) {
                $this->handleConnectionError($e);
            }
        );
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

            // Log if message has been retried multiple times
            if ($receiveCount > 3) {
                $this->warn("⚠️  Message {$messageId} has been retried {$receiveCount} times");
            }

            try {
                $this->processSingleMessage($message);
                $processedMessages[] = $message;

            } catch (\InvalidArgumentException $e) {
                // Message format errors - delete to avoid infinite retries
                $this->error("❌ Invalid message format: {$e->getMessage()} (Message ID: {$messageId})");
                $processedMessages[] = $message; // Add to processed for deletion

            } catch (\Throwable $e) {
                $this->handleError('Failed to process message in batch', $e, [
                    'message_id' => $messageId,
                    'message_body_preview' => substr($message['Body'] ?? '', 0, 200),
                ]);

                // Add to processed if it's a permanent error that should be deleted
                if ($this->isPermanentError($e)) {
                    $processedMessages[] = $message;
                }
            }
        }

        // Batch deletes successfully processed messages
        if (! empty($processedMessages)) {
            $this->batchDeleteMessages($queueUrl, $processedMessages);
        }
    }

    /**
     * Process a single SQS message (extracted for batch processing).
     *
     * @param  array  $message  SQS message data
     *
     * @throws \Throwable When processing fails
     */
    private function processSingleMessage(array $message): void
    {
        $messageId = $message['MessageId'] ?? 'unknown';

        // Validate message has body
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
        // Delete messages that have permanent issues
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
     * Route message to appropriate job based on function name.
     *
     * @param  array  $data  Message data
     *
     * @throws \InvalidArgumentException|\Throwable When function is missing or unknown
     */
    private function routeMessage(array $data): void
    {
        // Validate required fields
        if (! isset($data['function'])) {
            throw new \InvalidArgumentException('Message missing required "function" field');
        }

        $function = $data['function'];

        Log::info('Processing SQS message', [
            'function' => $function,
            'data_keys' => array_keys($data),
            'command' => 'export:listen-updates',
        ]);

        try {
            match ($function) {
                'BiospexImageProcess' => $this->dispatchImageProcessJob($data),
                'BiospexZipCreator' => $this->dispatchZipCreatorJob($data),
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
     * Dispatch BiospexImageProcess job with validation.
     */
    private function dispatchImageProcessJob(array $data): void
    {
        $required = ['queueId', 'subjectId', 'status'];
        $missing = array_diff($required, array_keys($data));

        if (! empty($missing)) {
            throw new \InvalidArgumentException('BiospexImageProcess missing required fields: '.implode(', ', $missing));
        }

        ExportImageUpdateJob::dispatch(
            $data['queueId'],
            $data['subjectId'],
            $data['status'],
            $data['error'] ?? null
        );
    }

    /**
     * Dispatch BiospexZipCreator job with validation.
     */
    private function dispatchZipCreatorJob(array $data): void
    {
        // Add any specific validation for zip creator job
        if (empty($data)) {
            throw new \InvalidArgumentException('BiospexZipCreator requires data payload');
        }

        ZooniverseExportZipResultJob::dispatch($data);
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
            'command' => 'export:listen-updates',
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
            'command' => 'export:listen-updates',
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
            $this->shutdown(1);
        }

        // Exponential backoff with jitter using reconnectDelay as base
        $jitter = mt_rand(0, 1000) / 1000;
        $delay = min(60, $this->reconnectDelay * pow(2, $this->reconnectAttempts - 1)) + $jitter;

        $this->info('⏰ Reconnecting in '.round($delay, 2).'s...');
        $this->loop->addTimer($delay, function () {
            $this->connectToSqs();
        });
    }

    /**
     * Force reconnection to SQS service.
     */
    private function forceReconnection(): void
    {
        $this->info('Forcing SQS reconnection...');
        $this->loop->addTimer(1.0, function () {
            $this->connectToSqs();
        });
    }

    /**
     * Get SQS queue URL from queue name.
     *
     * @param  string  $key  Configuration key for queue name
     * @return string SQS queue URL
     */
    private function getQueueUrl(string $key): string
    {
        $name = Config::get("services.aws.{$key}");

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

        // SQS batch delete supports up to 10 messages per request
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

                // Handle any failed deletions
                if (! empty($result['Failed'])) {
                    foreach ($result['Failed'] as $failed) {
                        $this->error("Failed to delete message in batch: {$failed['Code']} - {$failed['Message']}");
                    }
                }

            } catch (\Throwable $e) {
                $this->error('Batch delete failed, falling back to individual deletion: '.$e->getMessage());

                // Fallback to individual deletion
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
                ? '[CRITICAL] Export Updates Listener - '.config('app.name')
                : '[ERROR] Export Updates Listener - '.config('app.name');

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
        $body = "Export Updates Listener Error Report\n";
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
        $body .= 'Queue: '.Config::get('services.aws.queue_updates')."\n";
        $body .= 'Region: '.Config::get('services.aws.region', 'us-east-1')."\n";

        return $body;
    }

    /**
     * Shutdown the listener gracefully.
     *
     * @param  int  $code  Exit code
     */
    #[NoReturn]
    private function shutdown(int $code): void
    {
        $this->isShuttingDown = true;
        if ($this->heartbeatTimer) {
            $this->loop->cancelTimer($this->heartbeatTimer);
        }
        $this->loop->stop();
        $this->info($code === 0 ? 'Shutdown complete' : 'Shutdown with errors');
        exit($code);
    }
}
