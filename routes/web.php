<?php

use Illuminate\Support\Facades\Route;

// Controller Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\FieldOfficerController as AdminFieldOfficerController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\AboutController as AdminAboutController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Admin\CinemaController as AdminCinemaController;
use App\Http\Controllers\Admin\StudioController as AdminStudioController;
use App\Http\Controllers\Admin\MovieController as AdminMovieController;
use App\Http\Controllers\Admin\ShowtimeController as AdminShowtimeController;
use App\Http\Controllers\Admin\MonitoringController as AdminMonitoringController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;

// Controller Officer
use App\Http\Controllers\Officer\AuthController as OfficerAuthController;
use App\Http\Controllers\Officer\DashboardController as OfficerDashboardController;
use App\Http\Controllers\Officer\HistoryController as OfficerHistoryController;
use App\Http\Controllers\Officer\ProfileController as OfficerProfileController;
use App\Http\Controllers\Officer\ScanController as OfficerScanController;

// User Controller
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\EventController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\BlogController;
use App\Http\Controllers\User\FaqController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\TicketController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\PageController;
use App\Http\Controllers\User\AboutController;
use App\Http\Controllers\User\RefundController;
use App\Http\Controllers\ProfileController;

// Cinema Controller
use App\Http\Controllers\Cinema\HomeController as CinemaHomeController;
use App\Http\Controllers\Cinema\CinemaController as CinemaCinemaController;
use App\Http\Controllers\Cinema\MovieController as CinemaMovieController;
use App\Http\Controllers\Cinema\OrderController as CinemaOrderController;
use App\Http\Controllers\Cinema\SearchController as CinemaSearchController;
use App\Http\Controllers\Cinema\TicketController as CinemaTicketController;
use App\Http\Controllers\Cinema\SeatMapController as CinemaSeatMapController;
use App\Http\Controllers\Cinema\CheckoutController as CinemaCheckoutController;

// Midtrans Webhook
Route::post('/midtrans/notification', [CheckoutController::class, 'notification'])->name('midtrans.notification');

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('welcome');

// Checkout
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/events/{event:slug}/checkout', [CheckoutController::class, 'index'])->name('events.checkout');
    Route::post('/events/{event:slug}/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/finish', [CheckoutController::class, 'finish'])->name('checkout.finish');
});

// Event
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');

// Blog and FAQ
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{blog:slug}', [BlogController::class, 'show'])->name('blogs.show');
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');

// Privacy Terms
Route::get('/kebijakan-privasi', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/syarat-ketentuan',  [PageController::class, 'terms'])->name('pages.terms');
Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');

// About
Route::get('/tentang-kami', [AboutController::class, 'index'])->name('about.index');

// Cinema
Route::prefix('cinema')->name('cinema.')->group(function () {

    // Public
    Route::get('/', [CinemaHomeController::class,   'index'])->name('index');
    Route::get('/movies', [CinemaMovieController::class,  'index'])->name('movies');
    Route::get('/movies/{movie:slug}', [CinemaMovieController::class, 'show'])->name('movies.show');
    Route::get('/coming-soon', [CinemaMovieController::class,  'coming'])->name('coming');
    Route::get('/cinemas', [CinemaCinemaController::class, 'index'])->name('cinemas');
    Route::get('/cinemas/{cinema}', [CinemaCinemaController::class, 'show'])->name('cinemas.show');
    Route::get('/search', CinemaSearchController::class)->name('search');

    Route::post('/notification', [CinemaCheckoutController::class, 'notification'])->name('checkout.notification');

    // Auth required
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/my-tickets', [CinemaTicketController::class, 'index'])->name('my-tickets');
        Route::get('/my-tickets/{ticket}', [CinemaTicketController::class, 'show'])->name('tickets.show');
        Route::get('/my-tickets/{ticket}/qr', [CinemaTicketController::class, 'qr'])->name('tickets.qr');
        Route::get('/my-orders', [CinemaOrderController::class, 'index'])->name('my-orders');
        Route::get('/my-orders/{order}', [CinemaOrderController::class, 'show'])->name('orders.show');
        Route::patch('/my-orders/{order}/cancel', [CinemaOrderController::class, 'cancel'])->name('orders.cancel');

        // Seat map
        Route::get('/showtime/{showtime}/seats', [CinemaSeatMapController::class, 'index'])->name('seat-map');

        // Seat map
        Route::get('/showtime/{showtime}/seats', [CinemaSeatMapController::class, 'index'])->name('seat-map');
        Route::post('/showtime/{showtime}/seats/lock', [CinemaSeatMapController::class, 'lock'])->name('seat-map.lock');
        Route::post('/showtime/{showtime}/seats/release', [CinemaSeatMapController::class, 'release'])->name('seat-map.release');
        Route::get('/showtime/{showtime}/seats/status', [CinemaSeatMapController::class, 'status'])->name('seat-map.status');
        Route::post('/showtime/{showtime}/seats/extend', [CinemaSeatMapController::class, 'extend'])->name('seat-map.extend');

        // Checkout
        Route::get('/showtime/{showtime}/checkout', [CinemaCheckoutController::class, 'index'])->name('checkout');
        Route::post('/showtime/{showtime}/checkout', [CinemaCheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/checkout/finish', [CinemaCheckoutController::class, 'finish'])->name('checkout.finish');
    });
});

// User Authenticated Routes
Route::middleware(['auth', 'verified', 'check.expired.order'])->prefix('user')->name('user.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

    // Refunds
    Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
    Route::get('/refunds/{refund}', [RefundController::class, 'show'])->name('refunds.show');
    Route::get('/orders/{order}/refund', [RefundController::class, 'create'])->name('refunds.create');
    Route::post('/orders/{order}/refund', [RefundController::class, 'store'])->name('refunds.store');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route untuk Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Event
    Route::resource('events', AdminEventController::class);

    // Ticket
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');

    // Order
    Route::get('/orders/export', [AdminOrderController::class, 'export'])->name('orders.export');
    Route::resource('orders', AdminOrderController::class);
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Category
    Route::resource('categories', AdminCategoryController::class)
        ->except(['create', 'show', 'edit']);

    // Transaction
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/export', [AdminTransactionController::class, 'export'])->name('transactions.export');
    Route::get('/transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
        Route::get('/methods', [AdminPaymentController::class, 'methods'])->name('methods');
        Route::post('/methods', [AdminPaymentController::class, 'storeMethod'])->name('methods.store');
        Route::put('/methods/{method}', [AdminPaymentController::class, 'updateMethod'])->name('methods.update');
        Route::delete('/methods/{method}', [AdminPaymentController::class, 'destroyMethod'])->name('methods.destroy');
        Route::post('/methods/{method}/bank-accounts', [AdminPaymentController::class, 'storeBankAccount'])->name('bank-accounts.store');
        Route::delete('/bank-accounts/{bankAccount}', [AdminPaymentController::class, 'destroyBankAccount'])->name('bank-accounts.destroy');
        Route::get('/{payment}', [AdminPaymentController::class, 'show'])->name('show');
        Route::patch('/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('verify');
        Route::patch('/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('reject');
    });

    // Refunds
    Route::resource('refunds', AdminRefundController::class)->only(['index', 'show']);
    Route::patch('/refunds/{refund}/approve', [AdminRefundController::class, 'approve'])->name('refunds.approve');
    Route::patch('/refunds/{refund}/reject',  [AdminRefundController::class, 'reject'])->name('refunds.reject');

    // Blog
    Route::resource('blogs', AdminBlogController::class);

    // FAQ
    Route::get('/faqs', [AdminFaqController::class, 'index'])->name('faqs.index');
    Route::post('/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
    Route::put('/faqs/{faq}', [AdminFaqController::class, 'update'])->name('faqs.update');
    Route::delete('/faqs/{faq}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy');
    Route::post('/faqs/reorder', [AdminFaqController::class, 'reorder'])->name('faqs.reorder');

    // About
    Route::get('/about', [AdminAboutController::class, 'index'])->name('about.index');
    Route::put('/{about}', [AdminAboutController::class, 'update'])->name('about.update');
    Route::put('/{about}/items', [AdminAboutController::class, 'updateItems'])->name('about.items');
    Route::patch('/{about}/toggle', [AdminAboutController::class, 'toggleActive'])->name('about.toggle');
    Route::post('/reorder', [AdminAboutController::class, 'reorder'])->name('about.reorder');

    // Page
    Route::resource('pages', AdminPageController::class);

    // Users
    Route::resource('users', AdminUserController::class);
    Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');

    // Field Officers
    Route::get('/officers', [AdminFieldOfficerController::class, 'index'])->name('officers.index');
    Route::post('/officers', [AdminFieldOfficerController::class, 'store'])->name('officers.store');
    Route::get('/officers/{officer}', [AdminFieldOfficerController::class, 'show'])->name('officers.show');
    Route::put('/officers/{officer}', [AdminFieldOfficerController::class, 'update'])->name('officers.update');
    Route::delete('/officers/{officer}', [AdminFieldOfficerController::class, 'destroy'])->name('officers.destroy');

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [AdminSettingController::class, 'index'])->name('index');
        Route::patch('/general', [AdminSettingController::class, 'updateGeneral'])->name('general.update');
        Route::patch('/social', [AdminSettingController::class, 'updateSocial'])->name('social.update');
        Route::patch('/seo', [AdminSettingController::class, 'updateSeo'])->name('seo.update');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [AdminNotificationController::class, 'index'])->name('index');
        Route::get('/unread', [AdminNotificationController::class, 'unread'])->name('unread');
        Route::patch('/read', [AdminNotificationController::class, 'markAsRead'])->name('read');
        Route::delete('/{id}', [AdminNotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [AdminNotificationController::class, 'destroyAll'])->name('destroyAll');
    });

    // Cinema
    Route::prefix('cinema')->name('cinema.')->group(function () {
        Route::get('/', [AdminCinemaController::class, 'index'])->name('index');
        Route::get('/create', [AdminCinemaController::class, 'create'])->name('create');
        Route::post('/', [AdminCinemaController::class, 'store'])->name('store');
        Route::get('/{cinema}', [AdminCinemaController::class, 'show'])->name('show');
        Route::get('/{cinema}/edit', [AdminCinemaController::class, 'edit'])->name('edit');
        Route::put('/{cinema}', [AdminCinemaController::class, 'update'])->name('update');
        Route::patch('/{cinema}/toggle', [AdminCinemaController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{cinema}', [AdminCinemaController::class, 'destroy'])->name('destroy');

        // Studios
        Route::post('/{cinema}/studios', [AdminCinemaController::class, 'storeStudio'])->name('studios.store');
        Route::patch('/{cinema}/studios/{studio}/toggle', [AdminCinemaController::class, 'toggleStudio'])->name('studios.toggle');
        Route::delete('/{cinema}/studios/{studio}', [AdminCinemaController::class, 'destroyStudio'])->name('studios.destroy');
    });

    // Studios
    Route::prefix('studios')->name('studios.')->group(function () {
        Route::get('/', [AdminStudioController::class, 'index'])->name('index');
        Route::get('/create', [AdminStudioController::class, 'create'])->name('create');
        Route::post('/', [AdminStudioController::class, 'store'])->name('store');
        Route::get('/{studio}', [AdminStudioController::class, 'show'])->name('show');
        Route::get('/{studio}/edit', [AdminStudioController::class, 'edit'])->name('edit');
        Route::put('/{studio}', [AdminStudioController::class, 'update'])->name('update');
        Route::patch('/{studio}/toggle', [AdminStudioController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{studio}', [AdminStudioController::class, 'destroy'])->name('destroy');
        Route::post('/{studio}/seat', [AdminStudioController::class, 'updateSeat'])->name('seat.update');
        Route::post('/{studio}/regenerate', [AdminStudioController::class, 'regenerateLayout'])->name('regenerate');
    });

    // Movies
    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('/', [AdminMovieController::class, 'index'])->name('index');
        Route::get('/create', [AdminMovieController::class, 'create'])->name('create');
        Route::post('/', [AdminMovieController::class, 'store'])->name('store');
        Route::get('/{movie}', [AdminMovieController::class, 'show'])->name('show');
        Route::get('/{movie}/edit', [AdminMovieController::class, 'edit'])->name('edit');
        Route::put('/{movie}', [AdminMovieController::class, 'update'])->name('update');
        Route::patch('/{movie}/featured', [AdminMovieController::class, 'toggleFeatured'])->name('featured');
        Route::post('/{movie}/sync', [AdminMovieController::class, 'syncFromTmdb'])->name('sync');
        Route::delete('/{movie}', [AdminMovieController::class, 'destroy'])->name('destroy');

        // TMDb AJAX
        Route::get('/tmdb/search', [AdminMovieController::class, 'tmdbSearch'])->name('tmdb.search');
        Route::get('/tmdb/browse', [AdminMovieController::class, 'tmdbBrowse'])->name('tmdb.browse');
        Route::get('/tmdb/{tmdbId}', [AdminMovieController::class, 'tmdbDetail'])->name('tmdb.detail');
        Route::post('/tmdb/import', [AdminMovieController::class, 'importFromTmdb'])->name('tmdb.import');
    });

    // Showtime
    Route::prefix('showtimes')->name('showtimes.')->group(function () {
        Route::get('/', [AdminShowtimeController::class, 'index'])->name('index');
        Route::get('/create', [AdminShowtimeController::class, 'create'])->name('create');
        Route::post('/', [AdminShowtimeController::class, 'store'])->name('store');
        Route::post('/bulk', [AdminShowtimeController::class, 'storeBulk'])->name('bulk');
        Route::get('/{showtime}', [AdminShowtimeController::class, 'show'])->name('show');
        Route::get('/{showtime}/edit', [AdminShowtimeController::class, 'edit'])->name('edit');
        Route::put('/{showtime}', [AdminShowtimeController::class, 'update'])->name('update');
        Route::patch('/{showtime}/status', [AdminShowtimeController::class, 'toggleStatus'])->name('status');
        Route::delete('/{showtime}', [AdminShowtimeController::class, 'destroy'])->name('destroy');

        // AJAX
        Route::get('/ajax/studios/{cinema}', [AdminShowtimeController::class, 'getStudios'])->name('ajax.studios');
        Route::post('/ajax/conflict', [AdminShowtimeController::class, 'checkConflictAjax'])->name('ajax.conflict');
    });

    // Monitoring
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/', [AdminMonitoringController::class, 'index'])->name('index');
        Route::get('/realtime', [AdminMonitoringController::class, 'realtime'])->name('realtime');
        Route::get('/showtime/{showtime}/seats', [AdminMonitoringController::class, 'showtimeSeatMap'])->name('seatmap');
    });

    // Reports Bioskop
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AdminReportController::class, 'index'])->name('index');
        Route::get('/export', [AdminReportController::class, 'export'])->name('export');
        Route::get('/chart-data', [AdminReportController::class, 'chartData'])->name('chart');
    });

    // Global Search
    Route::get('/search', AdminSearchController::class)->name('search');
});

// Officer Routes
Route::prefix('officer')->name('officer.')->group(function () {

    // Auth
    Route::get('/login', [OfficerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [OfficerAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [OfficerAuthController::class, 'logout'])->name('logout');

    // Protected
    Route::middleware(['auth', 'officer'])->group(function () {
        Route::get('/dashboard', [OfficerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/scan', [OfficerScanController::class, 'index'])->name('scan');
        Route::post('/scan/validate', [OfficerScanController::class, 'validate'])->name('scan.validate');
        Route::get('/history', [OfficerHistoryController::class, 'index'])->name('history');
        Route::get('/profile',  [OfficerProfileController::class, 'index'])->name('profile');
        Route::patch('/profile',  [OfficerProfileController::class, 'update'])->name('profile.update');
    });
});

require __DIR__ . '/auth.php';
