<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\ReviewerAccountSeeder;
use Illuminate\Console\Command;

class ResetReviewerPassword extends Command
{
    protected $signature = 'reviewer:reset-password {--password= : New password (prompted if omitted)}';

    protected $description = 'Reset the reviewer account password';

    public function handle(): int
    {
        $reviewer = User::where('email', ReviewerAccountSeeder::EMAIL)->first();

        if (! $reviewer) {
            $this->error('Reviewer account not found. Run: php artisan db:seed --class=ReviewerAccountSeeder');

            return self::FAILURE;
        }

        $password = $this->option('password');

        if (! $password) {
            $password = $this->secret('Enter new password for reviewer account');

            if (! $password) {
                $this->error('Password cannot be empty.');

                return self::FAILURE;
            }

            $confirm = $this->secret('Confirm new password');

            if ($password !== $confirm) {
                $this->error('Passwords do not match.');

                return self::FAILURE;
            }
        }

        if (strlen($password) < 12) {
            $this->error('Password must be at least 12 characters for security.');

            return self::FAILURE;
        }

        $reviewer->update(['password' => bcrypt($password)]);

        $this->info("Reviewer account password updated successfully ({$reviewer->email}).");

        return self::SUCCESS;
    }
}
