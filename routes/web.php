<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');


Route::middleware('auth')->group(function () {
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
});

Route::middleware('auth',AuthAdmin::class)->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/brands', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('/admin/brand/add', [AdminController::class, 'add_brand'])->name('admin.add_brand');
    Route::post('/admin/brand/store', [AdminController::class, 'store_brand'])->name('admin.store_brand');
    Route::get('/admin/brand/edit/{id}', [AdminController::class, 'edit_brand'])->name('admin.edit_brand');
    Route::put('/admin/brand/update/{id}', [AdminController::class, 'update_brand'])->name('admin.update_brand');
    Route::delete('/admin/brand/delete/{id}', [AdminController::class, 'delete_brand'])->name('admin.delete_brand');
});
