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
    protected $signature = 'reminder:send
        {--party= : Comma-separated party IDs to target}
        {--user= : Comma-separated user IDs to target}
        {--dry-run : Preview which template each party would receive without sending}';

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
        $partyOption = $this->option('party');
        $userOption = $this->option('user');
        $targetedUserIds = null;

        if ($partyOption && $userOption) {
            $this->error('The --party and --user options cannot be used together.');

            return self::FAILURE;
        }

        if ($partyOption) {
            $partyIds = array_map('intval', explode(',', $partyOption));
            $allParties = Party::with('members')->whereIn('id', $partyIds)->get();

            $parties = $allParties->filter(
                fn ($party) => $party->members->contains(fn ($m) => is_null($m->rsvp) || $m->rsvp === 'maybe')
            );

            foreach ($allParties->diff($parties) as $party) {
                $this->warn("Skipping \"{$party->name}\" ({$party->id}): all members have already responded.");
            }
        } elseif ($userOption) {
            $targetedUserIds = array_map('intval', explode(',', $userOption));

            $parties = Party::with('members')
                ->whereHas('members', fn ($q) => $q->whereIn('id', $targetedUserIds))
                ->whereHas('members', fn ($q) => $q->whereNull('rsvp')->orWhere('rsvp', 'maybe'))
                ->get();

            $foundUserIds = $parties->flatMap(fn ($p) => $p->members->pluck('id'))->all();
            foreach (array_diff($targetedUserIds, $foundUserIds) as $userId) {
                $this->warn("Skipping user {$userId}: not found or their party does not require a reminder.");
            }
        } else {
            $parties = Party::with('members')
                ->whereHas('members', fn ($q) => $q->whereNull('rsvp')->orWhere('rsvp', 'maybe'))
                ->get();
        }

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
            $membersToNotify = $targetedUserIds !== null
                ? $party->members->whereIn('id', $targetedUserIds)
                : $party->members;

            foreach ($membersToNotify as $member) {
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
