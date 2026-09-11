<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqResource;
use App\Models\Faq;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
    protected static string $resource = FaqResource::class;

    /** A new question joins the end of the list rather than the top. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['sort_order'] ?? null)) {
            $data['sort_order'] = (int) Faq::max('sort_order') + 1;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
