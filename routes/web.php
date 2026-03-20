<?php

Route::livewire('/', 'pages::users.index');

Route::livewire('/table-management', 'admin.table-management')->name('admin.table-management');
Route::livewire('/category-management', 'admin.category-management')->name('admin.category-management');
Route::livewire('/menu-management', 'admin.menu-management')->name('admin.menu-management');

