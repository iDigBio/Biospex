<h4>{{ $actor->title }}</h4>
@if($expedition->stat->local_subject_count > 0)
    {!! $expedition->present()->expedition_export_btn !!}
@endif
@if(\App\Facades\GeneralHelper::exportFileCheck($actor->id, $expedition))
    {!! $expedition->present()->expedition_workflow_btn  !!}
    @if(\App\Facades\GeneralHelper::checkPanoptesWorkflow($expedition))
        @if ($expedition->workflowManager === null || $expedition->workflowManager->stopped === 1)
            {!!
            $expedition->stat->local_subject_count === 0 ? '' :
                $expedition->present()->expedition_process_start_btn
            !!}
        @else
            {!! $expedition->present()->expedition_process_stop_btn !!}
        @endif
    @endif
@endif
@if($actor->pivot->state === 2  && $actor->id === 2)
    {!! $actor->present()->reconcile_expert_review_btn !!}
@endif
<hr class="header mx-auto" style="width:300px;">
