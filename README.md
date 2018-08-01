# laravel-crud
CRUD generator for Laravel 5

## Requirements

- PHP 7.1 (TypeHints are used)
- Laravel 5.6
- Active DB connection (Model attributes are retrieved from the database table)

## Installation

```
composer require brnbio/laravel-crud
```

## Usage

Generate whole MVC
```
php artisan crud:all Group
```

Generate single part of MWV
```
php artisan crud:model Group
php artisan crud:controller Groups
```
