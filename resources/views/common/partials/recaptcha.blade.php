<div class="col-xs-12">
    <div class="form-group">
        <div class="col-xs-6 col-sm-6 col-md-6">
        @if ($errors->has('g-recaptcha-response'))
            <span class="help-block color-action">
                <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
            </span>
        @endif
        {!! NoCaptcha::renderJs() !!}
        {!! NoCaptcha::display() !!}
        </div>
    </div>
</div>