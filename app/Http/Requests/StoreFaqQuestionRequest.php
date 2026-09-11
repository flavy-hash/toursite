<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqQuestionRequest extends FormRequest
{
    /** Errors go to their own bag so they surface in the FAQ dialog. */
    protected $errorBag = 'faq';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:10', 'max:255'],
            'asked_by_name' => ['nullable', 'string', 'max:120'],
            'asked_by_email' => ['nullable', 'email', 'max:160'],

            // Honeypot: a real person never sees this field, so anything in it
            // came from a bot. Same approach as the review form.
            'website' => ['nullable', 'size:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'question.required' => 'Please type your question.',
            'question.min' => 'That is a little short — give us a bit more to go on.',
            'question.max' => 'Please keep the question under 255 characters.',
            'website.size' => 'Your submission could not be accepted.',
        ];
    }
}
