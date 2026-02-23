<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // Contacts
            ['name' => 'View Contacts', 'slug' => 'view_contacts', 'module' => 'contacts'],
            ['name' => 'Create Contacts', 'slug' => 'create_contacts', 'module' => 'contacts'],
            ['name' => 'Edit Contacts', 'slug' => 'edit_contacts', 'module' => 'contacts'],
            ['name' => 'Delete Contacts', 'slug' => 'delete_contacts', 'module' => 'contacts'],
            ['name' => 'Import Contacts', 'slug' => 'import_contacts', 'module' => 'contacts'],
            
            // Products
            ['name' => 'View Products', 'slug' => 'view_products', 'module' => 'products'],
            ['name' => 'Create Products', 'slug' => 'create_products', 'module' => 'products'],
            ['name' => 'Edit Products', 'slug' => 'edit_products', 'module' => 'products'],
            ['name' => 'Delete Products', 'slug' => 'delete_products', 'module' => 'products'],
            
            // Orders
            ['name' => 'View Orders', 'slug' => 'view_orders', 'module' => 'orders'],
            ['name' => 'Create Orders', 'slug' => 'create_orders', 'module' => 'orders'],
            ['name' => 'Edit Orders', 'slug' => 'edit_orders', 'module' => 'orders'],
            ['name' => 'Delete Orders', 'slug' => 'delete_orders', 'module' => 'orders'],
            
            // Deals
            ['name' => 'View Deals', 'slug' => 'view_deals', 'module' => 'deals'],
            ['name' => 'Create Deals', 'slug' => 'create_deals', 'module' => 'deals'],
            ['name' => 'Edit Deals', 'slug' => 'edit_deals', 'module' => 'deals'],
            ['name' => 'Delete Deals', 'slug' => 'delete_deals', 'module' => 'deals'],
            
            // Campaigns
            ['name' => 'View Campaigns', 'slug' => 'view_campaigns', 'module' => 'campaigns'],
            ['name' => 'Create Campaigns', 'slug' => 'create_campaigns', 'module' => 'campaigns'],
            ['name' => 'Edit Campaigns', 'slug' => 'edit_campaigns', 'module' => 'campaigns'],
            ['name' => 'Delete Campaigns', 'slug' => 'delete_campaigns', 'module' => 'campaigns'],
            ['name' => 'Send Campaigns', 'slug' => 'send_campaigns', 'module' => 'campaigns'],
            
            // Chat
            ['name' => 'View Chat', 'slug' => 'view_chat', 'module' => 'chat'],
            ['name' => 'Send Messages', 'slug' => 'send_messages', 'module' => 'chat'],
            
            // Tickets
            ['name' => 'View Tickets', 'slug' => 'view_tickets', 'module' => 'tickets'],
            ['name' => 'Create Tickets', 'slug' => 'create_tickets', 'module' => 'tickets'],
            ['name' => 'Edit Tickets', 'slug' => 'edit_tickets', 'module' => 'tickets'],
            ['name' => 'Delete Tickets', 'slug' => 'delete_tickets', 'module' => 'tickets'],
            ['name' => 'Assign Tickets', 'slug' => 'assign_tickets', 'module' => 'tickets'],
            
            // Automations
            ['name' => 'View Automations', 'slug' => 'view_automations', 'module' => 'automations'],
            ['name' => 'Create Automations', 'slug' => 'create_automations', 'module' => 'automations'],
            ['name' => 'Edit Automations', 'slug' => 'edit_automations', 'module' => 'automations'],
            ['name' => 'Delete Automations', 'slug' => 'delete_automations', 'module' => 'automations'],
            
            // Reports
            ['name' => 'View Reports', 'slug' => 'view_reports', 'module' => 'reports'],
            ['name' => 'Export Reports', 'slug' => 'export_reports', 'module' => 'reports'],
            
            // Users & Settings
            ['name' => 'View Users', 'slug' => 'view_users', 'module' => 'users'],
            ['name' => 'Create Users', 'slug' => 'create_users', 'module' => 'users'],
            ['name' => 'Edit Users', 'slug' => 'edit_users', 'module' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'delete_users', 'module' => 'users'],
            ['name' => 'Manage Roles', 'slug' => 'manage_roles', 'module' => 'users'],
            ['name' => 'View Settings', 'slug' => 'view_settings', 'module' => 'settings'],
            ['name' => 'Edit Settings', 'slug' => 'edit_settings', 'module' => 'settings'],
            
            // Surveys
            ['name' => 'View Surveys', 'slug' => 'view_surveys', 'module' => 'surveys'],
            ['name' => 'Create Surveys', 'slug' => 'create_surveys', 'module' => 'surveys'],
            ['name' => 'Edit Surveys', 'slug' => 'edit_surveys', 'module' => 'surveys'],
            ['name' => 'Delete Surveys', 'slug' => 'delete_surveys', 'module' => 'surveys'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // Create Roles
        // Admin - has access to all data (highest access level)
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full access to all data across all users',
                'is_default' => false,
            ]
        );

        $managerRole = Role::firstOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Can manage team and most features',
                'is_default' => false,
            ]
        );

        $agentRole = Role::firstOrCreate(
            ['slug' => 'agent'],
            [
                'name' => 'Agent',
                'description' => 'Can handle chats and tickets',
                'is_default' => true,
            ]
        );

        // Assign all permissions to admin
        $adminRole->permissions()->sync(Permission::all()->pluck('id'));

        // Assign admin role to first user (if exists)
        $firstUser = \App\Models\User::first();
        if ($firstUser) {
            $firstUser->assignRole($adminRole);
        }

        // Assign specific permissions to manager
        $managerPermissions = Permission::whereIn('slug', [
            'view_contacts', 'create_contacts', 'edit_contacts', 'import_contacts',
            'view_products', 'create_products', 'edit_products',
            'view_orders', 'create_orders', 'edit_orders',
            'view_deals', 'create_deals', 'edit_deals',
            'view_campaigns', 'create_campaigns', 'edit_campaigns', 'send_campaigns',
            'view_chat', 'send_messages',
            'view_tickets', 'create_tickets', 'edit_tickets', 'assign_tickets',
            'view_automations', 'create_automations', 'edit_automations',
            'view_reports', 'export_reports',
            'view_users', 'create_users', 'edit_users',
            'view_settings',
        ])->pluck('id');
        $managerRole->permissions()->sync($managerPermissions);

        // Assign specific permissions to agent
        $agentPermissions = Permission::whereIn('slug', [
            'view_contacts',
            'view_products',
            'view_orders',
            'view_deals',
            'view_campaigns',
            'view_chat', 'send_messages',
            'view_tickets', 'create_tickets', 'edit_tickets',
            'view_reports',
        ])->pluck('id');
        $agentRole->permissions()->sync($agentPermissions);
    }
}
