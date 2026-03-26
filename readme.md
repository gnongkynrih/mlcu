IDE for mysql
-> phpmyadmin/workbench/Dbeaver
//create database
create database databasename

//to run the server
composer run dev

//to connect laravel with database we need to edit the .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

//to migrate the tables
php artisan migrate

//in laravel Model represents a table in the database
//Model name is singular and table name is plural
//Model name starts with capital letter

//to create a new model with migration file
php artisan make:model ModelName -m
-m here means that it will create the migration file

//to create new livewire component
php artisan make:livewire ComponentName

//routes are mainly in routes/web.php

//we will use MaryUI components for forms
https://mary-ui.com/docs/installation
//install maryui
composer require robsontenorio/mary
php artisan mary:install

//to use toast from maryui
in the layout file, add this line:

<body>...
<x-toast />  
...</body>

//in the model to create the relationship
hasMany
belongsTo
eg in the Category model
public function menuItems()
{
return $this->hasMany(MenuItem::class);
}
eg in the MenuItem model
public function category()
{
return $this->belongsTo(Category::class);
}

//TO USE ROLE BASE ACCESS
https://spatie.be/docs/laravel-permission/v7/introduction

1. Install the package
   composer require spatie/laravel-permission
2. publish the migration
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
3. run the migration
   php artisan migrate
4. Add the HasRoles trait to your User model
   use Spatie\Permission\Traits\HasRoles;

    use HasRoles;

//TO implement middleware
You can register their aliases for easy reference elsewhere in your app:
Open /bootstrap/app.php and register them there:
->withMiddleware(function (Middleware $middleware): void {
$middleware->alias([
'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
})

in routes/web.php
Route::middleware(['auth','role:admin'])->group(function () {
//your routes here
});

A Seeder is a special class that populates (seeds) your database with initial or test data.

TO create a seeder
php artisan make:seeder SeederName
eg php artisan make:seeder RoleAndPermissionSeeder

To see the role and persmisison created check the file database/seeders/RoleAndPermissionSeeder.php

To run the seeder
php artisan db:seed --class=RoleAndPermissionSeeder
