@if(($expedition->completed === 0) && $expedition->stat->local_subject_count > 0)
    @if(! zooniverse_export_file_check($expedition))
        <div class="d-flex align-items-start mb-0" data-hover="tooltip" data-html="true"
             title="<div class='text-left'>{{ t('The Export file has not been generated. Select the tools icon for the Expedition and select Generate Export File.') }}</div>">
        <span role="button" class="small text-danger">
        {{ t('Export File generation needed.') }}
        <i class="fa fa-question-circle"></i>
        </span>
        </div>
    @elseif(!isset($expedition->panoptesProject) || $expedition->panoptesProject->panoptes_workflow_id === null)
        <div class="d-flex align-items-start mb-0" data-hover="tooltip" data-html="true"
             title="<div class='text-left'>{{ t('The Expedition export file has been generated but workflow id has not been entered. Select the tools icon for the Expedition and select Edit Workflow Id.') }}</div>">
        <span role="button" class="small text-danger">
        {{ t('Zooniverse Workflow Id missing.') }}
        <i class="fa fa-question-circle"></i>
        </span>
        </div>
    @elseif($expedition->panoptesProject->panoptes_workflow_id !== null && $expedition->panoptesProject->panoptes_project_id === null)
        <div class="d-flex align-items-start mb-0" data-hover="tooltip" data-html="true"
             title="<div class='text-left'>{{ t('Expedition is missing workflow information. This may be due to an incorrect workflow id. Please check the workflow id in the Expedition tools icon. If correct, please contact the administration.') }}</div>">
        <span role="button" class="small text-danger">
        {{ t('Zooniverse Project Id missing.') }}
        <i class="fa fa-question-circle"></i>
        </span>
        </div>
    @elseif($expedition->workflowManager === null || $expedition->workflowManager->stopped === 1)
        <div class="d-flex align-items-start mb-0" data-hover="tooltip" data-html="true"
             title="<div class='text-left'>{{ t('The Expedition is ready to start processing. Please select Start Expedition Processing in the Expedition tools icon when you are ready to begin.') }}</div>">
        <span role="button" class="small text-danger">
        {{ t('Expedition process not running.') }}
        <i class="fa fa-question-circle"></i>
        </span>
        </div>
    @endif
@endif