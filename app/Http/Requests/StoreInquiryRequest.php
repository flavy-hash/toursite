<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInquiryRequest extends FormRequest
{
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
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],

            // Must be a published package, or blank for a general enquiry.
            'tour_slug' => [
                'nullable',
                'string',
                Rule::exists('tours', 'slug')->where('is_published', true),
            ],

            'travel_date' => ['nullable', 'date', 'after:today'],
            'travellers' => ['required', 'integer', 'min:1', 'max:40'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'travel_date.after' => 'Please choose a departure date in the future.',
            'tour_slug.exists' => 'That package is no longer available - please pick another.',
            'travellers.max' => 'For groups over 40, please contact us directly.',
        ];
    }
}
