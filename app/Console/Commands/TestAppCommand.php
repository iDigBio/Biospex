<?php

namespace App\Console\Commands;

use App\Services\Image\Image;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var Image
     */
    private $image;

    /**
     * AmChartJob constructor.
     * @param Filesystem $filesystem
     * @param Image $image
     */
    public function __construct(Filesystem $filesystem, Image $image)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->image = $image;
    }

    public function fire()
    {

        $dir = 'bb0a5687-6012-4ad6-a337-98115f11fee1';
        /*
        $commands = [
            'thumbnail' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -filter Triangle -define filter:support=2 -define jpeg:size=1540x1540 -thumbnail 1540x1540 -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 -define jpeg:fancy-upsampling=off -define jpeg:extent=400kb -interlace none -colorspace sRGB -strip -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'resizeBasic' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -quality 82 -define jpeg:extent=400kb -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'resizeVars' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -filter Triangle -define filter:support=2 -define jpeg:size=1540x1540 -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 -define jpeg:fancy-upsampling=off -define jpeg:extent=400kb -interlace none -colorspace sRGB -strip -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'removeColorSpace' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -filter Triangle -define filter:support=2 -define jpeg:size=1540x1540 -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 -define jpeg:fancy-upsampling=off -define jpeg:extent=400kb -interlace none -strip -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'removeStrip' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -filter Triangle -define filter:support=2 -define jpeg:size=1540x1540 -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 -define jpeg:fancy-upsampling=off -define jpeg:extent=400kb -interlace none -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'removeInterlace' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -filter Triangle -define filter:support=2 -define jpeg:size=1540x1540 -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 -define jpeg:fancy-upsampling=off -define jpeg:extent=400kb -interlace none -strip -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'removeStripInterlace' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -filter Triangle -define filter:support=2 -define jpeg:size=1540x1540 -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 -define jpeg:fancy-upsampling=off -define jpeg:extent=400kb -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'removeStripInterlaceFancy' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -filter Triangle -define filter:support=2 -define jpeg:size=1540x1540 -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 -define jpeg:extent=400kb -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'removeStripInterlaceFancyPosterize' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -filter Triangle -define filter:support=2 -define jpeg:size=1540x1540 -unsharp 0.25x0.25+8+0.065 -dither None -quality 82 -define jpeg:extent=400kb -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'removeStripInterlaceFancyPosterizeDither' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -filter Triangle -define filter:support=2 -define jpeg:size=1540x1540 -unsharp 0.25x0.25+8+0.065 -quality 82 -define jpeg:extent=400kb -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'removeStripInterlaceFancyPosterizeDitherUnsharp' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -filter Triangle -define filter:support=2 -define jpeg:size=1540x1540 -quality 82 -define jpeg:extent=400kb -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'mogrifySample' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -sample 1540x1540 -filter Triangle -define jpeg:size=1540x1540 -quality 80 -define jpeg:extent=400kb -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
            'removeStripInterlaceFancyPosterizeDitherUnsharpSupport' => 'mogrify -path /vagrant/biospex/storage/scratch/test/out -resize 1540x1540 -filter Triangle -define jpeg:size=1540x1540 -quality 80 -define jpeg:extent=400kb -format jpg /vagrant/biospex/storage/scratch/test/*',
            'removeStripInterlaceFancyPosterizeDitherUnsharpSupportTriangle' => 'mogrify -path /vagrant/biospex/storage/scratch/' . $dir . '/' . $dir . ' -resize 1540x1540 -define jpeg:size=1540x1540 -quality 82 -define jpeg:extent=400kb -format jpg /vagrant/biospex/storage/scratch/' . $dir . '/orig/*',
        ];
        */
        $command = 'mogrify -path /data/web/biospex/development/storage/scratch/' . $dir . '/out -resize 1540x1540 -filter Triangle -define jpeg:size=1540x1540 -quality 80 -define jpeg:extent=400kb -format jpg /data/web/biospex/development/storage/scratch/' . $dir . '/orig/*';
        $start = $this->start();
        $result = exec($command);
        $finish = $this->finish($start);
        print_r($result);
        $count = count(glob(storage_path("scratch/$dir/png/*.png")));
        echo 'finished in ' . $finish . ' seconds.' . PHP_EOL;
        echo 'image per second: ' . round($finish/$count) . ' seconds.' . PHP_EOL;
        //$this->filesystem->deleteDirectory(storage_path("scratch/$dir/$dir"), true);

        /*
        foreach ($commands as $key => $command)
        {
            $this->process($dir, $command, $key);
        }
        */
    }

    private function process($dir, $command, $key = null)
    {
        $start = $this->start();
        $result = exec($command);
        $finish = $this->finish($start);
        print_r($result);
        $count = count(glob(storage_path("scratch/$dir/png/*.png")));
        echo $key . ' finished in ' . $finish . ' seconds.' . PHP_EOL;
        echo $key . ' image per second: ' . round($finish/$count) . ' seconds.' . PHP_EOL;
        //$this->filesystem->deleteDirectory(storage_path("scratch/$dir/$dir"), true);
    }

    private function start()
    {
        return microtime(true);
    }

    private function finish($start)
    {
        return microtime(true) - $start;
    }
}

