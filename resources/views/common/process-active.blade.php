<div class="mb-3 p-3 bg-light rounded border">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 color-action font-weight-bold">{{ $title }}</h6>
        <small class="text-muted">{{ $stage }}</small>
    </div>

    @php
        $showBar = false;
        $percentage = 0;
        $processed = 0;
        $total = 0;

        // Always use the passed numbers
        if (isset($processedCount) && isset($totalCount)) {
            $processed = $processedCount;
            $total = $totalCount;
            $showBar = true;
        }

        if ($showBar) {
            $percentage = $total > 0 ? round(($processed / $total) * 100) : 0;
        }
    @endphp

    @if($showBar)
        <div class="progress-bar d-flex align-items-center justify-content-center text-white fw-bold"
             style="width: {{ $percentage }}%; background: linear-gradient(90deg, #406288 0, #21304c 99%); font-size: 13px; min-width: 60px;"
             role="progressbar">
            {{ number_format($processed) }} / {{ number_format($total) }}
            <span class="ms-2">{{ $percentage }}%</span>
        </div>
    @else
        {{-- Fallback if counts are missing for some reason --}}
        <div class="text-muted small fst-italic">Waiting to start...</div>
    @endif
</div>