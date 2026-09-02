<?php

namespace App\Http\Requests;

use App\Models\Tour;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    /** Its own bag, so a rejected review cannot light up other forms. */
    protected $errorBag = 'review';

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
            'location' => ['nullable', 'string', 'max:120'],

            'tour_slug' => ['nullable', 'string', Rule::exists('tours', 'slug')->where('is_published', true)],

            'rating' => ['required', 'integer', 'between:1,5'],
            'rating_guiding' => ['nullable', 'integer', 'between:1,5'],
            'rating_value' => ['nullable', 'integer', 'between:1,5'],

            'title' => ['nullable', 'string', 'max:140'],
            'body' => ['required', 'string', 'min:20', 'max:2000'],

            'travelled_on' => ['nullable', 'date', 'before_or_equal:today'],

            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],

            // Honeypot: hidden from people, irresistible to bots.
            'website' => ['nullable', 'prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Please choose a star rating.',
            'body.min' => 'Please write at least a sentence or two.',
            'travelled_on.before_or_equal' => 'The travel date cannot be in the future.',
            'photo.max' => 'Photos must be 2 MB or smaller.',
            'website.prohibited' => 'That submission looked automated.',
        ];
    }
}
