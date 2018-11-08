<h2 class="mt-4">{{ $category->name }}</h2>
<div id="accordion{{ $category->id }}">
    @foreach($category->faqs as $key => $faq)
    <div class="faq card">
        <div class="card-header" id="heading{{ $faq->id }}">
            <button class="faq btn btn-link text-left p-0" data-toggle="collapse"
                    data-target="#collapse{{ $faq->id }}" aria-expanded="true"
                    aria-controls="collapse{{ $faq->id }}">
                {{ $faq->question }}
            </button>
        </div>

        <div id="collapse{{ $faq->id }}" class="collapse"
             aria-labelledby="heading{{ $faq->id }}"
             data-parent="#accordion{{ $category->id }}">
            <div class="card-body">
                {!! $faq->answer !!}
            </div>
        </div>
    </div>
    @endforeach
</div>