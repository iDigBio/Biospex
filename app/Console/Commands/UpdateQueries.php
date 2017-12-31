<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use File;


class UpdateQueries extends Command
{

    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * UpdateQueries constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire command
     */
    public function handle()
    {
        try
        {
            DB::unprepared(File::get(base_path('resources/mysql/null-columns.sql')));
        }
        catch(FileNotFoundException $exception)
        {
            echo $exception->getMessage() . PHP_EOL;
            exit;
        }
        /*
        DB::table('migrations')->truncate();
        DB::insert("INSERT INTO `migrations` (`migration`, `batch`) VALUES
            ('2017_11_13_153357_create_actor_contacts_table', 0),
            ('2017_11_13_153357_create_actor_expedition_table', 0),
            ('2017_11_13_153357_create_actor_workflow_table', 0),
            ('2017_11_13_153357_create_actors_table', 0),
            ('2017_11_13_153357_create_amcharts_table', 0),
            ('2017_11_13_153357_create_api_subscribers_table', 0),
            ('2017_11_13_153357_create_downloads_table', 0),
            ('2017_11_13_153357_create_expedition_stats_table', 0),
            ('2017_11_13_153357_create_expeditions_table', 0),
            ('2017_11_13_153357_create_export_queues_table', 0),
            ('2017_11_13_153357_create_failed_jobs_table', 0),
            ('2017_11_13_153357_create_faq_categories_table', 0),
            ('2017_11_13_153357_create_faqs_table', 0),
            ('2017_11_13_153357_create_group_permission_table', 0),
            ('2017_11_13_153357_create_group_user_table', 0),
            ('2017_11_13_153357_create_groups_table', 0),
            ('2017_11_13_153357_create_headers_table', 0),
            ('2017_11_13_153357_create_imports_table', 0),
            ('2017_11_13_153357_create_invites_table', 0),
            ('2017_11_13_153357_create_ltm_translations_table', 0),
            ('2017_11_13_153357_create_metas_table', 0),
            ('2017_11_13_153357_create_nfn_workflows_table', 0),
            ('2017_11_13_153357_create_notices_table', 0),
            ('2017_11_13_153357_create_notifications_table', 0),
            ('2017_11_13_153357_create_ocr_csv_table', 0),
            ('2017_11_13_153357_create_ocr_queues_table', 0),
            ('2017_11_13_153357_create_password_resets_table', 0),
            ('2017_11_13_153357_create_permission_user_table', 0),
            ('2017_11_13_153357_create_permissions_table', 0),
            ('2017_11_13_153357_create_profiles_table', 0),
            ('2017_11_13_153357_create_projects_table', 0),
            ('2017_11_13_153357_create_properties_table', 0),
            ('2017_11_13_153357_create_resources_table', 0),
            ('2017_11_13_153357_create_state_counties_table', 0),
            ('2017_11_13_153357_create_team_categories_table', 0),
            ('2017_11_13_153357_create_teams_table', 0),
            ('2017_11_13_153357_create_throttle_table', 0),
            ('2017_11_13_153357_create_transcription_locations_table', 0),
            ('2017_11_13_153357_create_user_grid_field_table', 0),
            ('2017_11_13_153357_create_user_grid_fields_table', 0),
            ('2017_11_13_153357_create_users_table', 0),
            ('2017_11_13_153357_create_workflow_managers_table', 0),
            ('2017_11_13_153357_create_workflows_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_actor_contacts_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_actor_expedition_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_actor_workflow_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_amcharts_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_downloads_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_expedition_stats_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_expeditions_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_export_queues_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_faqs_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_group_permission_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_group_user_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_groups_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_headers_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_imports_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_invites_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_metas_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_nfn_workflows_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_notifications_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_ocr_queues_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_permission_user_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_profiles_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_projects_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_teams_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_transcription_locations_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_user_grid_field_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_user_grid_fields_table', 0),
            ('2017_11_13_153359_add_foreign_keys_to_workflow_managers_table', 0),
            ('2017_11_13_153734_create_actor_contacts_table', 0),
            ('2017_11_13_153734_create_actor_expedition_table', 0),
            ('2017_11_13_153734_create_actor_workflow_table', 0),
            ('2017_11_13_153734_create_actors_table', 0),
            ('2017_11_13_153734_create_amcharts_table', 0),
            ('2017_11_13_153734_create_api_subscribers_table', 0),
            ('2017_11_13_153734_create_downloads_table', 0),
            ('2017_11_13_153734_create_expedition_stats_table', 0),
            ('2017_11_13_153734_create_expeditions_table', 0),
            ('2017_11_13_153734_create_export_queues_table', 0),
            ('2017_11_13_153734_create_failed_jobs_table', 0),
            ('2017_11_13_153734_create_faq_categories_table', 0),
            ('2017_11_13_153734_create_faqs_table', 0),
            ('2017_11_13_153734_create_group_permission_table', 0),
            ('2017_11_13_153734_create_group_user_table', 0),
            ('2017_11_13_153734_create_groups_table', 0),
            ('2017_11_13_153734_create_headers_table', 0),
            ('2017_11_13_153734_create_imports_table', 0),
            ('2017_11_13_153734_create_invites_table', 0),
            ('2017_11_13_153734_create_ltm_translations_table', 0),
            ('2017_11_13_153734_create_metas_table', 0),
            ('2017_11_13_153734_create_nfn_workflows_table', 0),
            ('2017_11_13_153734_create_notices_table', 0),
            ('2017_11_13_153734_create_notifications_table', 0),
            ('2017_11_13_153734_create_ocr_csv_table', 0),
            ('2017_11_13_153734_create_ocr_queues_table', 0),
            ('2017_11_13_153734_create_password_resets_table', 0),
            ('2017_11_13_153734_create_permission_user_table', 0),
            ('2017_11_13_153734_create_permissions_table', 0),
            ('2017_11_13_153734_create_profiles_table', 0),
            ('2017_11_13_153734_create_projects_table', 0),
            ('2017_11_13_153734_create_properties_table', 0),
            ('2017_11_13_153734_create_resources_table', 0),
            ('2017_11_13_153734_create_state_counties_table', 0),
            ('2017_11_13_153734_create_team_categories_table', 0),
            ('2017_11_13_153734_create_teams_table', 0),
            ('2017_11_13_153734_create_throttle_table', 0),
            ('2017_11_13_153734_create_transcription_locations_table', 0),
            ('2017_11_13_153734_create_user_grid_field_table', 0),
            ('2017_11_13_153734_create_user_grid_fields_table', 0),
            ('2017_11_13_153734_create_users_table', 0),
            ('2017_11_13_153734_create_workflow_managers_table', 0),
            ('2017_11_13_153734_create_workflows_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_actor_contacts_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_actor_expedition_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_actor_workflow_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_amcharts_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_downloads_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_expedition_stats_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_expeditions_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_export_queues_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_faqs_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_group_permission_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_group_user_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_groups_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_headers_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_imports_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_invites_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_metas_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_nfn_workflows_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_notifications_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_ocr_queues_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_permission_user_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_profiles_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_projects_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_teams_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_transcription_locations_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_user_grid_field_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_user_grid_fields_table', 0),
            ('2017_11_13_153736_add_foreign_keys_to_workflow_managers_table', 0);"
        );
        */
    }
}