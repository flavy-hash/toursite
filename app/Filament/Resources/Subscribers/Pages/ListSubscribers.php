<?php

namespace App\Filament\Resources\Subscribers\Pages;

use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Mail\PackageNewsletter;
use App\Models\Subscriber;
use App\Models\Tour;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ListSubscribers extends ListRecords
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendNewsletter')
                ->label('Send newsletter')
                ->icon('heroicon-m-paper-airplane')
                ->color('primary')
                // Nothing to send to, so do not offer it.
                ->hidden(fn (): bool => Subscriber::subscribed()->doesntExist())
                ->modalHeading('Send a newsletter')
                ->modalDescription(fn (): string => 'Goes to all '
                    . Subscriber::subscribed()->count()
                    . ' active subscribers. Unsubscribed addresses are skipped.')
                ->modalSubmitActionLabel('Send it')
                ->schema([
                    TextInput::make('subject')
                        ->required()
                        ->maxLength(140)
                        ->placeholder('New departures for the dry season'),

                    Textarea::make('intro')
                        ->label('Message')
                        ->rows(5)
                        ->maxLength(2000)
                        ->placeholder('A short note to open the email. Plain text — line breaks are kept.'),

                    Select::make('tours')
                        ->label('Packages to feature')
                        ->multiple()
                        ->options(fn () => Tour::published()->ordered()->pluck('name', 'id')->all())
                        ->helperText('Each appears with its photo, price and a link.'),
                ])
                ->action(function (array $data): void {
                    $tours = Tour::published()
                        ->whereIn('id', $data['tours'] ?? [])
                        ->ordered()
                        ->get();

                    $recipients = Subscriber::subscribed()->get();

                    $sent = 0;
                    $failed = [];

                    /*
                     * One message per subscriber rather than a shared BCC, so
                     * each footer can carry an unsubscribe link signed for that
                     * individual address.
                     *
                     * Each send is isolated: one unreachable mail server, or a
                     * single address the provider rejects, must not abandon the
                     * rest of the list or throw the panel to an error page.
                     */
                    foreach ($recipients as $subscriber) {
                        try {
                            Mail::to($subscriber->email)->queue(new PackageNewsletter(
                                subscriber: $subscriber,
                                subjectLine: $data['subject'],
                                intro: $data['intro'] ?? null,
                                tours: $tours,
                            ));

                            $sent++;
                        } catch (Throwable $e) {
                            $failed[] = $subscriber->email;

                            Log::warning('Newsletter could not be sent.', [
                                'subscriber' => $subscriber->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    if ($failed === []) {
                        Notification::make()
                            ->title('Newsletter sent')
                            ->body($sent . ' ' . str('email')->plural($sent) . ' away.')
                            ->success()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title($sent > 0 ? 'Partly sent' : 'Nothing could be sent')
                        ->body($sent . ' sent, ' . count($failed) . ' failed. Check your mail settings '
                            . 'with "php artisan mail:test", then send again — already-delivered '
                            . 'addresses will simply receive it twice.')
                        ->danger()
                        ->persistent()
                        ->send();
                }),
        ];
    }
}
