<div class="form-group mt-4">
    @if ($errors->has('g-recaptcha-response'))
        <span class="help-block color-action">
                            <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                            </span>
    @endif
    {!! NoCaptcha::renderJs() !!}
    {!! NoCaptcha::display() !!}
</div>