<?php

namespace Database\Seeders;

use App\Models\Cause;
use App\Models\GivingType;
use App\Models\Program;
use Illuminate\Database\Seeder;

/**
 * Giving types and causes from the plan (1.1 Donate, 1.2 Donation module).
 *
 * Deliberately NOT seeded: which causes may receive Zakat or Khums (Finance
 * decides, under scholarly guidance) and appeal goals (the plan leaves them as
 * "[Raised Rs X of Rs Y]"). Until set, Zakat/Khums given to a specific cause is
 * flagged for review and appeals show no progress bar.
 */
class DonationSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['zakat', 'Zakat', 'Kept in a separate fund and given only to eligible (mustahiq) individuals and families.', true],
            ['khums', 'Khums (Sehm-e-Sadat / Sehm-e-Imam)', 'Kept in a separate fund and used only for permitted purposes, under the permission (ijazah) of the Marja’s representative.', true],
            ['sadaqah', 'Sadaqah', 'For anyone in need, including Sadaqah for protection and well-being.', false],
            ['khairat', 'Khairat', 'General charity for any good cause we run.', false],
            ['fitrana', 'Fitrana', 'Paid before Eid-ul-Fitr and delivered to deserving families before the Eid prayer.', false],
            ['fidya-kaffarah', 'Fidya and Kaffarah', 'For missed fasts or broken oaths; we feed deserving people on your behalf.', false],
            ['nazr-niyaz', 'Nazr and Niyaz', 'Fulfil your vow; we arrange food or help for people in need in your name.', false],
            ['qurbani-aqiqah', 'Qurbani and Aqiqah', 'The sacrifice performed for you and the meat shared with deserving families.', false],
            ['general', 'General donation or membership fee', 'Supports all our work.', false],
        ];

        foreach ($types as $i => [$slug, $name, $description, $restricted]) {
            GivingType::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $description,
                'is_restricted' => $restricted,
                'sort_order' => $i + 1,
            ]);
        }

        $program = fn (string $slug) => Program::where('slug', $slug)->value('id');

        $causes = [
            ['clean-water', 'Clean Water', $program('clean-water')],
            ['health', 'Health', $program('health')],
            ['ambulance', 'Ambulance', $program('ambulance-service'), [
                'appeal_title' => 'A Better Ambulance Service',
                'description' => 'Our three ambulances carry patients within Gilgit free of cost and to Islamabad and other districts at nominal charges. Help us add a fully equipped ambulance.',
            ]],
            ['education', 'Education', $program('education')],
            ['ramadan-package', 'Ramadan Package', $program('social-services')],
            ['winter-package', 'Winter Package', $program('social-services'), [
                'appeal_title' => 'Winter Support 2026–27',
                'description' => 'Our Winter Support Programme runs through December 2026 and January 2027. We have already given about 600 blankets; help us keep more families warm this winter.',
            ]],
            ['sahara-complex', 'Sahara Complex', null, [
                'appeal_title' => 'The Sahara Complex',
                'description' => 'We have bought a building with an approved foundation for five floors. Help us build a low-cost pathology laboratory, a pharmacy and OPD, and a guest house for patients’ families coming from far away.',
            ]],
            ['emergency', 'Emergency', null],
        ];

        foreach ($causes as $i => $row) {
            [$slug, $name, $programId] = $row;
            $appeal = $row[3] ?? null;

            Cause::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'program_id' => $programId,
                'sort_order' => $i + 1,
                'is_appeal' => (bool) $appeal,
                'appeal_title' => $appeal['appeal_title'] ?? null,
                'description' => $appeal['description'] ?? null,
            ]);
        }
    }
}
