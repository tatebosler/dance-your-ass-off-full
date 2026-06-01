<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\Invitation;
use Illuminate\Console\Command;

class InviteSendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invite:send {users?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send invitations to users. Use --all for all users, or provide comma-separated user IDs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $all = $this->option('all');
        $users = $this->argument('users');

        if ($all) {
            $usersToInvite = User::all();
        } elseif ($users) {
            $userIds = array_map('intval', explode(',', $users));
            $usersToInvite = User::whereIn('id', $userIds)->get();
        } else {
            $this->error('Please provide either --all flag or comma-separated user IDs');

            return self::FAILURE;
        }

        if ($usersToInvite->isEmpty()) {
            $this->warn('No users found.');

            return self::SUCCESS;
        }

        $skipped = [];
        $sent = [];

        foreach ($usersToInvite as $user) {
            if (! $user->email && ! $user->phone) {
                $skipped[] = $user;

                continue;
            }

            $user->notify(new Invitation);
            $sent[] = $user;
            $this->line("✓ Invitation sent to {$user->name} ({$user->id})");
        }

        $this->info('Sent invitations to '.count($sent).' user(s).');

        if ($skipped !== []) {
            $this->warn('Skipped '.count($skipped).' user(s) with no email or phone:');
            foreach ($skipped as $user) {
                $this->line("  - {$user->name} ({$user->id})");
            }
        }

        return self::SUCCESS;
    }
}
