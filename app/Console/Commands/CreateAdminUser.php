<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Bootstrap a super-admin user from the CLI.
 *
 * There's no UI for creating the first admin — chicken-and-egg. Use this from
 * a dev shell or a production deploy hook. Promotes an existing user if the
 * email already exists, or creates a fresh one if --password is provided.
 *
 *   php artisan app:create-admin lee@7am.ca
 *   php artisan app:create-admin lee@7am.ca --password=secret --name="Lee"
 */
class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
        {email : The user email to promote or create}
        {--password= : Password (only used if creating a new user)}
        {--name= : Display name (only used if creating a new user)}';

    protected $description = 'Promote a user to super-admin, or create one if they do not exist';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first('email'));

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->forceFill(['is_admin' => true])->save();
            $this->info("Promoted existing user {$email} to super-admin.");

            return self::SUCCESS;
        }

        $password = (string) ($this->option('password') ?? '');
        $name = (string) ($this->option('name') ?? '');

        if ($password === '' || $name === '') {
            $this->error("User {$email} does not exist. Pass --password and --name to create them, or sign up via the app first.");

            return self::FAILURE;
        }

        // forceFill — email_verified_at is intentionally not in $fillable
        // (it should never be mass-assignable from user input), but we want
        // to mark CLI-created admins as verified.
        $user = new User;
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'is_admin' => true,
        ])->save();

        $this->info("Created super-admin {$user->email} (id={$user->id}).");

        return self::SUCCESS;
    }
}
