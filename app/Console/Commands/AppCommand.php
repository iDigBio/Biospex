<?php

/**
 * Biospex
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/iDigBio/Biospex/master/LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to padraic@php.net so we can send you a copy immediately.
 *
 * @category   Biospex
 * @package    Biospex
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/mockery/blob/master/LICENSE New BSD License
 */

namespace App\Console\Commands;

use App\Facades\GeneralHelper;
use App\Jobs\BingoJob;
use App\Models\Bingo;
use App\Repositories\Interfaces\BingoMap;
use App\Services\Api\GeoLocation;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\Interfaces\BingoMap
     */
    private $bingoMapContract;

    /**
     * @var \App\Services\Api\GeoLocation
     */
    private $location;

    /**
     * AppCommand constructor.
     */
    public function __construct(BingoMap $bingoMapContract, GeoLocation $location) {
        parent::__construct();

        $this->bingoMapContract = $bingoMapContract;
        $this->location = $location;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $test = $this->bingoMapContract->findBy('ip', '68.63.24.33');
        $uuid = GeneralHelper::uuidToBin($test->uuid);
        $result = $this->bingoMapContract->findBy('uuid', $uuid);
        dd($result);
        $bingo = Bingo::find(1);

        // temp since working local
        $this->location->locate('68.63.24.33');

        $attributes = [
            'bingo_id' => $bingo->id,
            'uuid' => \Session::get('bingoUuid') ?? null,
            'ip' => $this->location->ip
        ];
        $values = [
            'bingo_id' => $bingo->id,
            'uuid' => \Session::get('bingoUuid') ?? null,
            'ip' => $this->location->ip,
            'latitude' => $this->location->latitude,
            'longitude' => $this->location->longitude,
            'city' => $this->location->city
        ];

        $map = $bingo->maps()->firstOrCreate($attributes, $values);
        dd($map);
    }
}