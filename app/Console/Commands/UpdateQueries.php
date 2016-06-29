<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\FaqCategory;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\TeamCategory;
use Illuminate\Console\Command;


class UpdateQueries extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';
    /**
     * @var FaqCategory
     */
    private $faqCategory;
    /**
     * @var TeamCategory
     */
    private $teamCategory;
    /**
     * @var Group
     */
    private $group;


    /**
     * UpdateQueries constructor.
     */
    public function __construct(FaqCategory $faqCategory, TeamCategory $teamCategory, Group $group)
    {
        parent::__construct();
        $this->faqCategory = $faqCategory;
        $this->teamCategory = $teamCategory;
        $this->group = $group;
    }

    /**
     * handle
     */
    public function handle()
    {
        $groups = $this->group->skipCache()->all();
        foreach ($groups as $group)
        {
            $group->name = $group->name === 'admins' ? 'Admin' : ucwords(str_replace('-', ' ', $group->name));
            echo $group->name . PHP_EOL;
            $this->group->update(['name' => $group->name], $group->id);
        }
        
        $faqs = $this->faqCategory->skipCache()->all();
        foreach ($faqs as $faq)
        {
            $faq->name = ucwords(str_replace('-', ' ', $faq->name));
            echo $faq->name . PHP_EOL;
            $this->faqCategory->update(['name' => $faq->name], $faq->id);
        }

        $teams = $this->teamCategory->skipCache()->all();
        foreach ($teams as $team)
        {
            $team->name = ucwords(str_replace('-', ' ', $team->name));
            echo $team->name . PHP_EOL;
            $this->teamCategory->update(['name' => $team->name], $team->id);
        }
    }
}