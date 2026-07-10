<?php

namespace App\Console\Commands;

use App\Models\Party;
use App\Notifications\RsvpReminder;
use Illuminate\Console\Command;

class ReminderSendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:send {--dry-run : Preview which template each party would receive without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send RSVP reminders to all parties where at least one member has not yet responded';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $parties = Party::with('members')
            ->whereHas('members', function ($query) {
                $query->whereNull('rsvp')->orWhere('rsvp', 'maybe');
            })
            ->get();

        if ($parties->isEmpty()) {
            $this->info('No parties require reminders.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $rows = $parties->map(fn ($party) => [
                $party->id,
                $party->name,
                RsvpReminder::resolveSituation($party),
            ])->all();

            $this->table(['Party ID', 'Party Name', 'Template'], $rows);

            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;

        foreach ($parties as $party) {
            foreach ($party->members as $member) {
                if (! $member->email) {
                    $skipped++;

                    continue;
                }

                $member->notify(new RsvpReminder($party));
                $sent++;
                $this->line("✓ Reminder sent to {$member->name} ({$member->id})");
            }
        }

        $this->info("Sent reminders to {$sent} member(s).");

        if ($skipped > 0) {
            $this->warn("Skipped {$skipped} member(s) with no email address.");
        }

        return self::SUCCESS;
    }
}
