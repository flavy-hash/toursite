<?php

use App\Http\Controllers\FaqQuestionController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\UnsubscribeController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

// Content comes from the `pages` table, but the URLs are fixed here — staff
// edit these pages in the panel rather than inventing new routes. Keep the
// slugs in step with Page::ROUTES.
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/about/team', [PageController::class, 'team'])->name('about.team');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::post('/faq/ask', [FaqQuestionController::class, 'store'])->name('faq.ask');

Route::get('/tours', [TourController::class, 'index'])->name('tours.index');

Route::get('/tours/{slug}', [TourController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('tours.show');

Route::get('/inquiry', [InquiryController::class, 'create'])->name('inquiry.create');
Route::post('/inquiry', [InquiryController::class, 'store'])->name('inquiry.store');

Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

Route::post('/subscribe', [SubscriberController::class, 'store'])->name('subscribe');

Route::get('/unsubscribe/{subscriber}', UnsubscribeController::class)
    ->name('unsubscribe')
    ->middleware('signed');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
