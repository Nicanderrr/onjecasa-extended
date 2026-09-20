<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\HomeslideController;
use App\Http\Controllers\MealExtraController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OnlineOrderWorkflowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\AIController as PosAIController;
use App\Http\Controllers\Admin\AuditTrailController as PosAuditTrailController;
use App\Http\Controllers\Admin\BranchSwitchController;
use App\Http\Controllers\Admin\CategoryCrudController as PosCategoryCrudController;
use App\Http\Controllers\Admin\DashboardController as PosDashboardController;
use App\Http\Controllers\Admin\OrderController as PosOrderController;
use App\Http\Controllers\Admin\PaymentController as PosPaymentController;
use App\Http\Controllers\Admin\ProductCrudController as PosProductCrudController;
use App\Http\Controllers\Admin\ReceiptController as PosReceiptController;
use App\Http\Controllers\Admin\SalesController as PosSalesController;
use App\Http\Controllers\Admin\SettingsController as PosSettingsController;
use App\Http\Controllers\Admin\StaffCrudController as PosStaffCrudController;
use App\Http\Controllers\Admin\SystemUserController as PosSystemUserController;
use App\Http\Controllers\Cashier\PageController as CashierPageController;
use App\Http\Controllers\Cashier\ReceiptController as CashierReceiptController;
use App\Http\Controllers\Cashier\SaleController as CashierSaleController;
use App\Http\Controllers\CustomerReceiptController;
use App\Http\Controllers\SuperAdminController;

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return view('Frontend.index');
});

Route::get('/r/{token}', [CustomerReceiptController::class, 'show'])->name('customer.receipts.show');

Route::get('/index', function () {
    return view('Frontend.index');
});

Route::get('/frontend', function () {
    return view('Frontend.index');
})->name('frontend.index');

Route::get('/media/product/{path}', function (string $path) {
    $relativePath = ltrim(str_replace('\\', '/', $path), '/');

    abort_if($relativePath === '' || str_contains($relativePath, '..'), 404);

    $candidates = [
        public_path($relativePath),
        storage_path('app/public/' . $relativePath),
    ];

    foreach ($candidates as $candidate) {
        $realPath = realpath($candidate);

        if (!$realPath || !is_file($realPath)) {
            continue;
        }

        $publicRoot = realpath(public_path());
        $storageRoot = realpath(storage_path('app/public'));

        if (
            ($publicRoot && str_starts_with($realPath, $publicRoot)) ||
            ($storageRoot && str_starts_with($realPath, $storageRoot))
        ) {
            return response()->file($realPath);
        }
    }

    abort(404);
})->where('path', '.*')->name('product.media');

Route::get('/dashboard', [AdminController::class, 'dashboard'])
    ->middleware(['auth', 'admin'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Frontend pages
Route::get('/home', [PagesController::class, 'index'])->name('home_page');
Route::get('/about', [PagesController::class, 'AboutPage'])->name('about_page');
Route::get('/products', [PagesController::class, 'ProductPage'])->name('product_page');
Route::get('/faqs', [PagesController::class, 'FaqsPage'])->name('faqs_page');
Route::get('/contact', [PagesController::class, 'contactPage'])->name('contact_page');
Route::get('/productdetails/{id}', [PagesController::class, 'showProductDetail'])->name('product_details');

// Frontend contact submit
Route::get('/contact', [ContactController::class, 'index'])->name('contact_page');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');

// Cart & checkout
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/snapshot', [NotificationController::class, 'snapshot'])->name('notifications.snapshot');

    Route::get('/cart', [ProductController::class, 'viewCart'])->name('view_cart');
    Route::post('/addcart/{id}', [ProductController::class, 'addCart'])->name('addcart');
    Route::put('/cart/update/{id}', [ProductController::class, 'updateCart'])->name('update_cart');
    Route::delete('/cart/remove/{id}', [ProductController::class, 'removeCart'])->name('remove_cart');
    Route::get('/checkout', [ProductController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/confirm', [OrderController::class, 'confirmOrder'])->name('checkout.confirm');
    Route::get('/checkout/paystack/callback', [OrderController::class, 'paystackCallback'])->name('paystack.callback');

    Route::get('/order-history', [OrderController::class, 'viewOrderHistory'])->name('order.history');
    Route::get('/order-details/{orderId}', [OrderController::class, 'showOrderDetails'])->name('order.details');
    Route::get('/order-cancel/{orderId}', [OrderController::class, 'cancelOrder'])->name('order.cancel');
    Route::post('/reorder/{orderId}', [OrderController::class, 'reorder'])->name('order.reorder');
});

// Admin-protected routes
Route::middleware(['auth', 'admin'])->group(function () {
    // Home slider management
    Route::get('/homeslides', [HomeslideController::class, 'HomeSlide'])->name('home_slide');
    Route::post('/hero/update', [HomeslideController::class, 'HeroUpdate'])->name('updateHero');
    Route::post('/heroTwo/update', [HomeslideController::class, 'HeroUpdate2'])->name('updateHero2');
    Route::post('/heroThree3/update', [HomeslideController::class, 'HeroUpdate3'])->name('updateHero3');
    Route::post('/menu-favorites/background/update', [HomeslideController::class, 'updateMenuFavoritesBackground'])->name('updateMenuFavoritesBackground');
    Route::post('/site-global-images/update', [HomeslideController::class, 'updateGlobalSiteImages'])->name('updateGlobalSiteImages');
    Route::delete('/hero/media/remove', [HomeslideController::class, 'removeMedia'])->name('removeHeroMedia');

    // Product management
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/meal-extras', [MealExtraController::class, 'index'])->name('meal_extras.index');
    Route::post('/meal-extras', [MealExtraController::class, 'store'])->name('meal_extras.store');
    Route::get('/meal-extras/{mealExtra}/edit', [MealExtraController::class, 'edit'])->name('meal_extras.edit');
    Route::put('/meal-extras/{mealExtra}', [MealExtraController::class, 'update'])->name('meal_extras.update');
    Route::delete('/meal-extras/{mealExtra}', [MealExtraController::class, 'destroy'])->name('meal_extras.destroy');
    Route::get('/product/Add', [ProductController::class, 'AddProduct'])->name('add_product');
    Route::get('/product/display', [ProductController::class, 'DisplayProduct'])->name('view_product');
    Route::post('/product/update', [ProductController::class, 'storeProduct'])->name('store-product');
    Route::get('/delete/{id}', [ProductController::class, 'DeleteProduct'])->name('deleteproduct');
    Route::get('/edit/{id}', [ProductController::class, 'EditProduct'])->name('editproduct');
    Route::post('/update/{id}', [ProductController::class, 'UpdateProducts'])->name('updateproducts');

    Route::prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.panel');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        Route::get('/orders', [AdminController::class, 'showNewOrders'])->name('admin_orders');
        Route::get('/orders/delete/{id}', [AdminController::class, 'DeleteOrder'])->name('deleteorder');
        Route::get('/allorders', [AdminController::class, 'showAllOrders'])->name('all_orders');

        Route::post('/order/{id}/update-status', [AdminController::class, 'updateOrderStatus'])->name('admin.order.update-status');
        Route::post('/order/batch/{orderId}/update-status', [AdminController::class, 'updateOrderBatchStatus'])->name('admin.order.batch-update');
        Route::post('/orders/bulk-update', [AdminController::class, 'bulkUpdateOrderStatus'])->name('admin.orders.bulk-update');

        Route::post('/notifications/read', [AdminController::class, 'markNotificationsRead'])->name('admin.notifications.read');
        Route::post('/notifications/test-order-email', [AdminController::class, 'sendTestOrderEmail'])->name('admin.notifications.test-order-email');

        // FAQ management
        Route::get('/faqs', [FAQController::class, 'adminIndex'])->name('admin.faqs.index');
        Route::get('/faqs/create', [FAQController::class, 'create'])->name('admin.faqs.create');
        Route::post('/faqs', [FAQController::class, 'store'])->name('admin.faqs.store');
        Route::get('/faqs/{id}/edit', [FAQController::class, 'edit'])->name('admin.faqs.edit');
        Route::put('/faqs/{id}', [FAQController::class, 'update'])->name('admin.faqs.update');
        Route::delete('/faqs/{id}', [FAQController::class, 'destroy'])->name('admin.faqs.destroy');
        Route::get('/faqs/{id}/toggle', [FAQController::class, 'toggleActive'])->name('admin.faqs.toggle');

        // Contact settings
        Route::get('/contact/edit', [ContactController::class, 'edit'])->name('admin.contact.edit');
        Route::put('/contact/update', [ContactController::class, 'update'])->name('admin.contact.update');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::post('/branches/switch', [BranchSwitchController::class, 'switch'])->name('branches.switch');

    Route::middleware('superadmin')->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard.alias');
        Route::get('/branches', [SuperAdminController::class, 'branches'])->name('branches');
        Route::post('/branches', [SuperAdminController::class, 'storeBranch'])->name('branches.store');
        Route::patch('/branches/{branch}', [SuperAdminController::class, 'updateBranch'])->name('branches.update');
        Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
        Route::post('/users', [SuperAdminController::class, 'storeUser'])->name('users.store');
        Route::patch('/users/{user}', [SuperAdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [SuperAdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/audit', [SuperAdminController::class, 'auditLogs'])->name('audit');
        Route::get('/security', [SuperAdminController::class, 'security'])->name('security');
        Route::put('/security', [SuperAdminController::class, 'updateSecurity'])->name('security.update');
        Route::get('/settings', [SuperAdminController::class, 'settingsPage'])->name('settings');
        Route::get('/maintenance', [SuperAdminController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance/clear-cache', [SuperAdminController::class, 'clearCache'])->name('maintenance.clear-cache');
        Route::post('/maintenance/toggle', [SuperAdminController::class, 'toggleMaintenance'])->name('maintenance.toggle');
    });

    Route::middleware('role:admin')->prefix('pos-admin')->name('pos.admin.')->group(function () {
        Route::get('/dashboard', [PosDashboardController::class, 'index'])->name('dashboard');
        Route::resource('products', PosProductCrudController::class)->except(['show']);
        Route::get('products-import', [PosProductCrudController::class, 'importForm'])->name('products.import');
        Route::get('products-import/template', [PosProductCrudController::class, 'downloadImportTemplate'])->name('products.import.template');
        Route::post('products-import', [PosProductCrudController::class, 'import'])->name('products.import.store');
        Route::resource('categories', PosCategoryCrudController::class)->except(['show']);
        Route::resource('staff', PosStaffCrudController::class)->except(['show']);
        Route::resource('users', PosSystemUserController::class)->except(['show']);

        Route::get('orders/create', [PosOrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [PosOrderController::class, 'store'])->name('orders.store');
        Route::get('orders', [PosOrderController::class, 'index'])->name('orders.index');
        Route::get('online-orders', [OnlineOrderWorkflowController::class, 'adminIndex'])->name('online-orders.index');
        Route::post('online-orders/{orderId}/accept', [OnlineOrderWorkflowController::class, 'accept'])->name('online-orders.accept');
        Route::post('online-orders/{orderId}/status', [OnlineOrderWorkflowController::class, 'updateStatus'])->name('online-orders.status');
        Route::get('orders/{id}', [PosOrderController::class, 'show'])->name('orders.show');
        Route::get('payments', [PosPaymentController::class, 'index'])->name('payments.index');
        Route::get('receipts', [PosReceiptController::class, 'index'])->name('receipts.index');
        Route::get('receipts/{id}', [PosReceiptController::class, 'show'])->name('receipts.show');
        Route::get('receipts/{id}/print', [PosReceiptController::class, 'print'])->name('receipts.print');
        Route::get('orders-reports', [PosOrderController::class, 'index'])->name('orders-reports.index');
        Route::get('payments-reports', [PosPaymentController::class, 'index'])->name('payments-reports.index');

        Route::get('sales', [PosSalesController::class, 'index'])->name('sales.index');
        Route::get('settings', [PosSettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [PosSettingsController::class, 'update'])->name('settings.update');
        Route::get('audit-trails', [PosAuditTrailController::class, 'index'])->name('audit-trails.index');

        Route::get('ai', [PosAIController::class, 'index'])->name('ai.index');
        Route::post('ai/chat', [PosAIController::class, 'chat'])->name('ai.chat');
        Route::post('ai/analyze', [PosAIController::class, 'analyze'])->name('ai.analyze');
        Route::get('ai/alerts', [PosAIController::class, 'alerts'])->name('ai.alerts');
    });

    Route::middleware('role:cashier')->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/sales/new', [CashierSaleController::class, 'create'])->name('sales.create');
        Route::post('/sales', [CashierSaleController::class, 'store'])->name('sales.store');
        Route::put('/settings', [CashierPageController::class, 'updateSettings'])->name('settings.update');
        Route::get('/online-orders', [OnlineOrderWorkflowController::class, 'cashierIndex'])->name('online-orders.index');
        Route::post('/online-orders/{orderId}/accept', [OnlineOrderWorkflowController::class, 'accept'])->name('online-orders.accept');
        Route::post('/online-orders/{orderId}/status', [OnlineOrderWorkflowController::class, 'updateStatus'])->name('online-orders.status');
        Route::get('/receipts/{id}', [CashierReceiptController::class, 'show'])->name('receipts.show');
        Route::get('/receipts/{id}/print', [CashierReceiptController::class, 'print'])->name('receipts.print');
        Route::get('/{page}', [CashierPageController::class, 'show'])->name('pages.show');
    });
});
