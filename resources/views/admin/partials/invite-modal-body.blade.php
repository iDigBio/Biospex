@if($error)
    <div class="col-md-12 text-center">
        <h3>{{ __('You do not have sufficient permissions.') }}</h3>
    </div>
@else
    <div class="col-12 text-center">
        <h3>{{ __('Invite users to :group group.', ['group' => $group->title]) }}</h3>
        <form class="mx-auto" action="{{ route('admin.invites.store', [$group->id]) }}" method="post" role="form">
            {!! csrf_field() !!}
            <div class="controls col-sm-12">
            @for($i=0; $i < $inviteCount; $i++)
                <div class="entry mb-4">
                    <div class="col-8 mx-auto">
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
                </div>
            @endfor
            </div>
            <div class="form-group col-md-12 text-center">
                <button type="submit" class="btn btn-primary mr-4">{{ __('SUBMIT') }}</button>
            </div>
        </form>
    </div>
@endif