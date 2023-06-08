# Code generation for Laravel

This lib helps to generate code for Laravel projects.

### Installation

```bash
composer require --dev brnbio/laravel-crud
```

If you want your own templates, make sure you publish the stubs to your application.

```bash
php artisan stub:publish
```

### Macroable

If you want to add your own replacements, you can easy update the replacements.
All basic replacements, command arguments and options are available.

```php
// AppServiceProvider.php

GenerateRequestCommand::macro('updateReplace', function (array $replace, array $arguments, array $options) {
    return array_merge($replace, [
        // your replacements
    ]);
});
```

### Basic replacements

- `{{ namespace }}` - Namespace for generated class
- `{{ rootNamespace }}` - Base namespace
- `{{ class }}` - Class name
- `{{ namespacedModel }}` - Namespaced model class
- `{{ model }}` - Name of the model
- `{{ modelVariable }}` - Variable name for the model
- `{{ modelVariablePlural }}` - Variable name for the model in plural

```php
// e.g. for a Team model

$replace = [
    'namespace' => 'App\Models',
    'rootNamespace' => 'App',
    'class' => 'Team',
    'namespacedModel' => 'App\Models\Team',
    'model' => 'Team',
    'modelVariable' => 'team',
    'modelVariablePlural' => 'teams',
];
```

### Usage

Generate everything for model `Team`: Model, Controller, Views, Requests and Migration.

```bash
php artisan generate --table=teams --attributes=name:string Team
```
If you want to do it manually, you can use the basic commands to generate.

#### Generate model

tbd

#### Generate migration

tbd

#### Generate controller

tbd

#### Generate view

tbd

#### Generate request

In addition to the basic replacements, the following replacements are available for the request:

- `{{ rules }}` - List of rules based on the attributes

```bash
php artisan generate:request --model Team --attributes=name:string Teams/StoreRequest
```
```php
// generated rules

public function rules(): array
{
    return [
        Team::ATTRIBUTE_NAME => [
            'required',
            'string',
            'max:255',
        ],
    ];
}
```
