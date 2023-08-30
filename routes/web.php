<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuditTrailController;


use App\Http\Livewire\TableFilter;
use Illuminate\Support\Facades\Route;
use App\Models\Mannequin;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
})->name('auth.login')->middleware('guest');

Route::middleware(['auth', 'admin-access'])->group(function () {
    // Only Admin 1 can access these routes(and owner i guess)
    //Trashcan
    Route::get('/collection-trash', [CollectionController::class, 'trashcan'])->name('collection.trashcan');//view trashcan view
    Route::post('/collection/{id}', [CollectionController::class, 'trash'])->name('collection.trash');//change activeStatus of product/mannequin to 0
    Route::get('collection/{id}', [CollectionController::class, 'restore'])->name('collection.restore');//change activeStatus of product/mannequin back to 1
    Route::get('collection-delete/{id}', [CollectionController::class, 'destroy'])->name('collection.delete');//permanent delete
    Route::post('/collection/trash-multiple', [CollectionController::class, 'trashMultiple'])->name('collection.trashMultiple');

    //users
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::post('/users-add', [UsersController::class, 'store'])->name('users.add');
    Route::post('/users-trash/{id}', [UsersController::class, 'trash'])->name('users.trash');
    Route::post('/users-restore/{id}', [UsersController::class, 'restore'])->name('users.restore');
    // Company
    Route::get('/company', [CompanyController::class, 'index'])->name('company');
    Route::post('/add-company', [CompanyController::class, 'company'])->name('company.add');
    //Audit trail
    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail');
});

Route::middleware(['auth', 'can:viewer-access'])->group(function () {
    // Only Viewer can access these routes
    // Route::get('/collection', [CollectionController::class, 'index'])->name('collection');
    // Route::get('/collection-view/{id}', [CollectionController::class, 'view'])->name('collection.view_prod');
    //  Route::get('/company', [CompanyController::class, 'index'])->name('company');
    // ... other routes for Viewer
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Collection/Product
    Route::get('/collection', [CollectionController::class, 'index'])->name('collection');
    Route::get('/collection-view/{encryptedId}', [CollectionController::class, 'view'])->name('collection.view_prod');
    Route::get('/collection-add', [CollectionController::class, 'add'])->name('collection.add');//go to add view
    Route::post('/remove-image', [CollectionController::class, 'remove'])->name('remove-image');//image removal in add
    Route::put('/collection-store', [CollectionController::class, 'store'])->name('collection.store');//add product
    Route::get('/collection-edit/{id}', [CollectionController::class, 'edit'])->name('collection.edit');//go
    Route::put('/collection-update/{id}', [CollectionController::class, 'update'])->name('collection.update');

    // Category
    Route::get('/collection-category', [CollectionController::class, 'category'])->name('collection.category');
    Route::post('/collection-category', [CollectionController::class, 'store_category'])->name('collection.category.store');
    Route::post('/collection-category-trash/{id}', [CollectionController::class, 'trash_category'])->name('collection.category.trash');

    //Type
    Route::get('/collection-type', [CollectionController::class, 'type'])->name('collection.type');
    Route::post('/collection-type', [CollectionController::class, 'store_type'])->name('collection.type.store');

    // // Company
    // Route::get('/company', [CompanyController::class, 'index'])->name('company');
    // Route::post('/add-company', [CompanyController::class, 'company'])->name('company.add');

    //Users
    // Route::get('/users', [UsersController::class, 'index'])->name('users');
    // Route::post('/users-add', [UsersController::class, 'store'])->name('users.add');

    //Audit trail
    // Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail');

});

require __DIR__.'/auth.php';
