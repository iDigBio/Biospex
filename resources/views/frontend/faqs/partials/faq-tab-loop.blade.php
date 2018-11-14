<div class="tab-pane {{ $category->id === 1 ? 'active' : '' }}" id="{{ $category->name }}">
    <div class="panel-group" id="accordion{{ $category->id }}" role="tablist" aria-multiselectable="true">
        @foreach($category->faqs as $key => $faq)
            @include('front.faqs.partials.faq-content-loop')
        @endforeach
    </div>
</div>