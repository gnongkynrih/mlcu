<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::livewire('/', 'pages::users.index');
Route::livewire('/login','login')->name('login');
Route::get('/logout',function(){
    Auth::logout();
    return redirect()->route('login');
})->name('logout');
//auth means that you have to be logged in to access these routes
Route::middleware(['auth','role:admin'])->group(function () {
    Route::livewire('/table-management', 'admin.table-management')->name('admin.table-management');
    Route::livewire('/category-management', 'admin.category-management')->name('admin.category-management');
    Route::livewire('/menu-management', 'admin.menu-management')->name('admin.menu-management');
});

