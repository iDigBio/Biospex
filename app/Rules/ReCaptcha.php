<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class ReCaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $response = Http::asForm()->post(config('services.recaptcha.url'), [
            'secret'   => config('services.recaptcha.secret_key'),
            'response' => $value,
        ]);

        if (! ($response->json()["success"] ?? false)) {
            $fail('The google recaptcha is required.');
        }
    }
}