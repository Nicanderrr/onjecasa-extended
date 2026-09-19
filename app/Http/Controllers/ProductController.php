<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Branch;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductPage;
use App\Support\BranchContext;
use App\Support\BranchProductSync;
use App\Support\MealExtras;
use App\Support\ProductOptions;
use App\Models\cart;

class ProductController extends Controller
{

    public function DisplayProduct(){
        $products['alldata'] = ProductPage::latest()->take(100000)->get();
        return view('backend.view_product', $products);
    }

    public function AddProduct(){
        $categories = Category::orderBy('name')->get();
        return view('backend.add_product', compact('categories'));
    }
       

    public function ProdDet($id){
        $product = ProductPage::find($id);
        // Check if the product exists
        if (!$product) {
            abort(404); // Show a 404 error if the product doesn't exist
        }
        return view('Frontend.product_details', compact('product'));
    }

    // Method to display the product details
    public function ProductDetails($id)
    {
        // Fetch the specific product by ID
        $product = ProductPage::findOrFail($id);
        
        // Pass the product to the view
        return view('product_details', compact('product'));
    }

    public function storeProduct(Request $request)
    {
        // Validate the request - updated with new fields
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'contents' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'gallery_images' => 'required|array|min:1',
            'gallery_images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:15000',
            'product_videos' => 'nullable|array',
            'product_videos.*' => 'nullable|file|mimes:mp4,mov,webm,m4v|max:51200',
            'thumbnail_image_index' => 'nullable|integer|min:0',
            'thumbnail_video_index' => 'nullable|integer|min:0',
            'remove_media_ids' => 'nullable|array',
            'remove_media_ids.*' => 'nullable|integer|exists:product_images,id',
            'sizes' => 'nullable|array',
            'option_prices' => 'nullable|array',
            'colors' => 'nullable|array',
            'features' => 'nullable|array',
            'sync_to_pos' => 'nullable|boolean',
        ]);
    
        // Create a new instance of ProductPage
        $product = new ProductPage();

        // Set other attributes
        $product->name = $request->name;
        $product->description = $request->description;
        $product->contents = $request->contents;
        $product->price = $request->price;
        $product->stock = $request->integer('stock');
        
        // The legacy sizes column stores purchasable unit/pack options.
        $product->sizes = ProductOptions::normalizeForStorage(
            (array) $request->input('sizes', []),
            (array) $request->input('option_prices', []),
            (float) $request->price
        );
        $product->colors = $request->colors ?: null;
        $product->features = $this->normalizeFeatureLines($request->features ?? []);
        
        // Save the record to the database
        $product->save();

        $this->storeProductGalleryImages($request, $product);
        $newVideoThumbnail = $this->storeProductVideos($request, $product);

        if ($newVideoThumbnail) {
            $this->setProductThumbnail($product, $newVideoThumbnail);
        }

        if ($request->has('sync_to_pos')) {
            $this->syncWebsiteProductToPos($product, true);
        }
    
        return redirect()->back()->with('success', 'Product added successfully.');
    }
    
    //Edit Product
    public function EditProduct($id){
        $product = ProductPage::find($id);
        if ($product) {
            $this->ensureProductHasGalleryImage($product);
            $product->load('images');
        }
        $categories = Category::orderBy('name')->get();
        return view('backend.edit_products', compact('product', 'categories'));
    }

    // Update products
    public function UpdateProducts(Request $request, $id){
        $product = ProductPage::find($id);

        // Validate the request - updated with new fields
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'contents' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:15000',
            'product_videos' => 'nullable|array',
            'product_videos.*' => 'nullable|file|mimes:mp4,mov,webm,m4v|max:51200',
            'thumbnail_existing_id' => 'nullable|integer|exists:product_images,id',
            'thumbnail_new_index' => 'nullable|integer|min:0',
            'thumbnail_video_index' => 'nullable|integer|min:0',
            'sizes' => 'nullable|array',
            'option_prices' => 'nullable|array',
            'colors' => 'nullable|array',
            'features' => 'nullable|array',
            'sync_to_pos' => 'nullable|boolean',
        ]);

        $this->ensureProductHasGalleryImage($product);
    
        $product->name = $request->name;
        $product->description = $request->description;
        $product->contents = $request->contents;
        $product->price = $request->price;
        $product->stock = $request->integer('stock');
        
        // The legacy sizes column stores purchasable unit/pack options.
        $product->sizes = ProductOptions::normalizeForStorage(
            (array) $request->input('sizes', []),
            (array) $request->input('option_prices', []),
            (float) $request->price
        );
        $product->colors = $request->colors ?: null;
        $product->features = $this->normalizeFeatureLines($request->features ?? []);
        
        $product->save();

        $this->deleteSelectedProductMedia($request, $product);
        $thumbnailChanged = $this->updateProductGalleryImages($request, $product);
        $newVideoThumbnail = $this->storeProductVideos($request, $product);

        if ($newVideoThumbnail) {
            $oldThumbnail = $product->picture;
            $this->setProductThumbnail($product, $newVideoThumbnail);
            $product->refresh();
            $thumbnailChanged = $thumbnailChanged || $oldThumbnail !== $product->picture;
        }

        if ($request->has('sync_to_pos')) {
            $this->syncWebsiteProductToPos($product, $thumbnailChanged);
        }

        return redirect()->back()->with('success', 'Product updated successfully.');
    }

    // Delete Product
    public function DeleteProduct($id){
        $deleteData = ProductPage::find($id);
        if ($deleteData) {
            $deleteData->images()->get()->each(fn (ProductImage $image) => $this->deleteStoredPicture($image->path));
        }
        $deleteData->delete();

        return redirect()->back()->with('success', 'Product deleted successfully.');
    }

    /**
     * Add a product to the cart.
     */
    public function addCart(Request $request, $id)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $product = ProductPage::findOrFail($id);
            $quantity = $request->input('quantity', 1);
            
            // Legacy names: size = option, color = encoded product extras.
            $selectedSize = $request->input('size') ?: collect(ProductOptions::options($product))->first()['name'];
            $selectedPrice = ProductOptions::selectedPrice($product, $selectedSize);
            $selectedColor = MealExtras::encodeSelection(
                (array) $request->input('extras', []),
                (array) $request->input('extra_quantities', [])
            );

            // Check if the product is already in the cart
            $cartItem = Cart::where('user_id', $user->id)
                            ->where('product_id', $id)
                            ->where('size', $selectedSize)
                            ->where('price', $selectedPrice)
                            ->where('color', $selectedColor)
                            ->first();

            if ($cartItem) {
                // Update the quantity
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                // Add new item to cart with size and color
                Cart::create([
                    'user_id'    => $user->id,
                    'product_id' => $product->id,
                    'quantity'   => $quantity,
                    'price'      => $selectedPrice,
                    'size'       => $selectedSize,
                    'color'      => $selectedColor,
                    'notification' => false,
               ]);
            }

            return redirect()->back()->with('message', 'Item has been added to cart successfully.');
        } else {
            return redirect()->route('register')->with('message', 'Please register or login to add items to cart.');
        }
    }

    /**
     * Display the cart contents.
     */
    public function viewCart()
    {
        $user = Auth::user();
        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

            return view('Frontend.cart', compact('cartItems'));
    }

    /**
     * Update the quantity of a cart item.
     */
    public function updateCart(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cartItem = Cart::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $cartItem->quantity = $request->input('quantity');
        $cartItem->save();

        return redirect()->back()->with('message', 'Cart updated successfully.');
    }

    /**
     * Remove an item from the cart.
     */
    public function removeCart($id)
    {
        $user = Auth::user();
        $cartItem = Cart::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $cartItem->delete();

        return redirect()->back()->with('message', 'Item removed from cart.');
    }

    public function checkout()
    {
        $user = Auth::user();
        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // Calculate total
        $total = $cartItems->reduce(fn ($carry, $item) => $carry + MealExtras::lineTotal($item), 0);

        return view('Frontend.checkout', compact('cartItems', 'total', 'branches'));
    }

    // Clear cart
    public function clearCart($userId)
    {
        Cart::where('user_id', $userId)->delete();
    }

    private function storeProductPicture(Request $request, ?ProductPage $product = null): string
    {
        if ($product?->picture) {
            $this->deleteStoredPicture($product->picture);
        }

        if (!$request->hasFile('picture')) {
            return $product?->picture ?? '';
        }

        $file = $request->file('picture');
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $extension;
        $destination = public_path('uploads');

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $filename);

        return 'uploads/' . $filename;
    }

    private function storeUploadedProductImage(\Illuminate\Http\UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::random(8) . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $extension;
        $destination = public_path('uploads');

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $filename);

        return 'uploads/' . $filename;
    }

    private function storeProductGalleryImages(Request $request, ProductPage $product): void
    {
        $files = $request->file('gallery_images', []);
        $thumbnailIndex = (int) $request->input('thumbnail_image_index', 0);

        foreach ($files as $index => $file) {
            $path = $this->storeUploadedProductImage($file);
            $isThumbnail = $index === $thumbnailIndex || ($thumbnailIndex < 0 && $index === 0);

            ProductImage::create([
                'product_page_id' => $product->id,
                'path' => $path,
                'media_type' => 'image',
                'is_thumbnail' => $isThumbnail,
                'sort_order' => $index,
            ]);

            if ($isThumbnail || $index === 0 && empty($product->picture)) {
                $product->forceFill(['picture' => $path])->saveQuietly();
            }
        }

        $this->ensureSingleThumbnail($product);
    }

    private function updateProductGalleryImages(Request $request, ProductPage $product): bool
    {
        $oldThumbnail = $product->picture;
        $newThumbnailImage = null;

        if ($request->filled('thumbnail_existing_id')) {
            $newThumbnailImage = $product->images()
                ->whereKey($request->integer('thumbnail_existing_id'))
                ->first();
        }

        $startOrder = (int) $product->images()->max('sort_order') + 1;
        foreach ($request->file('gallery_images', []) as $index => $file) {
            $path = $this->storeUploadedProductImage($file);
            $image = ProductImage::create([
                'product_page_id' => $product->id,
                'path' => $path,
                'media_type' => 'image',
                'is_thumbnail' => false,
                'sort_order' => $startOrder + $index,
            ]);

            if ((string) $request->input('thumbnail_new_index') !== '' && (int) $request->input('thumbnail_new_index') === $index) {
                $newThumbnailImage = $image;
            }
        }

        if (! $newThumbnailImage) {
            $newThumbnailImage = $product->thumbnailImage()->first() ?: $product->images()->first();
        }

        if ($newThumbnailImage) {
            $this->setProductThumbnail($product, $newThumbnailImage);
        }

        $this->ensureSingleThumbnail($product);
        $product->refresh();

        return $oldThumbnail !== $product->picture;
    }

    private function ensureProductHasGalleryImage(ProductPage $product): void
    {
        if ($product->images()->exists() || empty($product->picture)) {
            return;
        }

        ProductImage::create([
            'product_page_id' => $product->id,
            'path' => $product->picture,
            'media_type' => 'image',
            'is_thumbnail' => true,
            'sort_order' => 0,
        ]);
    }

    private function ensureSingleThumbnail(ProductPage $product): void
    {
        $thumbnail = $product->images()->where('is_thumbnail', true)->orderBy('sort_order')->orderBy('id')->first()
            ?: $product->images()->orderBy('sort_order')->orderBy('id')->first();

        if (! $thumbnail) {
            return;
        }

        $this->setProductThumbnail($product, $thumbnail);
    }

    private function deleteSelectedProductMedia(Request $request, ProductPage $product): void
    {
        $ids = collect($request->input('remove_media_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $mediaItems = $product->images()->whereIn('id', $ids)->get();

        foreach ($mediaItems as $media) {
            $this->deleteStoredPicture($media->path);
            $media->delete();
        }

        if (! $product->images()->exists()) {
            $product->forceFill(['picture' => ''])->saveQuietly();
        }
    }

    private function setProductThumbnail(ProductPage $product, ProductImage $thumbnail): void
    {
        $product->images()->update([
            'is_thumbnail' => false,
            'updated_at' => now(),
        ]);

        ProductImage::whereKey($thumbnail->id)->update([
            'is_thumbnail' => true,
            'updated_at' => now(),
        ]);

        $product->forceFill(['picture' => $thumbnail->path])->saveQuietly();
    }

    private function storeUploadedProductVideo(\Illuminate\Http\UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::random(8) . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $extension;
        $destination = public_path('uploads/videos');

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $filename);

        return 'uploads/videos/' . $filename;
    }

    private function storeProductVideos(Request $request, ProductPage $product): ?ProductImage
    {
        $files = $request->file('product_videos', []);

        if (empty($files)) {
            return null;
        }

        $startOrder = (int) $product->images()->max('sort_order') + 1;
        $selectedThumbnailIndex = (string) $request->input('thumbnail_video_index') !== ''
            ? (int) $request->input('thumbnail_video_index')
            : null;
        $selectedThumbnail = null;

        foreach ($files as $index => $file) {
            if (! $file) {
                continue;
            }

            $video = ProductImage::create([
                'product_page_id' => $product->id,
                'path' => $this->storeUploadedProductVideo($file),
                'media_type' => 'video',
                'is_thumbnail' => false,
                'sort_order' => $startOrder + $index,
            ]);

            if ($selectedThumbnailIndex !== null && $selectedThumbnailIndex === $index) {
                $selectedThumbnail = $video;
            }
        }

        return $selectedThumbnail;
    }

    private function deleteStoredPicture(?string $picture): void
    {
        if (empty($picture)) {
            return;
        }

        $relativePath = ltrim((string) $picture, '/');
        $publicPath = public_path($relativePath);
        $storagePath = storage_path('app/public/' . $relativePath);

        if (file_exists($publicPath)) {
            @unlink($publicPath);
        }

        if (file_exists($storagePath)) {
            @unlink($storagePath);
        }
    }

    private function normalizeFeatureLines(array $features): array
    {
        return collect($features)
            ->flatMap(fn ($feature) => preg_split('/\r\n|\r|\n|,/', (string) $feature) ?: [])
            ->map(fn ($feature) => trim($feature))
            ->filter()
            ->values()
            ->all();
    }

    private function syncWebsiteProductToPos(ProductPage $product, bool $refreshImage = false): void
    {
        $branchId = BranchContext::activeId() ?: BranchContext::defaultBranchId();
        $posProduct = $product->pos_product_id
            ? DB::table('pos_products')->where('branch_id', $branchId)->where('id', $product->pos_product_id)->first()
            : null;

        $imageName = $posProduct->image ?? null;
        $posImagePath = $this->getPosSyncImagePath($product);
        if (($refreshImage || ! $imageName) && $posImagePath) {
            $imageName = $this->copyWebsiteImageToPos($posImagePath) ?? $imageName;
        }

        $payload = [
            'code' => $posProduct->code ?? $this->makePosCode($product),
            'branch_id' => $branchId,
            'name' => (string) $product->name,
            'description' => (string) $product->contents,
            'price' => (float) $product->price,
            'stock' => max(0, (int) ($product->stock ?? 0)),
            'image' => $imageName,
            'website_product_id' => $product->id,
            'updated_at' => now(),
        ];

        if ($posProduct) {
            DB::table('pos_products')->where('id', $posProduct->id)->update($payload);
            $updatedProduct = DB::table('pos_products')->where('id', $posProduct->id)->first();
            BranchProductSync::syncProductToBranches($updatedProduct, syncStock: true);
            return;
        }

        $payload['created_at'] = now();
        $posProductId = DB::table('pos_products')->insertGetId($payload);
        $product->forceFill(['pos_product_id' => $posProductId])->saveQuietly();
        $newProduct = DB::table('pos_products')->where('id', $posProductId)->first();
        BranchProductSync::syncProductToBranches($newProduct, syncStock: true);
    }

    private function makePosCode(ProductPage $product): string
    {
        $base = Str::upper(Str::slug((string) $product->name, ''));
        $base = Str::limit($base ?: 'PRD', 8, '');
        $code = $base . '-' . random_int(1000, 9999);

        $branchId = BranchContext::activeId() ?: BranchContext::defaultBranchId();

        while (DB::table('pos_products')->where('branch_id', $branchId)->where('code', $code)->exists()) {
            $code = $base . '-' . random_int(1000, 9999);
        }

        return $code;
    }

    private function copyWebsiteImageToPos(?string $picture): ?string
    {
        $relativePath = ltrim(str_replace('\\', '/', (string) $picture), '/');
        $source = public_path($relativePath);

        if (! is_file($source)) {
            return null;
        }

        $destination = public_path('assets/admin/img/products');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION) ?: 'jpg';
        $imageName = Str::uuid() . '.' . $extension;
        copy($source, $destination . DIRECTORY_SEPARATOR . $imageName);

        return $imageName;
    }

    private function getPosSyncImagePath(ProductPage $product): ?string
    {
        $thumbnail = $product->thumbnailImage()->first();

        if ($thumbnail && $thumbnail->media_type === 'image') {
            return $thumbnail->path;
        }

        $image = $product->images()->where('media_type', 'image')->orderBy('sort_order')->orderBy('id')->first();

        return $image?->path ?: ($this->pathLooksLikeImage($product->picture) ? $product->picture : null);
    }

    private function pathLooksLikeImage(?string $path): bool
    {
        return (bool) preg_match('/\.(jpe?g|png|gif|webp)$/i', (string) $path);
    }
}

