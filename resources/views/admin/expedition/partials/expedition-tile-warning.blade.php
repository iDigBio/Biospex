<!--
Check expedition->panoptesProject->panoptes_workflow_id && expedition->panoptesProject->panoptes_project_id are not null
Check expedition->stat->local_subject_count for subjects
-->
@if($expedition->stat->local_subject_count > 0)
    @if(!isset($expedition->panoptesProject) || $expedition->panoptesProject->panoptes_workflow_id === null)
        <div class="d-flex align-items-start mb-0" data-hover="tooltip" data-html="true"
             title="<div class='text-left'>{{ t('The Expedition has subjects but workflow id has not been entered. Select the tools icon for the Expedition to add workflow id.') }}</div>">
        <span role="button" class="small text-danger">
        {{ t('Zooniverse Workflow Id missing.') }}
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        </span>
        </div>
    @elseif($expedition->panoptesProject->panoptes_workflow_id !== null && $expedition->panoptesProject->panoptes_project_id === null)
        <div class="d-flex align-items-start mb-0" data-hover="tooltip" data-html="true"
             title="<div class='text-left'>{{ t('Expedition is missing workflow information. This may be due to an incorrect workflow id. Please check the workflow id in the Expedition tools icon. If correct, please contact the administration.') }}</div>">
        <span role="button" class="small text-danger">
        {{ t('Zooniverse Project Id missing.') }}
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        </span>
        </div>
    @elseif($expedition->workflowManager === null || $expedition->workflowManager->stopped === 1)
        <div class="d-flex align-items-start mb-0" data-hover="tooltip" data-html="true"
             title="<div class='text-left'>{{ t('The Expedition is ready to start processing. Please select Start Processing in the Expedition tools icon when you are ready to begin.') }}</div>">
        <span role="button" class="small text-danger">
        {{ t('Expedition process not running.') }}
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        </span>
        </div>
    @endif
@endif