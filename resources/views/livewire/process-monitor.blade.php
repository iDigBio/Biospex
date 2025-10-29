<!-- resources/views/livewire/process-monitor.blade.php -->
<div wire:poll.60s>
    <p class="text-center color-action small mb-4">
        {{ t('Stats update every minute.') }}
    </p>

    <div class="m-4">
        <h4>{{ t('Ocr Processes') }}</h4>
        <div id="ocr-html">
            {!! $ocrHtml ?: '<p class="text-muted">' . t('No processes running at this time') . '</p>' !!}
        </div>
    </div>

    <div class="m-4">
        <h4>{{ t('Export Processes') }}</h4>
        <div id="export-html">
            {!! $exportHtml ?: '<p class="text-muted">' . t('No processes running at this time') . '</p>' !!}
        </div>
    </div>
</div>