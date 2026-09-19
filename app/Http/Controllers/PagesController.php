<?php

namespace App\Http\Controllers;
use App\Models\FAQ;
use App\Models\HomeSlide;
use App\Models\ProductPage;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function index(){
        return view('Frontend.index');
    }

    public function AboutPage(){
        $homeSlides = HomeSlide::whereIn('id', [4, 5])->get();

        return view('Frontend.story', compact('homeSlides'));
    }

    public function ProductPage(){
        return view('Frontend.products');
    }

    // public function FaqsPage(){
    //     return view('Frontend.faqs');
    // }

    public function ContactPage(){
        return view('Frontend.contacts');
    }


    public function FaqsPage()
{
    $generalFaqs = FAQ::active()->byCategory('general')->ordered()->get();
    $servicesFaqs = FAQ::active()->byCategory('services')->ordered()->get();
    
    return view('Frontend.faqs', compact('generalFaqs', 'servicesFaqs'));
}
    // public function ProductDetails(){
    //     return view('Frontend.product_details');
    // }

    public function AdminPanel(){
    $user = Auth::user();
    $notifications = Auth::user()->notifications;

    if ($user && $user->is_admin == 1) {
        // Load the admin dashboard
        return view('backend.admin', compact('notifications'));
    } else {
        // Redirect non-admins
        return redirect('home'); // Adjust this path as needed
    }
    }


    public function showProductDetail($id)
{
    $product = ProductPage::findOrFail($id);
    
    // Pass both the product and the id to the view
    return view('Frontend.product_details', compact('product', 'id'));
}


}
