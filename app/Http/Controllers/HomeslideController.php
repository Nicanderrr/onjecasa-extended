<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HomeSlide;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HomeslideController extends Controller
{
    public function HomeSlide(){
        $homeslide = $this->firstOrCreateSlide(1, [
            'main_header' => 'Groceries Ready Daily',
            'small_header' => 'Groceries, household essentials, beverages, and fresh produce from ONJECASA.',
            'picture' => 'themes/zabaga/assets/images/resources/banner-one-img-1-1.jpg',
        ]);
        $homeslide2 = $this->firstOrCreateSlide(2, [
            'main_header' => 'Everyday Essentials In Stock',
            'small_header' => 'Shop pantry staples, drinks, personal care, and home supplies.',
            'picture' => 'themes/zabaga/assets/images/resources/banner-one-img-1-2.jpg',
        ]);
        $homeslide3 = $this->firstOrCreateSlide(3, [
            'main_header' => 'Pickup, Delivery & Bulk Orders',
            'small_header' => 'Fresh produce, add-ons, family packs, office restocks, and delivery deals.',
            'picture' => 'themes/zabaga/assets/images/resources/banner-one-img-1-3.jpg',
        ]);
        $aboutImageOne = $this->firstOrCreateSlide(4, [
            'main_header' => 'About Us Image One',
            'small_header' => 'Large photo beside the ONJECASA homepage section.',
            'picture' => 'themes/zabaga/assets/images/resources/about-three-img-1.jpg',
        ]);
        $aboutImageTwo = $this->firstOrCreateSlide(5, [
            'main_header' => 'About Us Image Two',
            'small_header' => 'Small overlay photo beside the ONJECASA homepage section.',
            'picture' => 'themes/zabaga/assets/images/resources/about-three-img-2.jpg',
        ]);
        $orderModeOne = $this->firstOrCreateSlide(6, [
            'main_header' => 'Order Mode Image One',
            'small_header' => 'Photo for pickup orders in the shopping modes homepage section.',
            'picture' => 'themes/zabaga/assets/images/resources/event-1-1.jpg',
        ]);
        $orderModeTwo = $this->firstOrCreateSlide(7, [
            'main_header' => 'Order Mode Image Two',
            'small_header' => 'Photo for delivery orders in the shopping modes homepage section.',
            'picture' => 'themes/zabaga/assets/images/resources/event-1-2.jpg',
        ]);
        $orderModeThree = $this->firstOrCreateSlide(8, [
            'main_header' => 'Order Mode Image Three',
            'small_header' => 'Photo for office restocks in the shopping modes homepage section.',
            'picture' => 'themes/zabaga/assets/images/resources/event-1-3.jpg',
        ]);
        $orderModeFour = $this->firstOrCreateSlide(9, [
            'main_header' => 'Order Mode Image Four',
            'small_header' => 'Photo for family packs in the shopping modes homepage section.',
            'picture' => 'themes/zabaga/assets/images/resources/event-1-4.jpg',
        ]);
        $orderModeFive = $this->firstOrCreateSlide(10, [
            'main_header' => 'Order Mode Image Five',
            'small_header' => 'Photo for bulk and event supply orders in the shopping modes homepage section.',
            'picture' => 'themes/zabaga/assets/images/resources/event-1-5.jpg',
        ]);
        $menuFavoritesBg = $this->firstOrCreateSlide(11, [
            'main_header' => 'Store Favorites Background',
            'small_header' => 'Background image behind ONJECASA store favorites: everyday products ready for pickup and delivery.',
            'picture' => 'themes/zabaga/assets/images/backgrounds/causes-three-bg.jpg',
        ]);
        $pageBannerBg = $this->firstOrCreateSlide(12, [
            'main_header' => 'All Pages Banner Background',
            'small_header' => 'Background image used behind page titles on About, Products, Contact, Cart, Checkout, and Product Details.',
            'picture' => 'themes/zabaga/assets/images/backgrounds/page-header-bg.jpg',
        ]);
        $siteFooterBg = $this->firstOrCreateSlide(13, [
            'main_header' => 'All Pages Footer Background',
            'small_header' => 'Background image used in the public website footer.',
            'picture' => 'themes/zabaga/assets/images/backgrounds/site-footer-two-bg.jpg',
        ]);
        $menuFavoritesOverlayStrength = (int) (DB::table('pos_settings')->where('key', 'menu_favorites_overlay_strength')->value('value') ?? 90);
        $menuFavoritesOverlayColor = DB::table('pos_settings')->where('key', 'menu_favorites_overlay_color')->value('value') ?: '#111111';

        return view('backend.homeslide', compact(
            'homeslide',
            'homeslide2',
            'homeslide3',
            'aboutImageOne',
            'aboutImageTwo',
            'orderModeOne',
            'orderModeTwo',
            'orderModeThree',
            'orderModeFour',
            'orderModeFive',
            'menuFavoritesBg',
            'pageBannerBg',
            'siteFooterBg',
            'menuFavoritesOverlayStrength',
            'menuFavoritesOverlayColor'
        ));

    }
    

//     public function HomeSlide()
// {
//     $homeslide = HomeSlide::first(); // Ensure this is returning data
//     if (!$homeslide) {
//         // Handle the case where no data is found
//         abort(404, 'Home slide data not found');
//     }
//     return view('backend.homeslide', compact('homeslide'));
// }








public function HeroUpdate(Request $request)
{
    return $this->updateHeroSlide($request);
} 


public function HeroUpdate2(Request $request)
{
    return $this->updateHeroSlide($request);
}











public function HeroUpdate3(Request $request)
{
    return $this->updateHeroSlide($request);
}

public function removeMedia(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:home_slides,id',
        'type' => 'required|in:image,video',
    ]);

    $homeslide = HomeSlide::findOrFail($validated['id']);

    if ($validated['type'] === 'video') {
        if ($homeslide->video_path) {
            Storage::disk('public')->delete($homeslide->video_path);
        }

        $homeslide->video_path = null;
    }

    if ($validated['type'] === 'image') {
        if ($homeslide->picture && str_starts_with(str_replace('\\', '/', $homeslide->picture), 'uploads/')) {
            Storage::disk('public')->delete($homeslide->picture);
        }

        $homeslide->picture = $this->defaultPictureForSlide((int) $homeslide->id);
    }

    $homeslide->save();

    return redirect()->back()->with('success', ucfirst($validated['type']) . ' removed successfully.');
}

public function updateMenuFavoritesBackground(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:home_slides,id',
        'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'overlay_strength' => 'required|integer|min:0|max:95',
        'overlay_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
    ]);

    $homeslide = HomeSlide::findOrFail($validated['id']);

    if ($request->hasFile('picture')) {
        if ($homeslide->picture && str_starts_with(str_replace('\\', '/', $homeslide->picture), 'uploads/')) {
            Storage::disk('public')->delete($homeslide->picture);
        }

        $homeslide->picture = $request->file('picture')->store('uploads', 'public');
    }

    $homeslide->main_header = 'Store Favorites Background';
    $homeslide->small_header = 'Background image behind ONJECASA store favorites: everyday products ready for pickup and delivery.';
    $homeslide->save();

    $this->setSetting('menu_favorites_overlay_strength', (string) $validated['overlay_strength']);
    $this->setSetting('menu_favorites_overlay_color', $validated['overlay_color']);

    return redirect()->back()->with('success', 'Store favorites background updated successfully.');
}

public function updateGlobalSiteImages(Request $request)
{
    $validated = $request->validate([
        'page_banner_id' => 'required|exists:home_slides,id',
        'site_footer_id' => 'required|exists:home_slides,id',
        'page_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'site_footer' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
    ]);

    $pageBanner = HomeSlide::findOrFail($validated['page_banner_id']);
    $siteFooter = HomeSlide::findOrFail($validated['site_footer_id']);

    if ($request->hasFile('page_banner')) {
        if ($pageBanner->picture && str_starts_with(str_replace('\\', '/', $pageBanner->picture), 'uploads/')) {
            Storage::disk('public')->delete($pageBanner->picture);
        }

        $pageBanner->picture = $request->file('page_banner')->store('uploads', 'public');
    }

    $pageBanner->main_header = 'All Pages Banner Background';
    $pageBanner->small_header = 'Background image used behind page titles on About, Products, Contact, Cart, Checkout, and Product Details.';
    $pageBanner->save();

    if ($request->hasFile('site_footer')) {
        if ($siteFooter->picture && str_starts_with(str_replace('\\', '/', $siteFooter->picture), 'uploads/')) {
            Storage::disk('public')->delete($siteFooter->picture);
        }

        $siteFooter->picture = $request->file('site_footer')->store('uploads', 'public');
    }

    $siteFooter->main_header = 'All Pages Footer Background';
    $siteFooter->small_header = 'Background image used in the public website footer.';
    $siteFooter->save();

    return redirect()->back()->with('success', 'Global banner and footer images updated successfully.');
}

private function firstOrCreateSlide(int $id, array $attributes): HomeSlide
{
    $slide = HomeSlide::find($id);

    if ($slide) {
        return $slide;
    }

    $slide = new HomeSlide($attributes);
    $slide->id = $id;
    $slide->save();

    return $slide;
}

private function updateHeroSlide(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:home_slides,id',
        'main_header' => 'required|string',
        'small_header' => 'required|string',
        'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'video' => 'nullable|file|mimes:mp4,mov,webm,m4v|max:51200',
    ]);

    $homeslide = HomeSlide::findOrFail($validated['id']);
    $homeslide->main_header = $validated['main_header'];
    $homeslide->small_header = $validated['small_header'];

    if ($request->hasFile('picture')) {
        $homeslide->picture = $request->file('picture')->store('uploads', 'public');
    }

    if ($request->hasFile('video')) {
        if ($homeslide->video_path) {
            Storage::disk('public')->delete($homeslide->video_path);
        }

        $homeslide->video_path = $request->file('video')->store('uploads/videos', 'public');
    }

    $homeslide->save();

    return redirect()->back()->with('success', 'Home slide updated successfully');
}

private function defaultPictureForSlide(int $id): string
{
    return match ($id) {
        2 => 'themes/zabaga/assets/images/resources/banner-one-img-1-2.jpg',
        3 => 'themes/zabaga/assets/images/resources/banner-one-img-1-3.jpg',
        4 => 'themes/zabaga/assets/images/resources/about-three-img-1.jpg',
        5 => 'themes/zabaga/assets/images/resources/about-three-img-2.jpg',
        6 => 'themes/zabaga/assets/images/resources/event-1-1.jpg',
        7 => 'themes/zabaga/assets/images/resources/event-1-2.jpg',
        8 => 'themes/zabaga/assets/images/resources/event-1-3.jpg',
        9 => 'themes/zabaga/assets/images/resources/event-1-4.jpg',
        10 => 'themes/zabaga/assets/images/resources/event-1-5.jpg',
        11 => 'themes/zabaga/assets/images/backgrounds/causes-three-bg.jpg',
        12 => 'themes/zabaga/assets/images/backgrounds/page-header-bg.jpg',
        13 => 'themes/zabaga/assets/images/backgrounds/site-footer-two-bg.jpg',
        default => 'themes/zabaga/assets/images/resources/banner-one-img-1-1.jpg',
    };
}

private function setSetting(string $key, string $value): void
{
    DB::table('pos_settings')->updateOrInsert(
        ['key' => $key],
        ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
    );
}




}

