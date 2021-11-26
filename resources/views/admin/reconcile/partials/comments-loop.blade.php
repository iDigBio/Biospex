<div class="col-sm-2 talk-name">
    {{ $comment['user_login'] }}
</div>
<div class="col-sm-10 talk-comment">
    <p>{{ DateHelper::formatDate($comment['created_at'], 'F d Y, g:ia') }}</p>
    {!! \GrahamCampbell\Markdown\Facades\Markdown::convertToHtml(str_replace('+tab+', '', $comment['body'])) !!}
</div>
