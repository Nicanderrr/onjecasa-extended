<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactSetting;

class ContactSettingsSeeder extends Seeder
{
    public function run(): void
    {
        ContactSetting::create([
            'header_title_line1' => 'Say hello to us',
            'header_title_line2' => 'love to hear you',
            'business_number' => '233204855546',
            'whatsapp_number' => '+233204855546',
            'whatsapp_link' => 'https://wa.me/message/XI4NUFIYKMK3G1',
            'office_address' => 'University Of Ghana Campus, Accra',
            'instagram_link' => 'https://www.instagram.com/yourpage',
            'youtube_link' => '#',
            'messenger_link' => '#',
            'skype_link' => '#',
            'form_email' => 'contact@urbanvogue.com',
        ]);
    }
}