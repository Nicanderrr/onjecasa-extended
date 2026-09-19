<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ContactSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    // Display the contact page
    public function index()
    {
        $settings = ContactSetting::getSettings();
        $branches = Branch::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'phone', 'address', 'latitude', 'longitude']);

        return view('Frontend.contacts', compact('settings', 'branches'));
    }

    // Handle contact form submission
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $settings = ContactSetting::getSettings();

        // Here you can send email to the admin
        // Mail::to($settings->form_email)->send(new ContactFormMail($request->all()));

        return redirect()->back()->with('success', 'Thank you for your message. We\'ll get back to you soon!');
    }

    // Admin: Show edit form
    public function edit()
    {
        $settings = ContactSetting::getSettings();
        return view('backend.contact.edit', compact('settings'));
    }

    // Admin: Update contact settings
    public function update(Request $request)
    {
        $settings = ContactSetting::getSettings();

        $validator = Validator::make($request->all(), [
            'header_title_line1' => 'required|string|max:255',
            'header_title_line2' => 'required|string|max:255',
            'business_number' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'whatsapp_link' => 'nullable|url|max:255',
            'office_address' => 'nullable|string|max:500',
            'facebook_link' => 'nullable|url|max:255',
            'instagram_link' => 'nullable|url|max:255',
            'twitter_link' => 'nullable|url|max:255',
            'youtube_link' => 'nullable|url|max:255',
            'linkedin_link' => 'nullable|url|max:255',
            'messenger_link' => 'nullable|url|max:255',
            'skype_link' => 'nullable|url|max:255',
            'tiktok_link' => 'nullable|url|max:255',
            'form_email' => 'nullable|email|max:255',
            'header_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('_token', '_method', 'header_image');

        // Handle image upload
        if ($request->hasFile('header_image')) {
            $imagePath = $request->file('header_image')->store('contacts', 'public');
            $data['header_image'] = $imagePath;
        }

        $settings->update($data);

        return redirect()->route('admin.contact.edit')
            ->with('success', 'Contact settings updated successfully.');
    }
}
