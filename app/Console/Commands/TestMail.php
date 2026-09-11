<?php

namespace App\Console\Commands;

use App\Mail\BookingConfirmed;
use App\Mail\SubscriberWelcome;
use App\Models\Inquiry;
use App\Models\Subscriber;
use App\Models\Tour;
use Illuminate\Console\Command;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends one real email and reports exactly what happened.
 *
 * Mail failures are usually silent — the log mailer "succeeds" without
 * transmitting anything, and a queued job fails somewhere nobody is watching.
 * This makes the whole path visible in one command.
 */
class TestMail extends Command
{
    protected $signature = 'mail:test
        {email : Where to send the test}
        {--booking : Send the booking confirmation instead of the welcome email}';

    protected $description = 'Send a test email and report the transport actually used';

    public function handle(): int
    {
        $to = $this->argument('email');
        $mailer = config('mail.default');

        $this->newLine();
        $this->line('  Mailer .......... ' . $mailer);
        $this->line('  From ............ ' . config('mail.from.address') . ' (' . config('mail.from.name') . ')');

        if ($mailer === 'smtp') {
            $this->line('  Host ............ ' . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
            $this->line('  Username ........ ' . (config('mail.mailers.smtp.username') ?: '(none)'));
            $this->line('  Encryption ...... ' . (config('mail.mailers.smtp.scheme') ?: 'default'));
        }

        $this->line('  Queue ........... ' . config('queue.default'));
        $this->newLine();

        if ($mailer === 'log') {
            $this->warn('  MAIL_MAILER is "log". Nothing is transmitted — mail is written to');
            $this->warn('  storage/logs/laravel.log instead. Set real SMTP credentials in .env');
            $this->warn('  to deliver to a real inbox.');
            $this->newLine();
        }

        if (config('queue.default') !== 'sync') {
            $this->warn('  Queue is "' . config('queue.default') . '", so mail is handed to a worker.');
            $this->warn('  Without "php artisan queue:work" running, it will never send.');
            $this->newLine();
        }

        try {
            // Sent immediately, bypassing the queue, so any error surfaces here
            // rather than inside a worker.
            Mail::to($to)->sendNow($this->message($to));
        } catch (Throwable $e) {
            $this->error('  Send failed: ' . $e->getMessage());
            $this->newLine();

            return self::FAILURE;
        }

        $this->info($mailer === 'log'
            ? '  Written to storage/logs/laravel.log (not delivered).'
            : '  Sent to ' . $to . '. Check the inbox, and the spam folder.');

        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * A sample of whichever email is being checked.
     *
     * Nothing here is persisted — the mailables only need something to address
     * and to render from, so a test never leaves a stray subscriber or a fake
     * booking in the database.
     */
    private function message(string $to): Mailable
    {
        if ($this->option('booking')) {
            $inquiry = new Inquiry([
                'name' => 'Sample Guest',
                'email' => $to,
                'tour_name' => Tour::published()->ordered()->value('name') ?: 'Great Migration Safari',
                'tour_slug' => Tour::published()->ordered()->value('slug'),
                'travel_date' => now()->addMonths(2),
                'travellers' => 2,
            ]);
            $inquiry->id = 0;

            return new BookingConfirmed($inquiry);
        }

        $subscriber = new Subscriber(['email' => $to]);
        $subscriber->id = 0;

        return new SubscriberWelcome($subscriber);
    }
}
