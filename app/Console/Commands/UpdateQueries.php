<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\Team;
use App\Repositories\Contracts\TeamCategory;
use Illuminate\Console\Command;
use App\Repositories\Contracts\Actor;
use App\Repositories\Contracts\Workflow;
use Illuminate\Support\Facades\DB;

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
     * @var TeamCategory
     */
    private $category;
    /**
     * @var Team
     */
    private $team;

    /**
     * UpdateQueries constructor.
     */
    public function __construct(TeamCategory $category, Team $team)
    {
        parent::__construct();
        $this->category = $category;
        $this->team = $team;
    }

    /**
     * handle
     */
    public function handle()
    {
        $categories = $this->getData();

        foreach ($categories as $category)
        {
            $result = $this->category->create($category);

            foreach ($category['members'] as $member)
            {
                $attributes = ['email' => $member['email']];
                $member['team_category_id'] = $result->id;
                $this->team->updateOrCreate($attributes, $member);
            }
        }
    }

    /**
     * Get data for building team.
     * 
     * @return array
     */
    protected function getData()
    {
        return
            [
                [
                    'name'    => 'principle-investigators',
                    'label'   => 'Principle Investigators',
                    'members' => [
                        [
                            'first_name'  => 'Austin',
                            'last_name'   => 'Mast',
                            'institution' => 'Associate Professor, Department of Biological Science, Florida State University',
                            'email'       => 'amast@bio.fsu.edu'
                        ],
                        [
                            'first_name'  => 'Greg',
                            'last_name'   => 'Ricarrdi',
                            'institution' => 'Director, Institute for Digital Information and Scientific Communication, Florida State University',
                            'email'       => 'griccardi@fsu.edu'
                        ],
                    ]
                ],
                [
                    'name'    => 'education-and-outreach-coordinator',
                    'label'   => 'Education and Outreach Coordinator',
                    'members' => [
                        [
                            'first_name'  => 'Libby',
                            'last_name'   => 'Ellwood',
                            'institution' => 'Postdoctoral Scholar, Department of Biological Science, Florida State University',
                            'email'       => 'eellwood@bio.fsu.edu'
                        ],

                    ]
                ],
                [
                    'name'    => 'developers',
                    'label'   => 'Developers',
                    'members' => [
                        [
                            'first_name'  => 'Robert',
                            'last_name'   => 'Bruhn',
                            'institution' => 'Institute for Digital Information and Scientific Communication, Florida State University',
                            'email'       => 'bruhnrp@yahoo.com'
                        ],

                    ]
                ],
                [
                    'name'    => 'past-team-members',
                    'label'   => 'Past Team Members',
                    'members' => [
                        [
                            'first_name'  => 'Jermey',
                            'last_name'   => 'Spinks',
                            'institution' => 'Institute for Digital Information and Scientific Communication, Florida State University',
                            'email'       => 'jeremy@jellybean-design.com'
                        ],

                    ]
                ],
            ];
    }

}