<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the single admin user, reusing the legacy bcrypt hash from .env
     * so the existing CEO password continues to work under Laravel auth.
     */
    public function run(): void
    {
        $email = config('integrations.admin.email');
        $hash  = config('integrations.admin.password_hash');

        if (! $email || ! $hash) {
            $this->command->warn('AdminUserSeeder skipped: ADMIN_EMAIL / ADMIN_PASSWORD_HASH not set.');
            return;
        }

        DB::table('users')->updateOrInsert(
            ['email' => $email],
            [
                'name'       => 'CEO',
                'password'   => $hash,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->command->info("Admin user ensured: {$email}");
    }
}
