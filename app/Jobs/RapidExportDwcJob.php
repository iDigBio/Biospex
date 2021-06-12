<?php
/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobErrorNotification;
use App\Notifications\ProductNotification;
use App\Services\Export\RapidExportDwc;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;

class RapidExportDwcJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * @var array
     */
    private $request;

    /**
     * @var string
     */
    private $filePath;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * RapidExportDwcJob constructor.
     *
     * @param \App\Models\User $user
     * @param array $request
     */
    public function __construct(User $user, array $request)
    {
        $this->onQueue(config('config.rapid_tube'));
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * Handle job.
     *
     * @param \App\Services\Export\RapidExportDwc $rapidExportDwc
     * @throws \Throwable
     */
    public function handle(RapidExportDwc $rapidExportDwc)
    {
        DB::beginTransaction();
        try {
            $key = $this->request['key-select'] === null ? $this->request['key-text'] : $this->request['key-select'];
            $name = $this->request['key-select'] === null ? $this->request['name-text'] : null;

            $product = $rapidExportDwc->getProductRecord($key, $name);

            if (Storage::exists(config('config.rapid_product_dir') . '/' . $product->key . '.zip')) {
                Storage::delete(config('config.rapid_product_dir') . '/' . $product->key . '.zip');
            }

            $rapidExportDwc->process($product);

            $downloadUrl = route('admin.download.product', ['file' => base64_encode($key . '.zip')]);

            $this->user->notify(new ProductNotification($downloadUrl));

            DB::commit();

        } catch (\Exception $e) {
            $attributes = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];

            $this->user->notify(new JobErrorNotification($attributes));

            DB::rollback();

            $this->delete();
        }
    }
}
