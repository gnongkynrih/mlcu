<?php

Route::livewire('/', 'pages::users.index');

Route::livewire('/table-management', 'admin.table-management')->name('admin.table-management');
