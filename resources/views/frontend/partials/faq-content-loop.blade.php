<div class="panel panel-info">
    <div class="panel-heading" role="tab" id="heading{{ $faq->id }}">
        <h4 class="panel-title">
            <a role="button" data-toggle="collapse" data-parent="#accordion{{ $category->id }}"
               href="#collapse{{ $faq->id }}" aria-expanded="true" aria-controls="collapse{{ $faq->id }}">
                {{ $faq->question }}
            </a>
        </h4>
    </div>
    <div id="collapse{{ $faq->id }}" class="panel-collapse collapse {{ $key === 0 ? 'in' : '' }}" role="tabpanel"
         aria-labelledby="heading{{ $faq->id }}">
        <div class="panel-body">
            {{ $faq->answer }}
        </div>
    </div>
</div>