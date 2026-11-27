<?php

/*
 * Site structure from the SSWF Website Master Plan (0.2 Sitemap and navigation).
 * The program list moves to the `programs` table when the Programs module is
 * built; until then the menu and home page read it from here.
 */

return [

    'name' => 'Subka Sahara Welfare Foundation',
    'short_name' => 'SSWF',
    'founded' => 2006,

    /*
     * Two-factor login for every admin (plan 1.2). Keep this on for the live
     * site and staging; the automated tests switch it off and test it on purpose.
     */
    'admin_two_factor_required' => (bool) env('ADMIN_TWO_FACTOR_REQUIRED', true),

    // Districts of Gilgit-Baltistan, for project locations and filters.
    'districts' => [
        'Gilgit', 'Nagar', 'Hunza', 'Ghizer', 'Gupis-Yasin', 'Astore', 'Diamer', 'Darel',
        'Tangir', 'Skardu', 'Shigar', 'Kharmang', 'Ghanche', 'Roundu',
    ],

    'programs' => [
        'clean-water' => [
            'name' => 'Clean Water',
            'summary' => 'Nine filtration plants and emergency water supply for villages without safe drinking water.',
        ],
        'health' => [
            'name' => 'Health',
            'summary' => 'Free medical, eye and circumcision camps, blood donor records, and surgical items for patients in need.',
        ],
        'ambulance-service' => [
            'name' => 'Ambulance Service',
            'summary' => 'Free within Gilgit city; nominal charges to Islamabad and other districts.',
        ],
        'education' => [
            'name' => 'Education',
            'summary' => 'Scholarships, interest-free education loans, books and uniforms, prizes for top students and career counselling.',
        ],
        'social-services' => [
            'name' => 'Social Services',
            'summary' => 'Ramadan rations, winter packages, flood relief, and free essentials for patients’ families at the hospital.',
        ],
        'qurbani' => [
            'name' => 'Qurbani',
            'summary' => 'Your sacrifice performed on Eid-ul-Adha and the meat shared with deserving families.',
        ],
        'youth-and-community' => [
            'name' => 'Youth & Community',
            'summary' => 'A drug-free youth campaign, seminars and programs that bring communities together.',
        ],
        'religious-events' => [
            'name' => 'Service at Religious Events',
            'summary' => 'Medical camps, ambulances and water stalls at Muharram, Chehlum and Milad processions.',
        ],
    ],

    /*
     * Desktop menu, in order. Donate is rendered separately as the orange button.
     * 'children' produces a flyout (desktop) or a disclosure (mobile).
     */
    'menu' => [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'About', 'route' => 'about', 'children' => [
            ['label' => 'Our story', 'route' => 'about'],
            ['label' => 'Leadership', 'route' => 'leadership'],
            ['label' => 'Transparency', 'route' => 'transparency'],
            ['label' => 'Sahara Complex', 'route' => 'about', 'fragment' => 'sahara-complex'],
        ]],
        ['label' => 'Programs', 'route' => 'programs.index', 'children' => 'programs'],
        ['label' => 'Projects', 'route' => 'projects.index'],
        ['label' => 'Get Help', 'route' => 'get-help'],
        ['label' => 'Get Involved', 'route' => 'get-involved'],
        ['label' => 'Media', 'route' => 'news.index', 'children' => [
            ['label' => 'News', 'route' => 'news.index'],
            ['label' => 'Gallery', 'route' => 'gallery'],
        ]],
        ['label' => 'Contact', 'route' => 'contact'],
    ],

];
