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

class PanoptesListenerCommand extends Command
{
    protected $signature = 'panoptes:listen';

    protected $description = 'Listen to external Panoptes Pusher channel';

    private $loop;

    private $connection;

    private bool $isShuttingDown = false;

    private int $maxReconnectAttempts = 10;

    private int $reconnectAttempts = 0;

    private int $reconnectDelay = 1;

    private $lastMessageTime;

    private $heartbeatTimer;

    private string $adminEmail;

    private int $lastEmailSent = 0; // Rate limiting for emails

    public function __construct()
    {
        parent::__construct();

        // Get admin email from config
        $this->adminEmail = config('mail.from.address');
    }

    public function handle(): int
    {
        // Only allow production environment to proceed
        if (config('app.env') !== 'production') {
            // Silently exit for non-production environments
            return self::SUCCESS;
        }

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

    private function connectToPusher(): void
    {
        if ($this->isShuttingDown) {
            return;
        }

        $this->lastMessageTime = time();
        $url = $this->buildWebSocketUrl();

        $connector = new Connector([
            'timeout' => 15,
            'tls' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
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

    public function onMessage(MessageInterface $msg): void
    {
        $this->lastMessageTime = time();

        try {
            $payload = json_decode($msg->getPayload(), true);

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
            }

        } catch (\Throwable $e) {
            $this->handleError('Error processing incoming message', $e);
        }
    }

    public function onConnectionClose($code = null, $reason = null): void
    {
        if (! $this->isShuttingDown) {
            $this->warn("Connection closed unexpectedly (Code: {$code}, Reason: {$reason})");
            $this->scheduleReconnection(2.0);
        }
    }

    public function onConnectionError(\Throwable $e): void
    {
        $this->handleError('Connection error during operation', $e);
    }

    private function handlePing(): void
    {
        try {
            $this->connection->send(json_encode(['event' => 'pusher:pong']));
            $this->info('🏓 Responded to Pusher ping');
        } catch (\Throwable $e) {
            $this->handleError('Failed to respond to ping', $e);
        }
    }

    private function handleClassificationEvent(array $payload): void
    {
        $this->info('🔬 Classification event received - dispatching job');

        try {
            if (! isset($payload['data'])) {
                throw new \InvalidArgumentException('Classification event missing data field');
            }

            ProcessPanoptesPusherDataJob::dispatch($payload['data']);

            $this->info('✅ Classification job dispatched successfully');

        } catch (\Throwable $e) {
            $this->handleError('Failed to dispatch classification job', $e, [
                'payload_keys' => array_keys($payload),
                'has_data' => isset($payload['data']),
            ]);
        }
    }

    private function handlePusherError(array $payload): void
    {
        $errorMessage = $payload['data']['message'] ?? 'Unknown Pusher error';
        $errorCode = $payload['data']['code'] ?? 'unknown';

        $this->handleError("Pusher error received: {$errorMessage} (Code: {$errorCode})", null, $payload);
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
