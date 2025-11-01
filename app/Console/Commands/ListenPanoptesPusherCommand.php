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

use App\Jobs\ProcessPanoptesPusherDataJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;
use React\Socket\Connector;

use function Ratchet\Client\connect;

/**
 * Command to listen to external Panoptes Pusher channel for real-time updates.
 *
 * This command establishes and maintains a WebSocket connection to the Panoptes Pusher service,
 * processes incoming messages, and handles various events including classifications.
 *
 * @author BIOSPEX <biospex@gmail.com>
 */
class ListenPanoptesPusherCommand extends Command
{
    protected $signature = 'panoptes:listen';

    protected $description = 'Listen to external Panoptes Pusher channel';

    /** @var \React\EventLoop\LoopInterface Event loop instance */
    private \React\EventLoop\LoopInterface $loop;

    /** @var \Ratchet\Client\WebSocket|null WebSocket connection instance */
    private ?WebSocket $connection;

    /** @var bool Flag indicating if the command is shutting down */
    private bool $isShuttingDown = false;

    /** @var int Maximum number of reconnection attempts */
    private int $maxReconnectAttempts = 10;

    /** @var int Current number of reconnection attempts */
    private int $reconnectAttempts = 0;

    /** @var int Base delay between reconnection attempts in seconds */
    private int $reconnectDelay = 1;

    /** @var int|null Timestamp of last received message */
    private ?int $lastMessageTime;

    /** @var mixed Timer for monitoring connection heartbeat */
    private mixed $heartbeatTimer;

    /** @var string Admin email address for notifications */
    private string $adminEmail;

    /** @var int Timestamp of last notification email sent */
    private int $lastEmailSent = 0; // Rate limiting for emails

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Get admin email from config
        $this->adminEmail = config('mail.from.address');
    }

    /**
     * Execute the console command.
     *
     * @return int Command exit code
     */
    public function handle(): int
    {
        // Only allow the production environment to proceed
        /*
        if (config('app.env') !== 'production') {
            // Silently exit for non-production environments
            // \Log::info('Panoptes listener is only available in production environment');

            return self::SUCCESS;
        }
        */

        try {
            $this->info('Starting Panoptes Pusher listener...');
            $this->validateConfiguration();
            $this->initializeListener();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->handleCriticalError('Failed to start Panoptes listener', $e);

            return self::FAILURE;
        }
    }

    /**
     * Validate required configuration settings.
     *
     * @throws \RuntimeException When required configuration is missing
     */
    private function validateConfiguration(): void
    {
        $required = [
            'zooniverse.pusher.id' => 'ZOONIVERSE_PUSHER_ID',
            'zooniverse.pusher.cluster' => 'ZOONIVERSE_PUSHER_CLUSTER',
            'zooniverse.pusher.channel' => 'ZOONIVERSE_PUSHER_CHANNEL',
        ];

        foreach ($required as $configKey => $envKey) {
            if (empty(config($configKey))) {
                throw new \RuntimeException("Missing required configuration: {$configKey} (env: {$envKey})");
            }
        }

        if (empty($this->adminEmail)) {
            $this->warn('Admin email not configured - error notifications will be logged only');
        }
    }

    /**
     * Initialize the WebSocket listener and event loop.
     *
     * @throws \Throwable When initialization fails
     */
    private function initializeListener(): void
    {
        $this->loop = Loop::get();
        $this->lastMessageTime = time();

        // Setup heartbeat monitoring
        $this->setupHeartbeatMonitor();

        // Setup graceful shutdown handlers
        $this->setupSignalHandlers();

        // Start initial connection
        $this->connectToPusher();

        // Run the event loop
        $this->info('Event loop starting...');
        $this->loop->run();
    }

    private function setupHeartbeatMonitor(): void
    {
        $this->heartbeatTimer = $this->loop->addPeriodicTimer(30, function () {
            if ($this->isShuttingDown) {
                return;
            }

            $now = time();
            if (($now - $this->lastMessageTime) > 120) {
                $this->warn('No messages received for 2 minutes, forcing reconnection...');
                $this->forceReconnection();
            }
        });
    }

    /**
     * Establish connection to Pusher WebSocket server.
     */
    private function connectToPusher(): void
    {
        if ($this->isShuttingDown) {
            return;
        }

        $this->lastMessageTime = time();
        $url = $this->buildWebSocketUrl();

        $connector = new Connector([
            'timeout' => 20, // Increased from 15 to 20 seconds
            'tls' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
            //'tcp' => [
            //    'so_keepalive' => true,
            //],
            //'dns' => [
            //    'timeout' => 10,
            //],
        ]);

        $this->info('Connecting to Pusher... (attempt: '.($this->reconnectAttempts + 1).')');

        connect($url, [], [], $this->loop, $connector)
            ->then(
                [$this, 'onConnectionSuccess'],
                [$this, 'onConnectionFailure']
            );
    }

    private function buildWebSocketUrl(): string
    {
        $cluster = config('zooniverse.pusher.cluster');
        $appId = config('zooniverse.pusher.id');

        // Use protocol version 7 to avoid deprecation warnings
        return "wss://ws-{$cluster}.pusher.com:443/app/{$appId}?protocol=7&client=php-ratchet&version=1.0";
    }

    /**
     * Handle successful WebSocket connection.
     *
     * @param  WebSocket  $connection  WebSocket connection instance
     */
    public function onConnectionSuccess(WebSocket $connection): void
    {
        $this->connection = $connection;
        $this->reconnectAttempts = 0; // Reset on successful connection
        $this->reconnectDelay = 1; // Reset delay
        $this->lastMessageTime = time();

        $this->info('✅ Connected to Pusher successfully!');

        // Subscribe to the channel
        $this->subscribeToChannel();

        // Set up event handlers
        $this->setupConnectionEventHandlers();
    }

    /**
     * Handle WebSocket connection failure.
     *
     * @param  \Throwable  $e  Exception that caused the failure
     */
    public function onConnectionFailure(\Throwable $e): void
    {
        $this->reconnectAttempts++;

        $this->handleError("Connection failed (attempt {$this->reconnectAttempts})", $e);

        if ($this->reconnectAttempts > $this->maxReconnectAttempts) {
            $this->handleCriticalError("Max reconnection attempts ({$this->maxReconnectAttempts}) exceeded", $e);
            $this->shutdown(1);

            return;
        }

        // Exponential backoff with jitter
        $jitter = mt_rand(0, 1000) / 1000;
        $delay = min(60, $this->reconnectDelay * pow(2, $this->reconnectAttempts - 1)) + $jitter;

        $this->info('⏰ Reconnecting in '.round($delay, 2).' seconds...');

        $this->loop->addTimer($delay, function () {
            $this->connectToPusher();
        });
    }

    private function subscribeToChannel(): void
    {
        $channel = config('zooniverse.pusher.channel');

        try {
            $this->connection->send(json_encode([
                'event' => 'pusher:subscribe',
                'data' => [
                    'channel' => $channel,
                ],
            ]));

            $this->info("📡 Subscribed to channel: {$channel}");
        } catch (\Throwable $e) {
            $this->handleError("Failed to subscribe to channel: {$channel}", $e);
        }
    }

    private function setupConnectionEventHandlers(): void
    {
        // Handle incoming messages
        $this->connection->on('message', [$this, 'onMessage']);

        // Handle connection close
        $this->connection->on('close', [$this, 'onConnectionClose']);

        // Handle connection errors
        $this->connection->on('error', [$this, 'onConnectionError']);
    }

    /**
     * Handle incoming WebSocket messages.
     *
     * @param  MessageInterface  $msg  Received message
     */
    public function onMessage(MessageInterface $msg): void
    {
        $this->lastMessageTime = time();

        try {
            $rawPayload = $msg->getPayload();

            // Validate JSON before decoding
            if (empty($rawPayload)) {
                $this->warn('Received empty message payload');

                return;
            }

            $payload = json_decode($rawPayload, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->warn('Received invalid JSON message: '.json_last_error_msg());

                return;
            }

            if (! is_array($payload)) {
                $this->warn('Received invalid message format - not JSON array');

                return;
            }

            $event = $payload['event'] ?? 'unknown';

            switch ($event) {
                case 'pusher:connection_established':
                    $this->info('🔗 Pusher connection established and authenticated');
                    break;

                case 'pusher:ping':
                    $this->handlePing();
                    break;

                case 'classification':
                    $this->handleClassificationEvent($payload);
                    break;

                case 'pusher:error':
                    $this->handlePusherError($payload);
                    break;

                default:
                    $this->info("📨 Received event: {$event}");
                    // Log unknown events for debugging
                    Log::info('Unknown Pusher event received', [
                        'event' => $event,
                        'payload' => $payload,
                        'command' => 'panoptes:listen',
                    ]);
            }

        } catch (\Throwable $e) {
            $this->handleError('Error processing incoming message', $e, [
                'raw_payload' => substr($msg->getPayload(), 0, 500), // First 500 chars for debugging
            ]);
        }
    }

    /**
     * Handle WebSocket connection closure.
     *
     * @param  int|null  $code  Close status code
     * @param  string|null  $reason  Close reason
     */
    public function onConnectionClose($code = null, $reason = null): void
    {
        if (! $this->isShuttingDown) {
            $this->warn("Connection closed unexpectedly (Code: {$code}, Reason: {$reason})");
            $this->scheduleReconnection(2.0);
        }
    }

    /**
     * Handle WebSocket connection errors.
     *
     * @param  \Throwable  $e  Connection error exception
     */
    public function onConnectionError(\Throwable $e): void
    {
        $this->handleError('Connection error during operation', $e);
    }

    private function handlePing(): void
    {
        try {
            // Send pong immediately when ping is received
            $pongMessage = json_encode(['event' => 'pusher:pong', 'data' => []]);
            $this->connection->send($pongMessage);
            $this->info('🏓 Responded to Pusher ping');

            // Update last message time to reset heartbeat monitor
            $this->lastMessageTime = time();
        } catch (\Throwable $e) {
            $this->handleError('Failed to respond to ping', $e);
            // Force reconnection if ping response fails
            $this->forceReconnection();
        }
    }

    private function handleClassificationEvent(array $payload): void
    {
        $this->info('🔬 Classification event received - dispatching job');

        try {
            if (! isset($payload['data'])) {
                throw new \InvalidArgumentException('Classification event missing data field');
            }

            // Validate that data is either string or array
            $data = $payload['data'];
            if (! is_string($data) && ! is_array($data)) {
                throw new \InvalidArgumentException('Classification data must be string or array, got: '.gettype($data));
            }

            // If it's an array, encode it as JSON string for the job
            if (is_array($data)) {
                $data = json_encode($data);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \InvalidArgumentException('Failed to encode classification data as JSON: '.json_last_error_msg());
                }
            }

            ProcessPanoptesPusherDataJob::dispatch($data);
            $this->info('✅ Classification job dispatched successfully');

        } catch (\Throwable $e) {
            $this->handleError('Failed to dispatch classification job', $e, [
                'payload_keys' => array_keys($payload),
                'has_data' => isset($payload['data']),
                'data_type' => isset($payload['data']) ? gettype($payload['data']) : 'missing',
                'payload_preview' => json_encode($payload, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR),
            ]);
        }
    }

    private function handlePusherError(array $payload): void
    {
        $errorMessage = $payload['data']['message'] ?? 'Unknown Pusher error';
        $errorCode = $payload['data']['code'] ?? 'unknown';

        // Handle specific Pusher error codes
        switch ($errorCode) {
            case 4004: // Over quota
                $this->handleCriticalError('Pusher account over quota - service degraded',
                    new \RuntimeException("Account for App exceeded quota. Error: {$errorMessage}"));
                // Don't reconnect immediately for quota issues
                $this->scheduleReconnection(300); // Wait 5 minutes

                return;

            case 4201: // Pong not received
                $this->warn("Pusher didn't receive our pong response - connection may be slow");
                // Force immediate reconnection
                $this->forceReconnection();

                return;

            case 4001: // App does not exist
            case 4003: // App disabled
                $this->handleCriticalError('Pusher configuration error',
                    new \RuntimeException("Pusher error {$errorCode}: {$errorMessage}"));
                $this->shutdown(1); // Exit completely

                return;

            default:
                $this->handleError("Pusher error received: {$errorMessage} (Code: {$errorCode})", null, $payload);
        }
    }

    private function forceReconnection(): void
    {
        try {
            if ($this->connection && method_exists($this->connection, 'close')) {
                $this->connection->close();
            }
        } catch (\Throwable $e) {
            $this->error("Error closing stale connection: {$e->getMessage()}");
        }

        $this->scheduleReconnection(1.0);
    }

    private function scheduleReconnection(float $delay = 1.0): void
    {
        if ($this->isShuttingDown) {
            return;
        }

        $this->info('🔄 Scheduling reconnection in '.round($delay, 1).' seconds...');

        $this->loop->addTimer($delay, function () {
            if (! $this->isShuttingDown) {
                $this->connectToPusher();
            }
        });
    }

    private function setupSignalHandlers(): void
    {
        if (! extension_loaded('pcntl')) {
            $this->warn('PCNTL extension not loaded - graceful shutdown may not work properly');

            return;
        }

        $signals = [SIGINT, SIGTERM, SIGHUP];

        foreach ($signals as $signal) {
            $this->loop->addSignal($signal, function ($signal) {
                $this->info("📡 Received signal {$signal} - shutting down gracefully...");
                $this->shutdown(0);
            });
        }
    }

    /**
     * Handle and log error events.
     *
     * @param  string  $message  Error message
     * @param  \Throwable|null  $e  Exception if available
     * @param  array  $context  Additional context information
     */
    private function handleError(string $message, ?\Throwable $e = null, array $context = []): void
    {
        $context['timestamp'] = now()->toISOString();
        $context['command'] = 'panoptes:listen';

        if ($e) {
            $context['error'] = $e->getMessage();
            $context['trace'] = $e->getTraceAsString();
            $context['file'] = $e->getFile();
            $context['line'] = $e->getLine();
        }

        Log::error($message, $context);
        $this->error($message.($e ? ": {$e->getMessage()}" : ''));

        // Send email notification for errors (with rate limiting)
        if ($this->shouldSendEmailNotification($message)) {
            $this->sendErrorEmail($message, $e, $context);
        }
    }

    private function handleCriticalError(string $message, \Throwable $e): void
    {
        $context = [
            'timestamp' => now()->toISOString(),
            'command' => 'panoptes:listen',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        Log::critical($message, $context);
        $this->error("🚨 CRITICAL: {$message}: {$e->getMessage()}");

        $this->sendErrorEmail($message, $e, $context, true);
    }

    private function shouldSendEmailNotification(string $message): bool
    {
        // Rate limiting - only send one email per 5 minutes for regular errors
        $now = time();
        if (($now - $this->lastEmailSent) < 300) {
            return false;
        }

        // Send emails for certain types of errors
        $criticalKeywords = ['critical', 'failed to start', 'max reconnection', 'configuration', 'dispatch'];

        foreach ($criticalKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                $this->lastEmailSent = $now;

                return true;
            }
        }

        return false;
    }

    private function sendErrorEmail(string $message, ?\Throwable $e, array $context, bool $isCritical = false): void
    {
        if (empty($this->adminEmail)) {
            return;
        }

        try {
            $subject = $isCritical
                ? '[CRITICAL] Panoptes Listener Error - '.config('app.name')
                : '[ERROR] Panoptes Listener - '.config('app.name');

            $body = $this->buildErrorEmailBody($message, $e, $context, $isCritical);

            Mail::raw($body, function ($mail) use ($subject) {
                $mail->to($this->adminEmail)
                    ->subject($subject);
            });

        } catch (\Throwable $mailException) {
            Log::error('Failed to send error notification email', [
                'mail_error' => $mailException->getMessage(),
                'original_error' => $e?->getMessage(),
                'admin_email' => $this->adminEmail,
            ]);
        }
    }

    private function buildErrorEmailBody(string $message, ?\Throwable $e, array $context, bool $isCritical): string
    {
        $body = "Panoptes Listener Error Report\n";
        $body .= str_repeat('=', 50)."\n\n";

        if ($isCritical) {
            $body .= "🚨 CRITICAL ERROR - Immediate attention required!\n\n";
        }

        $body .= 'Time: '.now()->format('Y-m-d H:i:s T')."\n";
        $body .= 'Server: '.php_uname('n')."\n";
        $body .= 'Application: '.config('app.name')."\n";
        $body .= 'Environment: '.config('app.env')."\n\n";

        $body .= "Error Message:\n{$message}\n\n";

        if ($e) {
            $body .= "Exception Details:\n";
            $body .= 'Type: '.get_class($e)."\n";
            $body .= 'Message: '.$e->getMessage()."\n";
            $body .= 'File: '.$e->getFile().':'.$e->getLine()."\n\n";

            $body .= "Stack Trace:\n".$e->getTraceAsString()."\n\n";
        }

        if (! empty($context)) {
            $body .= "Context:\n";
            $body .= json_encode($context, JSON_PRETTY_PRINT)."\n\n";
        }

        $body .= "Configuration:\n";
        $body .= 'Pusher Cluster: '.config('zooniverse.pusher.cluster')."\n";
        $body .= 'Pusher Channel: '.config('zooniverse.pusher.channel')."\n";
        $body .= 'Queue: '.config('config.queue.pusher_process', 'default')."\n";

        return $body;
    }

    /**
     * Perform graceful shutdown of the command.
     *
     * @param  int  $exitCode  Exit code to return
     */
    private function shutdown(int $exitCode = 0): void
    {
        $this->isShuttingDown = true;

        // Cancel heartbeat timer
        if ($this->heartbeatTimer) {
            $this->loop->cancelTimer($this->heartbeatTimer);
        }

        // Close WebSocket connection
        try {
            if ($this->connection && method_exists($this->connection, 'close')) {
                $this->connection->close();
            }
        } catch (\Throwable $e) {
            $this->error("Error closing connection during shutdown: {$e->getMessage()}");
        }

        // Stop event loop
        if ($this->loop) {
            $this->loop->stop();
        }

        $this->info($exitCode === 0 ? '✅ Shutdown complete' : '❌ Shutdown with errors');

        exit($exitCode);
    }
}
