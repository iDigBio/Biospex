<hr class="header mx-auto" style="width:300px;">
<h4>{{ $actor->title }}</h4>
@if($expedition->stat->local_subject_count > 0)
    <a href="{{ route('admin.downloads.export', [$expedition->project->id, $expedition->id]) }}" class="prevent-default btn btn-primary rounded-0 mb-1"
       data-method="get"
       data-confirm="confirmation"
       data-title="{{ t('Generate Export File') }}"
       data-content="{{ t('This will generate a new export file. Any previous exports will be overwritten. Do you wish to Continue?') }}">
        {{t('Generate Export File') }}</a>
@endif
<a href="" class="prevent-default btn btn-primary rounded-0 mb-1"
   data-dismiss="modal"
   data-toggle="modal"
   data-target="#global-modal"
   data-size="modal-lg"
   data-url="{{ route('admin.zooniverse.workflowShowForm', [$expedition->project->id, $expedition->id]) }}"
   data-title="{{ t('Edit Workflow Id') }}"> {{ t('Edit Workflow Id') }}</a>
@if(\App\Facades\GeneralHelper::exportFileCheck($expedition))

    @if(\App\Facades\GeneralHelper::checkPanoptesWorkflow($expedition))
        @if ($expedition->workflowManager === null || $expedition->workflowManager->stopped === 1)
            @unless($expedition->stat->local_subject_count === 0)
                <a href="{{ route('admin.zooniverse.process', [$expedition->project_id, $expedition->id]) }}"
                   class="prevent-default btn btn-primary rounded-0 mb-1 green"
                   data-method="post"
                   data-confirm="confirmation"
                   data-title="{{ t('Start Expedition Processing') }}?"
                   data-content="{{ t('This will begin processing the Expedition. After starting, Subjects cannot be added or removed. Do you wish to Continue?') }}">
                    {{ t('Start Expedition Processing') }}</a>
            @endunless
        @else
            <a href="{{ route('admin.zooniverse.stop', [$expedition->project_id, $expedition->id]) }}"
               class="prevent-default btn btn-primary rounded-0 mb-1"
               data-method="delete"
               data-confirm="confirmation"
               data-title="{{ t('Stop Processing') }}"
               data-content="{{ t('This will stop the Expedition Process. However, Subjects cannot be added since process was already started. Do you wish to Continue?') }}'">
                {{ t('Stop Expedition Processing') }}</a>
        @endif
    @endif
@endif
@if($actor->pivot->state === 3  && $actor->id === 2)
    {!! $actor->present()->reconcile_expert_review_btn !!}
@endif
