<?php

namespace App\Console\Commands;

use App\Exceptions\Handler;
use App\Jobs\NfnClassificationsCsvDownloadJob;
use App\Jobs\NfnClassificationsCsvFileJob;
use App\Repositories\Contracts\ExpeditionContract;
use App\Services\Api\NfnApi;
use App\Services\Report\Report;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;


class TestAppCommand extends Command
{

    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * TestAppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(
        ExpeditionContract $expeditionContract,
        NfnApi $api,
        Report $report,
        Handler $handler
    )
    {
        $job = new NfnClassificationsCsvFileJob([35]);
        $job->handle($expeditionContract,$api,$report,$handler);


        return;
        //$ids = [35,38,44,45,47,48,49,50,51,52,53,55,57,60,61,65,66,69,71];
        //$job = new NfnClassificationsCsvFileJob($ids);
        //$job->handle($expeditionContract, $api, $report, $handler);

        $sources = $this->sources();
        $job = new NfnClassificationsCsvDownloadJob($sources);
        $job->handle($api, $report, $handler);
    }


    public function sources()
    {
        return [
            35 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/70de8cab-f048-4854-ba08-fa48371ddf9a.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=lcfODC073cE7kHrv%2FSjA3BYrRYs%3D",
            38 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/fc0981d0-2cc7-49c2-935b-2aa791e9cfdc.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=0u1wz37ZEh0HglGI8aP0Rm11lGg%3D",
            44 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/4c7a2072-ef6d-4017-9ce3-04ee8bbf7484.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=ipwm6x2%2BOmDp2z0IhiFciwotO9o%3D",
            45 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/b07e01d1-b41b-4cfe-ac98-2b1126e6eac0.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=nEbjUXmsJa9vj3ZGnmoYTMU%2Bobo%3D",
            47 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/41663252-7783-4fe4-9cc1-efb0f4f95d48.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=Mt3O2GjJ8rJVut6bUdZxjuwm%2BUk%3D",
            48 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/247d29fd-d266-4972-ba74-51cb244db567.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=51IsCkqwjyAe4OFl4Q2iBR0TsPo%3D",
            49 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/0c3ff414-8645-48fe-b225-96eceb4e75dc.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=5OLeMqOyevZBV1tvPS0avTyGxfU%3D",
            50 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/beaac97b-7cd0-4e62-a09a-8632fb7dc9a1.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=8DPUx%2ByjwtmxZDFmCJ%2BHZ1zdxHg%3D",
            51 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/5f8cec74-4328-4d5b-b4f2-837e2fb30c72.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=S%2BIepTFP%2FLXHq4D7ZDoYF0KJ4rg%3D",
            52 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/ef60d043-77a0-4c43-a5ef-93074ddbe2f6.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=lQiodr87fH13muZZCg1AdkdTI60%3D",
            53 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/73f6c65f-3b25-4620-9af9-f875e95c8331.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=W8j3584ikyiuo3r%2BiqfNJE3ACaU%3D",
            55 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/8aac2884-06c4-42a4-83da-64068e377bf1.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=2oQRKnWuXetlBHwzd4r781s0Epo%3D",
            57 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/76994e6d-249d-46cf-aca7-973041a899c3.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=G6N404%2B9s%2BC5jqaWp3UpPVSlpKc%3D",
            60 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/ea72a7f0-71b9-4257-b8d3-e0eb36bac26a.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=lE9WccLjhiJTDRDSMzcjshWOSEg%3D",
            61 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/15d1fb6c-0721-490f-ae4b-62e2f34b0535.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=D%2FG1LnPd7Dcbb3xM%2BBKp%2B44pR28%3D",
            65 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/3aba16cc-59b6-4e0f-94dd-a6e0ae364113.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=8mvHvWOS09Jjor00znZAo%2Fsd080%3D",
            66 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/a7dbba94-5af5-4f7e-a92b-f97c8134dd76.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=cPHV1HOCg4hLbktgBmOhZFhjEDk%3D",
            69 => "https://zooniverse-static.s3.amazonaws.com/panoptes-uploads.zooniverse.org/production/workflow_classifications_export/82bb3d0e-25aa-4e9b-afbc-1a1edf8ad30e.csv?AWSAccessKeyId=AKIAJ7CH25FJAJXZNYMA&Expires=1491857340&Signature=gWfCTPTuv%2F0L8HezewjPyjOzKhc%3D",
        ];

    }
}
