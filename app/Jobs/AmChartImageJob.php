<?php

namespace App\Jobs;

use App\Models\Project;
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
     * @var \App\Models\Project
     */
    private $project;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
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
            $projectFolderPath = $dir.'/'.$this->project->id;
            $projectFilePath = $dir.'/'.$this->project->id.'.png';
            $amChartFilePath = $dir.'/'.$this->project->id.'/amCharts.png';

            if (! Storage::exists($projectFilePath)) {
                Storage::makeDirectory($projectFolderPath);
            }

            exec("node chart-image.js {$this->project->id}", $output);

            if ($output[0] == "true") {
                if (Storage::exists($projectFilePath)) {
                    Storage::delete($projectFilePath);
                }
                Storage::move($amChartFilePath, $projectFilePath);
                Storage::deleteDirectory($dir.'/'.$this->project->id);

                $this->delete();

                return;
            }

            throw new \Exception(implode('<br>', $output));

        } catch (\Exception $e) {
            $user = $userContract->find(1);
            $user->notify(new JobError(__FILE__, ['Project Id: ' . $this->project->id, $e->getMessage()]));
        }
    }
}
