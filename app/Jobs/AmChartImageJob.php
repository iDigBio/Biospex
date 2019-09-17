<?php

namespace App\Jobs;

use App\Models\AmChart;
use App\Notifications\JobError;
use App\Repositories\Interfaces\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Storage;

class AmChartImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\AmChart
     */
    private $amChart;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\AmChart $amChart
     */
    public function __construct(AmChart $amChart)
    {
        $this->amChart = $amChart;
        $this->onQueue(config('config.chart_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\Interfaces\User $userContract
     */
    public function handle(User $userContract)
    {
        try {
            $dir = config('config.charts_dir');
            $projectFolderPath = $dir.'/'.$this->amChart->project_id;
            $projectFilePath = $dir.'/'.$this->amChart->project_id.'.png';
            $amChartFilePath = $dir.'/'.$this->amChart->project_id.'/amCharts.png';

            if (! Storage::exists($projectFolderPath)) {
                Storage::makeDirectory($projectFolderPath);
            }

            exec("node chart-image.js {$this->amChart->project_id}", $output);

            if ($output[0] == "true") {
                if (Storage::exists($projectFilePath)) {
                    Storage::delete($projectFilePath);
                }
                Storage::move($amChartFilePath, $projectFilePath);
                Storage::deleteDirectory($projectFolderPath);

                $this->delete();

                return;
            }

            throw new \Exception(implode('<br>', $output));

        } catch (\Exception $e) {
            $user = $userContract->find(1);
            $user->notify(new JobError(__FILE__, ['Project Id: ' . $this->amChart->project_id, $e->getMessage()]));
        }
    }
}
