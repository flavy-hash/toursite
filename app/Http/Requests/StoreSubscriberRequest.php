<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriberRequest extends FormRequest
{
    /**
     * Errors go to their own bag so a failed sign-up cannot light up the
     * booking form that shares the page.
     */
    protected $errorBag = 'newsletter';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:180'],

            // Honeypot: a real person never sees this field, so anything in it
            // is a bot. Named innocuously enough that they fill it in.
            'website' => ['nullable', 'prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'That does not look like a valid email address.',
            'website.prohibited' => 'That submission looked automated.',
        ];
    }
}
