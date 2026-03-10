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
