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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;
use React\Socket\Connector;

use function Ratchet\Client\connect;

/**
 * Command to listen to external Panoptes Pusher channel for real-time updates.
 */
class ListenerPanoptesPusherCommand extends Command
{
    protected $signature = 'panoptes:listen';

    protected $description = 'Listen to external Panoptes Pusher channel';

    private \React\EventLoop\LoopInterface $loop;

    private ?WebSocket $connection = null;

    private bool $isShuttingDown = false;

    private int $maxReconnectAttempts = 10;

    private int $reconnectAttempts = 0;

    private int $reconnectDelay = 1;

    private ?int $lastMessageTime;

    private mixed $heartbeatTimer = null;

    private string $adminEmail;

    private bool $intentionalDisconnect = false;

    public function __construct()
    {
        parent::__construct();
        $this->adminEmail = config('mail.from.address');
    }

    public function handle(): int
    {
        try {
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

        $this->setupHeartbeatMonitor();
        $this->setupSignalHandlers();
        $this->connectToPusher();

        $this->loop->run();
    }

    private function setupHeartbeatMonitor(): void
    {
        $this->heartbeatTimer = $this->loop->addPeriodicTimer(30, function () {
            if ($this->isShuttingDown) {
                return;
            }

            if ((time() - $this->lastMessageTime) > 120) {
                $this->forceReconnection();
            }
        });
    }

    private function connectToPusher(): void
    {
        if ($this->isShuttingDown || Cache::has('panoptes_listener_quota_cooldown')) {
            if (Cache::has('panoptes_listener_quota_cooldown')) {
                $this->warn('Still in Pusher quota cooldown. Skipping connection attempt.');
            }

            return;
        }

        $this->intentionalDisconnect = false;
        $this->lastMessageTime = time();
        $url = $this->buildWebSocketUrl();

        $connector = new Connector([
            'timeout' => 20,
            'tls' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

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

        return "wss://ws-{$cluster}.pusher.com:443/app/{$appId}?protocol=7&client=php-ratchet&version=1.0";
    }

    public function onConnectionSuccess(WebSocket $connection): void
    {
        $this->connection = $connection;
        $this->reconnectAttempts = 0;
        $this->reconnectDelay = 1;
        $this->lastMessageTime = time();

        $this->subscribeToChannel();
        $this->setupConnectionEventHandlers();
    }

    public function onConnectionFailure(\Throwable $e): void
    {
        if (Cache::has('panoptes_listener_quota_cooldown')) {
            return;
        }

        $this->reconnectAttempts++;
        $this->handleError("Connection failed (attempt {$this->reconnectAttempts})", $e);

        if ($this->reconnectAttempts > $this->maxReconnectAttempts) {
            $this->handleCriticalError("Max reconnection attempts ({$this->maxReconnectAttempts}) exceeded", $e);
            $this->shutdown(1);

            return;
        }

        $jitter = mt_rand(0, 1000) / 1000;
        $delay = min(60, $this->reconnectDelay * pow(2, $this->reconnectAttempts - 1)) + $jitter;

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
                'data' => ['channel' => $channel],
            ]));
        } catch (\Throwable $e) {
            $this->handleError("Failed to subscribe to channel: {$channel}", $e);
        }
    }

    private function setupConnectionEventHandlers(): void
    {
        $this->connection->on('message', [$this, 'onMessage']);
        $this->connection->on('close', [$this, 'onConnectionClose']);
        $this->connection->on('error', [$this, 'onConnectionError']);
    }

    public function onMessage(MessageInterface $msg): void
    {
        $this->lastMessageTime = time();

        try {
            $rawPayload = $msg->getPayload();
            if (empty($rawPayload)) {
                return;
            }

            $payload = json_decode($rawPayload, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($payload)) {
                return;
            }

            $event = $payload['event'] ?? 'unknown';

            switch ($event) {
                case 'pusher:ping':
                    $this->handlePing();
                    break;
                case 'classification':
                    $this->handleClassificationEvent($payload);
                    break;
                case 'pusher:error':
                    $this->handlePusherError($payload);
                    break;
            }
        } catch (\Throwable $e) {
            $this->handleError('Error processing incoming message', $e);
        }
    }

    public function onConnectionClose($code = null, $reason = null): void
    {
        if ($this->intentionalDisconnect || $this->isShuttingDown) {
            return;
        }

        $this->warn("Connection closed unexpectedly (Code: {$code}, Reason: {$reason})");
        $this->scheduleReconnection(2.0);
    }

    public function onConnectionError(\Throwable $e): void
    {
        $this->handleError('Connection error during operation', $e);
    }

    private function handlePing(): void
    {
        try {
            $this->connection->send(json_encode(['event' => 'pusher:pong', 'data' => []]));
            $this->lastMessageTime = time();
        } catch (\Throwable $e) {
            $this->forceReconnection();
        }
    }

    private function handleClassificationEvent(array $payload): void
    {
        try {
            $data = $payload['data'];
            if (is_array($data)) {
                $data = json_encode($data);
            }
            ProcessPanoptesPusherDataJob::dispatch($data);
        } catch (\Throwable $e) {
            $this->handleError('Failed to dispatch classification job', $e);
        }
    }

    private function handlePusherError(array $payload): void
    {
        $errorMessage = $payload['data']['message'] ?? 'Unknown Pusher error';
        $errorCode = $payload['data']['code'] ?? 'unknown';

        $this->trackError($errorMessage);

        switch ($errorCode) {
            case 4004: // Over quota
                $cooldownKey = 'panoptes_listener_quota_cooldown';
                if (Cache::has($cooldownKey)) {
                    $this->intentionalDisconnect = true;

                    return;
                }

                Cache::put($cooldownKey, true, 3600);
                $this->intentionalDisconnect = true;

                $this->handleCriticalError('Pusher account over quota',
                    new \RuntimeException("Account exceeded quota. Error: {$errorMessage}"));

                $this->scheduleReconnection(3600);
                break;

            case 4001:
            case 4003:
                $this->handleCriticalError('Pusher configuration error', new \RuntimeException($errorMessage));
                $this->shutdown(1);
                break;

            default:
                $this->handleError("Pusher error: {$errorMessage}", null, $payload);
        }
    }

    private function forceReconnection(): void
    {
        try {
            if ($this->connection) {
                $this->connection->close();
            }
        } catch (\Throwable $e) {
        }
        $this->scheduleReconnection(1.0);
    }

    private function scheduleReconnection(float $delay = 1.0): void
    {
        if ($this->isShuttingDown) {
            return;
        }
        $this->loop->addTimer($delay, function () {
            $this->connectToPusher();
        });
    }

    private function trackError(?string $details = null): void
    {
        $key = 'panoptes_listener_errors';
        $shutdownKey = 'panoptes_listener_shutdown_attempted';
        if (Cache::has($shutdownKey)) {
            return;
        }

        $errors = array_filter(Cache::get($key, []), fn ($t) => $t > (time() - 60));
        $errors[] = time();
        Cache::put($key, $errors, 70);

        if (count($errors) > 10) {
            Cache::put($shutdownKey, true, 3600);
            $msg = 'Too many errors detected. Entering dormant mode for 1 hour.';
            $this->handleCriticalError($msg, new \RuntimeException('Error threshold exceeded'));

            \Log::info('Entering dormant mode for 1 hour...');
            sleep(3600);
            exit(1);
        }
    }

    private function handleError(string $message, ?\Throwable $e = null, array $context = []): void
    {
        if (Cache::has('panoptes_listener_quota_cooldown')) {
            return;
        }

        $this->trackError($message.($e ? ": {$e->getMessage()}" : ''));

        $context['timestamp'] = now()->toISOString();
        Log::error($message, $context);
        $this->error($message);

        $this->sendErrorEmail($message, $e, $context);
    }

    private function handleCriticalError(string $message, \Throwable $e): void
    {
        if (Cache::has('panoptes_listener_quota_cooldown') && strpos($message, 'quota') === false) {
            return;
        }

        $context = ['timestamp' => now()->toISOString(), 'error' => $e->getMessage()];
        Log::critical($message, $context);
        $this->error("🚨 CRITICAL: {$message}");

        $this->sendErrorEmail($message, $e, $context, true);
    }

    private function sendErrorEmail(string $message, ?\Throwable $e, array $context, bool $isCritical = false): void
    {
        if (empty($this->adminEmail)) {
            return;
        }

        // MASTER GATEKEEPER - Strictly 1 hour cooldown across all error types
        $lockKey = 'panoptes_listener_global_email_cooldown';
        if (Cache::has($lockKey)) {
            return;
        }
        Cache::put($lockKey, true, 3600);

        try {
            $subject = ($isCritical ? '[CRITICAL] ' : '[ERROR] ').'Panoptes Listener - '.config('app.name');
            $body = $this->buildErrorEmailBody($message, $e, $context, $isCritical);

            Mail::raw($body, function ($m) use ($subject) {
                $m->to($this->adminEmail)->subject($subject);
            });
        } catch (\Throwable $me) {
            Log::error('Failed to send error notification email', ['mail_error' => $me->getMessage()]);
        }
    }

    private function buildErrorEmailBody(string $message, ?\Throwable $e, array $context, bool $isCritical): string
    {
        $body = "Panoptes Listener Error Report\n".str_repeat('=', 50)."\n\n";
        if ($isCritical) {
            $body .= "🚨 CRITICAL ERROR - Immediate attention required!\n\n";
        }

        $body .= 'Time: '.now()->format('Y-m-d H:i:s T')."\n";
        $body .= "Error Message: {$message}\n\n";

        if ($e) {
            $body .= 'Exception: '.get_class($e)."\nMessage: ".$e->getMessage()."\n";
            $body .= 'File: '.$e->getFile().':'.$e->getLine()."\n\n";
            $body .= "Stack Trace:\n".$e->getTraceAsString()."\n\n";
        }

        if (! empty($context)) {
            $body .= "Context:\n".json_encode($context, JSON_PRETTY_PRINT)."\n\n";
        }

        return $body;
    }

    private function setupSignalHandlers(): void
    {
        if (! extension_loaded('pcntl')) {
            return;
        }
        foreach ([SIGINT, SIGTERM, SIGHUP] as $s) {
            $this->loop->addSignal($s, fn () => $this->shutdown(0));
        }
    }

    private function shutdown(int $exitCode = 0): void
    {
        $this->isShuttingDown = true;
        if ($this->heartbeatTimer) {
            $this->loop->cancelTimer($this->heartbeatTimer);
        }
        try {
            if ($this->connection) {
                $this->connection->close();
            }
        } catch (\Throwable $e) {
        }
        if ($this->loop) {
            $this->loop->stop();
        }
        exit($exitCode);
    }
}
