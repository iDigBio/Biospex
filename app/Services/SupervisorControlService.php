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

use GuzzleHttp\Client;
use Supervisor\Supervisor;

/**
 * Service class for controlling Supervisor processes.
 *
 * This class provides functionality to manage Supervisor processes by executing
 * start, stop, and restart commands through XML-RPC interface.
 */
class SupervisorControlService
{
    /**
     * Control Supervisor processes with specified action.
     *
     * @param  string  $action  Action to perform on processes ('start', 'stop', or 'restart')
     */
    public function control(array $programs, string $action): void
    {
        $guzzle = new Client([
            'curl' => [CURLOPT_UNIX_SOCKET_PATH => '/var/run/supervisor.sock'],
        ]);

        $transport = new \fXmlRpc\Transport\PsrTransport(new \GuzzleHttp\Psr7\HttpFactory, $guzzle);
        $client = new \fXmlRpc\Client('http://localhost/RPC2', $transport);
        $supervisor = new Supervisor($client);

        $group = config('config.supervisor_group');
        foreach ($programs as $program) {
            // Join program name with group name to form full program name
            $program = "{$group}:{$program}";
            match ($action) {
                'start' => $supervisor->startProcess($program),
                'stop' => $supervisor->stopProcess($program),
                'restart' => (function () use ($supervisor, $program) {
                    $supervisor->stopProcess($program);
                    $supervisor->startProcess($program);
                })(),
            };

            \Log::info("Supervisor: {$action}ed program {$program}");
        }
    }
}
