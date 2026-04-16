<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::livewire('/', 'test-pdf')->middleware('auth'); //needs authentication
Route::livewire('/login','login')->name('login');
Route::get('/logout',function(){
    Auth::logout();
    return redirect()->route('login');
})->name('logout');
//auth means that you have to be logged in to access these routes
Route::middleware(['auth','role:admin'])->group(function () {
    Route::livewire('/table-management', 'admin.table-management')->name('admin.table-management');
    Route::livewire('/category-management', 'admin.category-management')->name('admin.category-management');
    Route::livewire('/sales-report', 'reports.sales-report')->name('reports.sales-report');
    
    
});
Route::middleware(['auth','permission:cashier'])->group(function () {
    Route::livewire('/menu-management', 'admin.menu-management')->name('admin.menu-management');
    Route::livewire('/checkout', 'checkout')->name('pos.checkout');
});
Route::middleware(['auth','role:admin'])->group(function () {
    Route::livewire('/user-management', 'admin.user-management')->name('admin.user-management');
});
Route::middleware(['auth','role:waiter'])->group(function () {
    Route::livewire('/table-selection', 'table-selection')->name('pos.table-selection');
    Route::livewire('/food-selection', 'food-selection')->name('pos.food-selection');
});
