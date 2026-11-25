<?php

namespace Database\Seeders;

use App\Enums\TeamGroup;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

/**
 * Leadership from plan 1.1. Patrons and the Executive Body are listed in the
 * plan without brackets, so they are published. The advisers paragraph says
 * "[Confirm names.]", so advisers start hidden until an editor confirms them.
 */
class LeadershipSeeder extends Seeder
{
    public function run(): void
    {
        $people = [
            ['Agha Sheikh Mushtaq Hussain Ansari', 'Patron', TeamGroup::Patron, true],
            ['Haji Liaqat Ali', 'Patron', TeamGroup::Patron, true],
            ['Arif Hussain', 'Chairman', TeamGroup::Executive, true],
            ['Alamdar Kiyani', 'Vice Chairman', TeamGroup::Executive, true],
            ['Jaffar Hussain', 'General Secretary', TeamGroup::Executive, true],
            ['Anber Hussain', 'Secretary Finance', TeamGroup::Executive, true],
            ['Kalb Ali', 'Secretary NGO (former Chairman)', TeamGroup::Executive, true],
            ['Syed Riaz Kazmi', 'Advisory Council', TeamGroup::Adviser, false],
            ['Dr Muhammad Anwar', 'Career counselling', TeamGroup::Adviser, false],
            ['Prof. Manzoor Hussain', 'Career counselling', TeamGroup::Adviser, false],
            ['Dr Sajid Ali', 'Career counselling', TeamGroup::Adviser, false],
        ];

        foreach ($people as $i => [$name, $role, $group, $published]) {
            TeamMember::firstOrCreate(['name' => $name, 'group' => $group], [
                'role' => $role,
                'sort_order' => $i + 1,
                'is_published' => $published,
            ]);
        }
    }
}
