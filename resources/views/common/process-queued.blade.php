<!-- resources/views/common/process-queued.blade.php -->
<div class="mb-3 p-3 bg-light rounded border">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 color-action font-weight-bold">{{ $title }}</h6>
        <small class="text-muted">{{ $stage }}</small>
    </div>
    @if($queuePosition)
        <div class="text-muted small fst-italic">
            {{ $queuePosition }}
        </div>
    @endif
</div>