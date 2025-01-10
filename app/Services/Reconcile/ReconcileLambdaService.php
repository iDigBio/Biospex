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

namespace App\Services\Reconcile;

use App\Services\Api\AwsLambdaApiService;
use App\Traits\SkipZooniverse;

/**
 * Class ReconcileLambdaService
 *
 * Handles all methods for the reconcile and explained process.
 *
 * @see \App\Listeners\LabelReconciliationListener
 */
class ReconcileLambdaService
{
    use SkipZooniverse;

    /**
     * ReconcileLambdaService constructor.
     */
    public function __construct(
        protected AwsLambdaApiService $awsLambdaApiService
    ) {}

    /**
     * Process explained file via lambda labelReconciliations.
     *
     * @see \App\Http\Controllers\Admin\ExpertReconcileController::create
     * @see \App\Console\Commands\ZooniverseExpertReviewCommand::handle
     */
    public function invokeLambdaExplained(int $expeditionId): void
    {
        $attributes = [
            'bucket' => config('filesystems.disks.s3.bucket'),
            'key' => config('zooniverse.directory.classification').'/'.$expeditionId.'.csv',
            'explanations' => true,
        ];

        $this->awsLambdaApiService->lambdaInvokeAsync(config('config.aws.lambda_reconciliation_function'), $attributes);
    }
}
