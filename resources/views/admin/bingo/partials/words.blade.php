@for($i=0; $i < 24; $i++)
    @if(!($i % 2))
        <div class="row">
            <div class="col-sm-10">
                <div class="input-group">
                    <div class="col mb-4">
                        <input type="text"
                               class="form-control {{ ($errors->has("words.$i.word")) ? 'is-invalid' : '' }}"
                               id="words[{{ $i }}][word]"
                               name="words[{{ $i }}][word]"
                               value="{{ old("words.$i.word", $words[$i]->word ?? '') }}">
                        <span class="invalid-feedback">{{ $errors->first("words.$i.word") }}</span>
                        <input type="hidden" id="words[{{ $i }}][id]" name="words[{{ $i }}][id]"
                               value="{{ old("words.$i.id", $words[$i]->id ?? '') }}">
                    </div>
    @else
                    <div class="col mb-4">
                        <input type="text"
                               class="form-control {{ ($errors->has("words.$i.word")) ? 'is-invalid' : '' }}"
                               id="words[{{ $i }}][word]"
                               name="words[{{ $i }}][word]"
                               value="{{ old("words.$i.word", $words[$i]->word ?? '') }}">
                        <span class="invalid-feedback">{{ $errors->first("words.$i.word") }}</span>
                        <input type="hidden" id="words[{{ $i }}][id]" name="words[{{ $i }}][id]"
                               value="{{ old("words.$i.id", $words[$i]->id ?? '') }}">
                    </div>
                </div>
            </div>
        </div>
    @endif
@endfor
