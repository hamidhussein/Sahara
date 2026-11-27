<?php

namespace Database\Seeders;

use App\Enums\PostCategory;
use App\Enums\ProjectStatus;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use Illuminate\Database\Seeder;

/**
 * Starting content taken word for word from the SSWF Website Master Plan
 * (sections 1.1 Programs, Projects, News and Partners). Facts the plan marks
 * as unconfirmed are left empty. Uses firstOrCreate, so running it again never
 * overwrites anything editors have changed in the admin.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $programs = $this->programs();
        $this->projects($programs);
        $this->partners();
        $this->posts();
    }

    /** @return array<string, Program> */
    private function programs(): array
    {
        $details = [
            'clean-water' => [
                'tagline' => 'Safe drinking water for every home.',
                'body' => '<p>In District Nagar, unsafe water used to cause diarrhoea, stomach problems, gastroenteritis and skin disease, and children were hit hardest. We install water filtration plants built to WHO standards in villages where the water was unsafe, test water before installation, and keep the plants running. When floods cut off supply, we deliver water by tanker to every door.</p>'
                    .'<h2>Highlights</h2><ul>'
                    .'<li>13 working plants in villages including Phekar, Dadimal, Miachar, Bar, Budalas, Chaprote and Minapin, many funded by Mr Hadi Shahzad.</li>'
                    .'<li>Among people using filtered water, these illnesses have dropped by about 80%.</li>'
                    .'<li>"Pani Har Ghar Tak" 2025: 28 days of door-to-door water for Danyor and Sultanabad, with 50 volunteers.</li>'
                    .'<li>Latest: four new plants completed in Bar, Budalas, Chaprote and Minapin.</li></ul>'
                    .'<p><strong>Coming next:</strong> four more plants, with installation starting in April 2027, in Jutal, Hakucher, Dadimal Paeen and one more village to be announced.</p>'
                    .'<p><strong>Your gift:</strong> help fund the next plant, or donate a water tanker in the name of a loved one.</p>',
            ],
            'health' => [
                'tagline' => 'Good care should not depend on what you can pay.',
                'body' => '<h2>Every year</h2><ul>'
                    .'<li>Free general medical camps in 3 villages of Nagar (with Mr M. Akbar’s family and friends, Faisalabad).</li>'
                    .'<li>Free circumcision camps in 3 villages of Nagar (with the Health Department, Government of Gilgit-Baltistan).</li>'
                    .'<li>Free medical camps during Muharram and in every disaster-affected area.</li>'
                    .'<li>Blood donor records kept with the Regional Blood Center Gilgit.</li>'
                    .'<li>Surgical items for deserving patients.</li></ul>'
                    .'<p><strong>Highlight:</strong> Free Eye Camp 2026: 3,000 patients examined and more than 300 free operations, with follow-up and medicines (with Mr Rizwan Ahmad’s family and friends, Lahore, and DHQ Hospital Nagar).</p>'
                    .'<p><strong>Next camp:</strong> a free medical camp in Phekar, Nagar, on 4 December 2026.</p>'
                    .'<p><strong>Coming next:</strong> a low-cost pathology laboratory and a blood bank at the Sahara Complex.</p>',
            ],
            'ambulance-service' => [
                'tagline' => 'No patient left without a ride to care.',
                'body' => '<ul>'
                    .'<li><strong>Within Gilgit city:</strong> free of cost.</li>'
                    .'<li><strong>Gilgit to Islamabad:</strong> medically equipped Hi-Roof ambulance at nominal charges.</li>'
                    .'<li><strong>Gilgit to other districts:</strong> medically equipped ambulance for patients and for carrying the deceased home, at nominal charges.</li></ul>'
                    .'<p><strong>Your gift:</strong> help us add a fully equipped ambulance with a ventilator.</p>',
            ],
            'education' => [
                'tagline' => 'No talented student held back by money.',
                'body' => '<ul>'
                    .'<li>Interest-free education loans (Qarz-e-Hasna) of Rs 50,000–100,000 for one year for 2–3 students admitted to medical or engineering studies.</li>'
                    .'<li>Scholarships for 30 deserving and orphan students at eight educational institutions in Nagar and Gilgit.</li>'
                    .'<li>School fees for orphans and deserving students in Gilgit and Nagar.</li>'
                    .'<li>New books, uniforms, shoes and bags at the start of each session.</li>'
                    .'<li>Shields and dictionaries for top position holders in Union Council Phekar.</li>'
                    .'<li>Career counselling with Dr Muhammad Anwar, Prof. Manzoor Hussain and Dr Sajid Ali.</li></ul>'
                    .'<p><strong>Coming next:</strong> IT and AI courses, scholarships with a focus on girls, and student hostels in Gilgit.</p>',
            ],
            'social-services' => [
                'tagline' => 'Help in the hardest seasons of life.',
                'body' => '<ul>'
                    .'<li>Ramadan rations worth at least Rs 10,000 per family in Nagar, Hunza, Gilgit, Astore and Ghizer; 1,067 families in 2026 (supported by Haji Liaqat Ali, Agha Sheikh Mushtaq Hussain Ansari, FY Tiles and other donors).</li>'
                    .'<li>About 1,200 ration packages and 600 blankets given to deserving families in Nagar, Hunza, Gilgit, Astore and Ghizer.</li>'
                    .'<li>Winter package of a warm blanket and bedding set. The next Winter Support Programme runs in December 2026 and January 2027.</li>'
                    .'<li>At DHQ Hospital Gilgit, blankets, hot pots, thermoses, plates and cups lent free to patients’ families against an original CNIC.</li>'
                    .'<li>Tents and essentials after floods and disasters, with the Pakistan Red Crescent Society Gilgit.</li>'
                    .'<li>Wheelchairs, with Nagar Disability Forum and Vision Welfare Foundation.</li></ul>'
                    .'<p><strong>Your gift:</strong> Rs 10,000 gives one family a Ramadan ration package.</p>',
            ],
            'qurbani' => [
                'tagline' => 'The blessing of Eid-ul-Adha, shared with families who cannot afford meat.',
                'body' => '<p>Every Eid-ul-Adha we perform Qurbani on behalf of our donors at several locations in District Nagar, following Islamic guidelines. Volunteers verify deserving families in advance, and the meat is delivered to their homes during Eid.</p>'
                    .'<p><strong>2026:</strong> three locations in Nagar, meat for about 350 people.</p>'
                    .'<h2>How it works</h2><ol>'
                    .'<li>Choose your animal or share and the name(s).</li>'
                    .'<li>Pay online, by bank transfer or at our office.</li>'
                    .'<li>We perform the Qurbani and distribute the meat.</li>'
                    .'<li>You receive confirmation by WhatsApp or email.</li></ol>',
            ],
            'youth-and-community' => [
                'tagline' => 'A healthy, confident next generation and united communities.',
                'body' => '<p>The Drug-Free Phekar campaign against cigarettes, naswar and other harmful substances; seminars, workshops and teacher training; first-aid and volunteer training; events that build harmony between communities.</p>'
                    .'<p><strong>Highlight:</strong> with local elders and Falahi Tanzeem, we brought Phekar Valley together for a healthy, drug-free community. Shopkeepers agreed to stop selling cigarettes and other harmful substances in the village.</p>'
                    .'<p><strong>Coming next:</strong> support for jobless youth, an entrepreneurship programme, interfaith harmony sessions and climate change awareness.</p>',
            ],
            'religious-events' => [
                'tagline' => 'Serving every gathering, every year.',
                'body' => '<p>For over a decade our medical teams, ambulances and water stalls (sabeel) have served mourners and participants at Muharram and Ashura processions, Chehlum walks, the 12 Rabi-ul-Awwal procession and Tableeghi gatherings in Gilgit and Nagar.</p>',
            ],
        ];

        $models = [];
        $order = 1;
        foreach (config('sswf.programs') as $slug => $program) {
            $models[$slug] = Program::firstOrCreate(['slug' => $slug], [
                'name' => $program['name'],
                'summary' => $program['summary'],
                'tagline' => $details[$slug]['tagline'],
                'body' => $details[$slug]['body'],
                'sort_order' => $order++,
            ]);
        }

        return $models;
    }

    /** @param array<string, Program> $programs */
    private function projects(array $programs): void
    {
        $projects = [
            [
                'slug' => 'miachar-water-plant',
                'program' => 'clean-water',
                'name' => 'Miachar Water Filtration Plant',
                'village' => 'Miachar',
                'district' => 'Nagar',
                'status' => ProjectStatus::Completed,
                'donor' => 'Gift of Mr Hadi Shahzad',
                'summary' => 'A water filtration plant built to WHO standards that gives Miachar clean and safe drinking water every day.',
                'story' => '<p>For years, families in Miachar relied on untreated water. Before installation, the chairman collected water samples for testing to design the right plant. With the generous support of Mr Hadi Shahzad, an overseas Pakistani, our volunteers built the Miachar plant, which now gives the community clean and safe drinking water every day.</p>',
            ],
            [
                'slug' => 'free-eye-camp-2026',
                'program' => 'health',
                'name' => 'Free Eye Camp 2026',
                'district' => 'Nagar',
                'status' => ProjectStatus::Completed,
                'donor' => 'With Mr Rizwan Ahmad’s family and friends, Lahore, and DHQ Hospital Nagar',
                'people_served' => 3000,
                'people_served_label' => 'Patients examined',
                'summary' => '3,000 patients examined and more than 300 free operations, with follow-up and medicines.',
                'story' => '<p>In 2026, our free eye camp examined 3,000 patients and carried out more than 300 free operations, with follow-up care and medicines. It was made possible with Mr Rizwan Ahmad’s family and friends from Lahore and DHQ Hospital Nagar.</p>',
            ],
            [
                'slug' => 'pani-har-ghar-tak-danyor',
                'program' => 'clean-water',
                'name' => 'Pani Har Ghar Tak: Danyor water emergency',
                'village' => 'Danyor and Sultanabad',
                'district' => 'Gilgit',
                'status' => ProjectStatus::Completed,
                'summary' => '28 days of door-to-door water for Danyor and Sultanabad, delivered by 50 volunteers.',
                'story' => '<p>In 2025, when water supply to Danyor and Sultanabad was cut off, our "Pani Har Ghar Tak" (water to every home) campaign delivered water door to door for 28 days, with 50 volunteers.</p>',
            ],
            [
                'slug' => 'ramadan-rations-2026',
                'program' => 'social-services',
                'name' => 'Ramadan Rations 2026',
                'status' => ProjectStatus::Completed,
                'donor' => 'Supported by Haji Liaqat Ali, Agha Sheikh Mushtaq Hussain Ansari, FY Tiles and other donors',
                'people_served' => 1067,
                'people_served_label' => 'Families served',
                'summary' => 'Ramadan ration packages for 1,067 families in Nagar, Hunza, Gilgit, Astore and Ghizer.',
                'story' => '<p>In Ramadan 2026, we gave ration packages worth at least Rs 10,000 each to 1,067 families in Nagar, Hunza, Gilgit, Astore and Ghizer.</p>',
            ],

            // Project update, November 2026: completed.
            [
                'slug' => 'water-plants-bar-budalas-chaprote-minapin',
                'program' => 'clean-water',
                'name' => 'Four Water Filtration Plants: Bar, Budalas, Chaprote and Minapin',
                'village' => 'Bar, Budalas, Chaprote and Minapin',
                'district' => 'Nagar',
                'status' => ProjectStatus::Completed,
                'people_served' => 4,
                'people_served_label' => 'Plants installed',
                'summary' => 'Four new water filtration plants giving the villages of Bar, Budalas, Chaprote and Minapin safe, clean drinking water.',
                'story' => '<p>We have completed the installation of four water filtration plants in Bar, Budalas, Chaprote and Minapin. These plants give local communities safe and clean drinking water, and protect families, especially children, from the illnesses that unsafe water brings.</p>',
            ],
            [
                'slug' => 'ration-distribution',
                'program' => 'social-services',
                'name' => 'Ration Distribution',
                'village' => 'Nagar, Hunza, Gilgit, Astore and Ghizer',
                'status' => ProjectStatus::Completed,
                'people_served' => 1200,
                'people_served_label' => 'Ration packages',
                'summary' => 'About 1,200 ration packages for deserving and vulnerable families in Nagar, Hunza, Gilgit, Astore and Ghizer.',
                'story' => '<p>We distributed about 1,200 ration packages to deserving and vulnerable families across the districts of Nagar, Hunza, Gilgit, Astore and Ghizer, so that no family in these communities goes without food.</p>',
            ],
            [
                'slug' => 'blanket-distribution',
                'program' => 'social-services',
                'name' => 'Winter Blanket Distribution',
                'village' => 'Nagar, Hunza, Gilgit, Astore and Ghizer',
                'status' => ProjectStatus::Completed,
                'people_served' => 600,
                'people_served_label' => 'Blankets given',
                'summary' => 'About 600 blankets for deserving families facing the harsh Gilgit-Baltistan winter.',
                'story' => '<p>We distributed about 600 blankets among deserving families in Nagar, Hunza, Gilgit, Astore and Ghizer to help them through the harsh winter.</p>',
            ],

            // Ongoing.
            [
                'slug' => 'free-medical-camp-phekar-2026',
                'program' => 'health',
                'name' => 'Free Medical Camp, Phekar',
                'village' => 'Phekar',
                'district' => 'Nagar',
                'status' => ProjectStatus::Ongoing,
                'started_on' => '2026-12-04',
                'summary' => 'A free medical camp in Phekar on 4 December 2026, with medical care and health services for local residents.',
                'story' => '<p>On 4 December 2026 we are holding a free medical camp in Phekar, Nagar, to give local residents medical care and health services at no cost.</p>',
                'featured' => false,
            ],
            [
                'slug' => 'educational-scholarships',
                'program' => 'education',
                'name' => 'Educational Scholarships',
                'status' => ProjectStatus::Ongoing,
                'people_served' => 30,
                'people_served_label' => 'Students supported',
                'summary' => 'Scholarships for 30 deserving and orphan students at eight educational institutions in Nagar and Gilgit.',
                'story' => '<p>We provide scholarships to 30 deserving and orphan students at eight educational institutions across the districts of Nagar and Gilgit.</p>'
                    .'<p>Through this programme we support students from poor families, so that money never becomes a barrier to their education.</p>',
                'featured' => false,
            ],

            [
                'slug' => 'drug-free-phekar',
                'program' => 'youth-and-community',
                'name' => 'A Healthy, Drug-Free Phekar',
                'village' => 'Phekar Valley',
                'district' => 'Nagar',
                'status' => ProjectStatus::Ongoing,
                'donor' => 'With local elders and Falahi Tanzeem, Phekar',
                'summary' => 'A community campaign with local elders, youth and Falahi Tanzeem that ended the sale of cigarettes and harmful substances in Phekar.',
                'story' => '<p>We believe lasting community development begins with people taking part and sharing responsibility.</p>'
                    .'<p>With local elders and Falahi Tanzeem, a welfare organization in Phekar, we launched an awareness and mobilization campaign in Phekar Valley. Local youth and Sahara volunteers led the way towards a healthier, safer and more responsible community.</p>'
                    .'<h2>What the campaign did</h2><ul>'
                    .'<li><strong>Brought the community together:</strong> elders, youth, community representatives and Sahara volunteers working for one cause.</li>'
                    .'<li><strong>Stopped the sale of harmful substances:</strong> local shopkeepers agreed to stop selling cigarettes, intoxicants and other harmful products in the village.</li>'
                    .'<li><strong>Held awareness sessions:</strong> residents learned about the harm caused by smoking, substance abuse and other unhealthy habits.</li>'
                    .'<li><strong>Put young people first:</strong> local youth were encouraged to lead positive change in their own community.</li></ul>'
                    .'<p>The campaign shows what local communities, grassroots organizations, young people and volunteers can achieve together.</p>',
                'featured' => false,
            ],

            // Upcoming.
            [
                'slug' => 'winter-support-2026-27',
                'program' => 'social-services',
                'name' => 'Winter Support Programme 2026–27',
                'status' => ProjectStatus::Planned,
                'started_on' => '2026-12-01',
                'completed_on' => '2027-01-31',
                'summary' => 'Support for vulnerable families through the severe winter of December 2026 and January 2027.',
                'story' => '<p>In December 2026 and January 2027 we will help vulnerable families face the severe Gilgit-Baltistan winter. Your gift can keep a family warm.</p>',
                'featured' => false,
            ],
            [
                'slug' => 'water-plants-2027',
                'program' => 'clean-water',
                'name' => 'Four More Water Filtration Plants',
                'village' => 'Jutal, Hakucher, Dadimal Paeen and one more village',
                'status' => ProjectStatus::Planned,
                'started_on' => '2027-04-01',
                'people_served' => 4,
                'people_served_label' => 'Plants planned',
                'summary' => 'Four more water filtration plants, with installation starting in April 2027 in Jutal, Hakucher, Dadimal Paeen and one more village.',
                'story' => '<p>Four more water filtration plants are on the way. Installation is planned to begin in April 2027 in Jutal, Hakucher and Dadimal Paeen, and in one more village that we will announce soon.</p>',
                'featured' => false,
            ],
        ];

        $images = [
            'miachar-water-plant' => database_path('seeders/images/miachar-water-plant.jpg'),
            'free-eye-camp-2026' => database_path('seeders/images/free-eye-camp.jpg'),
            'pani-har-ghar-tak-danyor' => database_path('seeders/images/danyor-water.jpg'),
            'ramadan-rations-2026' => database_path('seeders/images/ramadan-rations.jpg'),
        ];

        foreach ($projects as $data) {
            $program = $programs[$data['program']];
            $featured = $data['featured'] ?? true;
            unset($data['program'], $data['featured']);

            $project = Project::firstOrCreate(['slug' => $data['slug']], $data + [
                'program_id' => $program->id,
                'is_featured' => $featured,
            ]);

            $image = $images[$data['slug']] ?? null;
            if ($image && is_file($image) && ! $project->hasMedia('cover')) {
                $project->addMedia($image)->preservingOriginal()->toMediaCollection('cover');
            }
        }
    }

    private function partners(): void
    {
        $names = [
            'Health Department, Government of Gilgit-Baltistan',
            'DHQ Hospital Nagar',
            'Regional Blood Center Gilgit',
            'Pakistan Red Crescent Society Gilgit',
            'Rah-e-Khuda Foundation',
            'Vision Welfare Foundation',
            'Phekar Youth Organization',
            'Hamdardi Welfare',
            'Falahi Tanzeem, Phekar',
        ];

        foreach ($names as $i => $name) {
            Partner::firstOrCreate(['name' => $name], ['sort_order' => $i + 1]);
        }
    }

    private function posts(): void
    {
        // The plan gives the month (November 2026) but not the day, so this
        // starts as a draft: set "Publish on" in the admin to publish it.
        $post = Post::firstOrCreate(['slug' => 'phekar-youth-smoking-naswar-seminar'], [
            'title' => 'Youth in Phekar Learn the Dangers of Smoking and Naswar',
            'category' => PostCategory::YouthCommunity,
            'body' => '<p>Subka Sahara Welfare Foundation held a special awareness seminar in Phekar, Nagar, to help young people understand the harm caused by cigarettes, naswar and other harmful substances.</p>'
                .'<p>Psychologist Dr Muhammad Ali Khan and SSWF’s chairman spoke to the youth about health, addiction and making strong choices. The seminar is part of the Drug-Free Phekar campaign, run with local elders and youth organizations.</p>',
            'published_at' => null,
        ]);

        $image = database_path('seeders/images/youth-seminar.jpg');
        if (is_file($image) && ! $post->hasMedia('cover')) {
            $post->addMedia($image)->preservingOriginal()->toMediaCollection('cover');
        }
    }
}
