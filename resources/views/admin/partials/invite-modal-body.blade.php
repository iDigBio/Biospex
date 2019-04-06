@if($error)
    <div class="col-md-8 mx-auto">
        <h3 class="text-center">{{ __('You do not have sufficient permissions.') }}</h3>
    </div>
@else
    <div class="col-sm-8 mx-auto">
        <h3 class="text-center">{{ __('Invite users to :group group.', ['group' => $group->title]) }}</h3>
        <form action="{{ route('admin.invites.store', [$group->id]) }}" method="post" role="form">
            {!! csrf_field() !!}
            <input type="hidden" name="entries" value="{{ old('entries', $inviteCount) }}">
            <div class="controls">
            @for($i=0; $i < $inviteCount; $i++)
                <div class="entry mb-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text btn btn-primary btn-add px-3 py-0"
                                  id="basic-addon{{$i}}"><i class="fas fa-plus"></i></span>
                        </div>
                        <input type="email"
                               class="form-control {{ ($errors->has("invites.$i.email")) ? 'is-invalid' : '' }}"
                               id="invites[][email]" name="invites[][email]"
                               value="{{ old("invites.$i.email", $group->invites[$i]->email ?? '') }}"
                               placeholder="{{ __('Email') }}" required>
                        <span class="invalid-feedback">{{ $errors->first("invites.$i.email") }}</span>
                    </div>
                </div>
            @endfor
            </div>
            <div class="form-group col-md-8 d-flex align-items-start justify-content-between mx-auto">
                <button type="button" class="btn btn-primary mr-4"
                        data-dismiss="modal">{{ __('CANCEL') }}
                </button>
                <button type="submit" class="btn btn-primary">{{ __('SUBMIT') }}</button>
            </div>
        </form>
    </div>
@endif