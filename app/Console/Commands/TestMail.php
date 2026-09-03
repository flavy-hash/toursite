<?php

namespace App\Console\Commands;

use App\Mail\SubscriberWelcome;
use App\Models\Subscriber;
use Illuminate\Console\Command;
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
    protected $signature = 'mail:test {email : Where to send the test}';

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

        // Not persisted — the mailable only needs something to address and to
        // build an unsubscribe link from.
        $subscriber = new Subscriber(['email' => $to]);
        $subscriber->id = 0;

        try {
            // Sent immediately, bypassing the queue, so any error surfaces here
            // rather than inside a worker.
            Mail::to($to)->sendNow(new SubscriberWelcome($subscriber));
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
}
