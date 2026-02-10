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
                'meeting.create',
                'meeting.update',
                'meeting.cancel',
                'meeting.join',
                'meeting.invite',
                'meeting.view.audit',
                'org.users.manage',
                'org.settings.manage',
                'system.view.analytics',
            ],
            'org-admin' => [
                'meeting.create',
                'meeting.update',
                'meeting.cancel',
                'meeting.join',
                'meeting.invite',
                'meeting.view.audit',
                'org.users.manage',
                'org.settings.manage',
            ],
            'host' => [
                'meeting.create',
                'meeting.update',
                'meeting.cancel',
                'meeting.join',
                'meeting.invite',
            ],
            'member' => [
                'meeting.join',
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
