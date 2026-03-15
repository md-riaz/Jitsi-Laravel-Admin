<?php

namespace Database\Seeders;

use HasinHayder\Tyro\Models\Privilege;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Seeder;

class TyroRolesAndPrivilegesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['super-admin', 'org-admin', 'host', 'member'];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'slug' => $role,
            ], [
                'name' => str($role)->title()->toString(),
            ]);
        }

        $privilegesByRole = [
            'super-admin' => [
                // Meeting privileges
                'meeting.create',
                'meeting.update',
                'meeting.cancel',
                'meeting.join',
                'meeting.invite',
                'meeting.view.audit',
                'meeting.view.history',
                // Organization management
                'org.create',
                'org.update',
                'org.delete',
                'org.suspend',
                'org.users.manage',
                'org.settings.manage',
                'org.branding.manage',
                // Subscription & billing
                'subscription.manage',
                'subscription.assign',
                'subscription.view',
                // System-level
                'system.view.analytics',
                'system.view.logs',
                'system.security.manage',
            ],
            'org-admin' => [
                // Meeting privileges
                'meeting.create',
                'meeting.update',
                'meeting.cancel',
                'meeting.join',
                'meeting.invite',
                'meeting.view.audit',
                'meeting.view.history',
                // Organization management (own org only)
                'org.users.manage',
                'org.settings.manage',
                'org.branding.manage',
                // Reporting
                'org.view.analytics',
                'org.recordings.manage',
            ],
            'host' => [
                'meeting.create',
                'meeting.update',
                'meeting.cancel',
                'meeting.join',
                'meeting.invite',
                'meeting.moderate',
                'meeting.view.history',
                'meeting.attendance.view',
            ],
            'member' => [
                'meeting.join',
                'meeting.view.history',
            ],
        ];

        foreach ($privilegesByRole as $roleSlug => $privileges) {
            $role = Role::where('slug', $roleSlug)->first();

            foreach ($privileges as $privilegeSlug) {
                $privilege = Privilege::firstOrCreate([
                    'slug' => $privilegeSlug,
                ], [
                    'name' => str($privilegeSlug)->replace('.', ' ')->title()->toString(),
                ]);

                $role?->privileges()->syncWithoutDetaching([$privilege->id]);
            }
        }
    }
}
