<div class="panel">
    <div role="tab" id="heading-{{ $faq->id }}">
        <h4>
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse-{{ $faq->id }}" aria-expanded="true" aria-controls="collapse-{{ $faq->id }}">
                {{ $faq->question }}
            </a>
        </h4>
    </div>
    <div id="collapse-{{ $faq->id }}" class="collapse" role="tabpanel" aria-labelledby="heading-{{ $faq->id }}">
        {{ $faq->answer }}
    </div>
</div>