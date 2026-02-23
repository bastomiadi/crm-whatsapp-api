<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run CRM seeders first to create roles
        $this->call([
            RolesAndPermissionsSeeder::class,
            CRMSeeder::class,
            AutomationSeeder::class,
            ChatbotSeeder::class,
            CRMDataSeeder::class,
        ]);

        // Create default admin user (admin role - highest access)
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
        ]);
        $admin->assignRole(Role::where('slug', 'admin')->first());

        // Create agent user for testing (agent role)
        $agent = User::factory()->create([
            'name' => 'Agent User',
            'email' => 'agent@agent.com',
        ]);
        $agent->assignRole(Role::where('slug', 'agent')->first());

        // Seed agent data for testing filters
        $this->call([
            AgentDataSeeder::class,
        ]);
    }
}
