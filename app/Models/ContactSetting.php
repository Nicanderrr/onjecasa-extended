<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'header_title_line1',
        'header_title_line2',
        'header_image',
        'business_number',
        'whatsapp_number',
        'whatsapp_link',
        'office_address',
        'facebook_link',
        'instagram_link',
        'twitter_link',
        'youtube_link',
        'linkedin_link',
        'messenger_link',
        'skype_link',
        'tiktok_link',
        'form_email',
        'form_enabled',
        'map_embed_url',
        'latitude',
        'longitude',
    ];

    // Get the first contact settings record (there should only be one)
    public static function getSettings()
    {
        return self::first() ?? self::create([]);
    }
}