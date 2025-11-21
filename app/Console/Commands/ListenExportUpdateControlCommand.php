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
use Supervisor\Supervisor;

class ListenExportUpdateControlCommand extends Command
{
    protected $signature = 'update:listen-controller {action=start|stop}';

    protected $description = 'Start/stop the export update listener via Supervisor';

    public function handle(): int
    {
        $action = $this->argument('action');
        $programs = [
            'loc-biospex:loc-biospex-listen-export-update-queue',  // Exact from status
            'loc-biospex:loc-biospex-listen-export-image-tasks-dlq',
        ];

        try {
            // Build XML-RPC client for Unix socket
            $guzzle = new \GuzzleHttp\Client([
                'curl' => [
                    CURLOPT_UNIX_SOCKET_PATH => '/var/run/supervisor.sock',
                ],
            ]);

            $transport = new \fXmlRpc\Transport\PsrTransport(new \GuzzleHttp\Psr7\HttpFactory, $guzzle);
            $client = new \fXmlRpc\Client('http://localhost/RPC2', $transport);

            $supervisor = new Supervisor($client);

            foreach ($programs as $type => $program) {
                if ($action === 'start') {
                    $supervisor->startProcess($program);
                    $this->info("Started {$type} listener: {$program}");
                } else {
                    $supervisor->stopProcess($program);
                    $this->info("Stopped {$type} listener: {$program}");
                }
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to {$action} listeners: ".$e->getMessage());

            return self::FAILURE;
        }
    }
}
