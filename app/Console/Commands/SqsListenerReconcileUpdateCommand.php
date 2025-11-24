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

use Illuminate\Console\Command;

class SqsListenerReconcileUpdateCommand extends Command
{
    protected $signature = 'reconcile:listen-controller {action=start|stop}';

    protected $description = 'Start/stop the reconciliation update listener via Supervisor';

    public function handle(): int
    {
        $action = $this->argument('action');

        // Update these to match your exact Supervisor program names
        $programs = [
            'loc-biospex:loc-biospex-listener-reconciliation-update',
        ];

        try {
            $guzzle = new \GuzzleHttp\Client([
                'curl' => [
                    CURLOPT_UNIX_SOCKET_PATH => '/var/run/supervisor.sock',
                ],
            ]);

            $transport = new \fXmlRpc\Transport\PsrTransport(new \GuzzleHttp\Psr7\HttpFactory, $guzzle);
            $client = new \fXmlRpc\Client('http://localhost/RPC2', $transport);
            $supervisor = new \Supervisor\Supervisor($client);

            foreach ($programs as $program) {
                if ($action === 'start') {
                    $supervisor->startProcess($program);
                    $this->info("Started reconciliation listener: {$program}");
                } else {
                    $supervisor->stopProcess($program);
                    $this->info("Stopped reconciliation listener: {$program}");
                }
            }

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Failed to {$action} reconciliation listener: ".$e->getMessage());

            return self::FAILURE;
        }
    }
}
