<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Settings\SiteSettings;
use Illuminate\Database\Seeder;

/**
 * The plan's drafts of the Privacy, Refund and Terms pages (plan 1.1), which
 * still need a lawyer's review. Bracketed details are handled like this:
 * [email] and [number] come from Settings; unconfirmed time limits ([7 days],
 * [14 working days]) are worded without a number until they are agreed.
 * Editors replace the text in Admin → Content → Pages after legal review.
 */
class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        $site = app(SiteSettings::class);
        $email = e($site->email);
        $phone = e($site->whatsapp_number ?: $site->office_phone);
        $contactBy = $site->whatsapp_number ? "WhatsApp {$phone}" : "phone {$phone}";

        $pages = [
            'privacy' => [
                'title' => 'Privacy Policy',
                'lede' => 'How we collect, use and protect your details.',
                'body' => '<h2>What we collect</h2><p>Your name, phone, email and donation details when you donate; details you share in help, volunteer and blood donor forms.</p>'
                    .'<h2>Why</h2><p>To process donations and send receipts, respond to requests, and contact volunteers and blood donors when needed.</p>'
                    .'<h2>Who sees it</h2><p>Only authorised SSWF staff. We never sell or share your details, except with payment providers to process your payment, or where the law requires.</p>'
                    .'<h2>Blood donors and help requests</h2><p>Your phone number is never shown publicly.</p>'
                    ."<h2>Your choices</h2><p>Ask us to update or delete your details at any time at <a href=\"mailto:{$email}\">{$email}</a>.</p>"
                    .'<h2>Cookies</h2><p>Basic cookies keep the site working and help us understand visits.</p>',
            ],
            'refund-policy' => [
                'title' => 'Refund Policy',
                'lede' => 'What to do if you donated by mistake.',
                'body' => "<p>If you donated by mistake or the wrong amount, contact us as soon as possible at <a href=\"mailto:{$email}\">{$email}</a> or {$contactBy} with your receipt.</p>"
                    .'<p>We will refund mistaken donations to the original payment method.</p>'
                    .'<p>Once a donation has been spent on a project or distributed, it cannot be refunded.</p>',
            ],
            'terms' => [
                'title' => 'Terms of Use',
                'lede' => 'The terms for using this website.',
                'body' => '<p>By using this website you agree to use it lawfully.</p>'
                    .'<p>Content and photos belong to Sab Ka Sahara Welfare Foundation unless stated otherwise, and may be shared with credit.</p>'
                    .'<p>We work to keep information accurate but cannot guarantee it is always complete.</p>'
                    .'<p>These terms are governed by the laws of Pakistan.</p>',
            ],
        ];

        foreach ($pages as $slug => $data) {
            Page::firstOrCreate(['slug' => $slug], $data);
        }
    }
}
