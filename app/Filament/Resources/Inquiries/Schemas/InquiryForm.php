<?php

namespace App\Filament\Resources\Inquiries\Schemas;

use App\Models\Inquiry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Enquiry')
                ->description('Submitted from the website. Left read-only so the record matches what was actually sent.')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->disabled(),
                    TextInput::make('email')->disabled(),
                    TextInput::make('phone')->disabled(),
                    TextInput::make('tour_name')->label('Package')->disabled(),
                    DatePicker::make('travel_date')->label('Preferred departure')->disabled(),
                    TextInput::make('travellers')->disabled(),
                    Textarea::make('message')->rows(5)->disabled()->columnSpanFull(),
                ]),

            Section::make('Handling')
                ->schema([
                    Select::make('status')
                        ->required()
                        ->default(Inquiry::NEW)
                        ->options(Inquiry::statusOptions()),
                ]),
        ]);
    }
}
