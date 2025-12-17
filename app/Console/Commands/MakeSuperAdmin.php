<?php

namespace App\Console\Commands;

use App\Actions\Auth\EnsureRolesAction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laraflow:make-super-admin
                            {email : The email of the super admin user}
                            {--name= : Optional name (used when creating the user)}
                            {--password= : Optional password (used when creating the user)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or promote a user to super_admin (global role).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = strtolower(trim($this->argument('email')));
        $name = $this->option('name') ?? 'Super Admin';
        $password = $this->option('password');

        // Ensure global roles exist (team_id = 0)
        app(EnsureRolesAction::class)->ensureGlobal();

        // Ensure Spatie is in global context for assignment
        app(PermissionRegistrar::class)
            ->setPermissionsTeamId(config('laraflow.platform_team_id', 0));

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            if (! $password) {
                $password = $this->secret('Password (will be hidden)');
                if (! $password) {
                    $this->error('Password is required for new user.');

                    return self::FAILURE;
                }
            }

            $user = User::create([
                'tenant_id' => null,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->info("Created user: {$user->email}");
        } else {
            // Make sure super admin is not tied to a tenant
            if ($user->tenant_id !== null) {
                $user->update(['tenant_id' => null]);
            }
            $this->info("Found user: {$user->email}");
        }

        // Assign global role
        if (! $user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
            $this->info('Assigned role: super_admin');
        } else {
            $this->info('User already has role: super_admin');
        }

        return self::SUCCESS;
    }
}
