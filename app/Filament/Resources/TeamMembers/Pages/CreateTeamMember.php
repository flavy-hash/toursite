<?php

namespace App\Filament\Resources\TeamMembers\Pages;

use App\Filament\Resources\TeamMembers\TeamMemberResource;
use App\Models\TeamMember;
use Filament\Resources\Pages\CreateRecord;

class CreateTeamMember extends CreateRecord
{
    protected static string $resource = TeamMemberResource::class;

    /** New people go to the end of the list rather than jumping to the top. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['sort_order'] ?? null)) {
            $data['sort_order'] = (int) TeamMember::max('sort_order') + 1;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
