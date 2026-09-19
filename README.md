
## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:
Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.


## About the project
This Project is in 3 parts.
1. the users panel
2. the professional's panel
3. the admin panel

## Installation

Steps to install the project locally.

Prerequisites
Make sure you have the following installed:

PHP >= 8.2.4
Laravel >= 11.25.0
MySQL or any other database you're using
Composer

the sql file should come with the code
create a database named 'lawconsult' and import the sql file
        or
run 'php artisan migrate' after creating the database

## some debugging tips
if there's an issue with the design or alignment(which there shouldnt), run 'npm install' then 'npm run dev'


## Hostinger deployment

See [docs/hostinger-deployment.md](docs/hostinger-deployment.md) for the production document-root layout, environment configuration, Composer and Artisan commands, permissions, cron, and Paystack checks.

## admin assignment
\App\Models\User::where('id', '1')->update(['is_admin' => '1')]);

## professional assignment
\App\Models\User::where('id', '1')->update(['is_admin' => '2')]);
