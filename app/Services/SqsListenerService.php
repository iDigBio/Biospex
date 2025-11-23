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

namespace App\Services;

use Aws\Sqs\SqsClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SqsListenerService
{
    private int $reconnectAttempts = 0;
    private int $maxReconnectAttempts = 10;
    private int $lastEmailSent = 0;
    private ?string $adminEmail;
    private string $commandName;

    public function __construct(
        protected SqsClient $sqs,
        string $commandName = 'sqs:listener'
    ) {
        $this->adminEmail = config('mail.from.address', 'admin@biospex.org');
        $this->commandName = $commandName;
    }

    public function getQueueUrl(string $configKey): string
    {
        $name = Config::get("services.aws.queues.{$configKey}");
        if (empty($name)) {
            throw new \RuntimeException("Missing config for queue: {$configKey}");
        }

        return $this->sqs->getQueueUrl(['QueueName' => $name])['QueueUrl'];
    }

    public function receiveMessages(string $queueUrl, int $maxMessages = 10, int $waitTime = 20): array
    {
        $result = $this->sqs->receiveMessage([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => $maxMessages,
            'WaitTimeSeconds' => $waitTime,
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        return $result['Messages'] ?? [];
    }

    public function deleteMessage(string $queueUrl, string $receiptHandle): void
    {
        try {
            $this->sqs->deleteMessage(['QueueUrl' => $queueUrl, 'ReceiptHandle' => $receiptHandle]);
        } catch (\Throwable $e) {
            // Log locally but don't throw to avoid crashing the loop for one deletion failure
            Log::error("Failed to delete message: {$e->getMessage()}");
        }
    }

    public function batchDeleteMessages(string $queueUrl, array $messages): void
    {
        if (empty($messages)) {
            return;
        }

        $batches = array_chunk($messages, 10);

        foreach ($batches as $batch) {
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

                if (!empty($result['Failed'])) {
                    foreach ($result['Failed'] as $failed) {
                        Log::error("Failed to delete message in batch: {$failed['Code']} - {$failed['Message']}");
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Batch delete failed, falling back to individual deletion: ' . $e->getMessage());
                foreach ($batch as $message) {
                    $this->deleteMessage($queueUrl, $message['ReceiptHandle']);
                }
            }
        }
    }

    public function handleConnectionError(\Throwable $e, callable $onCriticalError): void
    {
        $this->reconnectAttempts++;
        $this->handleError("SQS connection failed (attempt {$this->reconnectAttempts})", $e);

        if ($this->reconnectAttempts > $this->maxReconnectAttempts) {
            $msg = "Max reconnection attempts ({$this->maxReconnectAttempts}) exceeded";
            $this->handleCriticalError($msg, $e);
            $onCriticalError();
        }
    }

    public function resetConnectionAttempts(): void
    {
        $this->reconnectAttempts = 0;
    }

    public function handleError(string $msg, ?\Throwable $e = null, array $ctx = []): void
    {
        $context = array_merge($ctx, [
            'timestamp' => now()->toISOString(),
            'command' => $this->commandName,
            'reconnect_attempts' => $this->reconnectAttempts,
        ]);

        if ($e) {
            $context['error'] = $e->getMessage();
            $context['trace'] = $e->getTraceAsString();
            $context['file'] = $e->getFile();
            $context['line'] = $e->getLine();
        }

        Log::error($msg, $context);

        if ($this->shouldSendEmail($msg)) {
            $this->sendEmail($msg, $e, $context);
        }
    }

    public function handleCriticalError(string $msg, \Throwable $e): void
    {
        $context = [
            'timestamp' => now()->toISOString(),
            'command' => $this->commandName,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'reconnect_attempts' => $this->reconnectAttempts,
        ];

        Log::critical($msg, $context);
        $this->sendEmail($msg, $e, $context, true);
    }

    public function isPermanentError(\Throwable $e): bool
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

    private function sendEmail(string $msg, ?\Throwable $e, array $ctx, bool $critical = false): void
    {
        if (!$this->adminEmail) {
            return;
        }

        try {
            $subject = ($critical ? '[CRITICAL] ' : '[ERROR] ') . $this->commandName . ' - ' . config('app.name');
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

    private function buildEmailBody(string $msg, ?\Throwable $e, array $ctx, bool $critical): string
    {
        $body = "SQS Listener Error Report\n";
        $body .= str_repeat('=', 50) . "\n\n";

        if ($critical) {
            $body .= "🚨 CRITICAL ERROR - Immediate attention required!\n\n";
        }

        $body .= 'Time: ' . now()->format('Y-m-d H:i:s T') . "\n";
        $body .= 'Server: ' . php_uname('n') . "\n";
        $body .= 'Application: ' . config('app.name') . "\n";
        $body .= "Error Message:\n{$msg}\n\n";

        if ($e) {
            $body .= "Exception Details:\n";
            $body .= 'Type: ' . get_class($e) . "\n";
            $body .= 'Message: ' . $e->getMessage() . "\n";
            $body .= 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n\n";
            $body .= "Stack Trace:\n" . $e->getTraceAsString() . "\n\n";
        }

        if (!empty($ctx)) {
            $body .= "Context:\n";
            $body .= json_encode($ctx, JSON_PRETTY_PRINT) . "\n\n";
        }

        return $body;
    }
}