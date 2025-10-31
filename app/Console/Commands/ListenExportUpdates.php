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
            // Log::info('Export updates listener disabled in non-prod/local env');

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
     * Poll SQS queue for messages.
     */
    private function pollSqs(): void
    {
        $queueUrl = $this->getQueueUrl('queue_updates');

        $this->sqs->receiveMessageAsync([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => 1,
            'WaitTimeSeconds' => 20,
            'VisibilityTimeout' => 30,
        ])->then(
            function ($response) {
                $this->reconnectAttempts = 0;
                $this->reconnectDelay = 1.0;

                if (! empty($response['Messages'])) {
                    $this->lastMessageTime = time();
                    $this->processMessage($response['Messages'][0], $this->getQueueUrl('queue_updates'));
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
     * Process received SQS message.
     *
     * @param  array  $message  SQS message data
     * @param  string  $queueUrl  URL of the SQS queue
     */
    private function processMessage(array $message, string $queueUrl): void
    {
        $body = json_decode($message['Body'], true);
        $receipt = $message['ReceiptHandle'];

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON: '.json_last_error_msg());
            $this->deleteMessage($queueUrl, $receipt);

            return;
        }

        try {
            $this->routeMessage($body);
            $this->deleteMessage($queueUrl, $receipt);
        } catch (\Throwable $e) {
            $this->handleError('Failed to process message', $e, ['message_id' => $message['MessageId'] ?? '']);
        }
    }

    /**
     * Route message to appropriate job based on function name.
     *
     * @param  array  $data  Message data
     *
     * @throws \InvalidArgumentException When function is missing or unknown
     */
    private function routeMessage(array $data): void
    {
        $function = $data['function'] ?? throw new \InvalidArgumentException('Missing function');

        match ($function) {
            'BiospexImageProcess' => ExportImageUpdateJob::dispatch(
                $data['queueId'], $data['subjectId'], $data['status'], $data['error'] ?? null
            ),

            'BiospexZipCreator' => ZooniverseExportZipResultJob::dispatch($data),

            default => throw new \InvalidArgumentException("Unknown function: {$function}"),
        };
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
            $this->handleCriticalError('Max reconnection attempts exceeded', $e);
            $this->shutdown(1);
        }

        $jitter = mt_rand(0, 1000) / 1000;
        $delay = min(60, $this->reconnectDelay * pow(2, $this->reconnectAttempts - 1)) + $jitter;

        $this->info('Reconnecting in '.round($delay, 2).'s...');
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
     * Handle non-critical errors with logging and notifications.
     *
     * @param  string  $msg  Error message
     * @param  \Throwable|null  $e  Exception if any
     * @param  array  $ctx  Additional context
     */
    private function handleError(string $msg, ?\Throwable $e = null, array $ctx = []): void
    {
        Log::error($msg, array_merge($ctx, ['error' => $e?->getMessage()]));
        $this->error($msg.($e ? ': '.$e->getMessage() : ''));

        if ($this->shouldSendEmail($msg)) {
            $this->sendEmail($msg, $e, $ctx);
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
        Log::critical($msg, ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        $this->sendEmail($msg, $e, [], true);
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
        if (($now - $this->lastEmailSent) < 300) {
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

            Mail::raw("Error: {$msg}\n".($e ? 'Exception: '.$e->getMessage() : ''), function ($m) use ($subject) {
                $m->to($this->adminEmail)->subject($subject);
            });
        } catch (\Throwable $me) {
            Log::error('Failed to send alert email', ['error' => $me->getMessage()]);
        }
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
