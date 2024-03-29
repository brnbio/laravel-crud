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

In addition to the basic replacements, the following replacements are available for the request:

- `{{ table }}` - Table name (if set by option)
- `{{ attributes }}` - List of the attribute constants
- `{{ fillable }}` - List of the fillable attributes
- `{{ properties }}` - List of the properties based on the attributes for the doc block

```bash
php artisan generate:model --table teams --attributes=name:string Team
```
```php
// generated fields

/**
 * Class Team
 *
 * @package App\Models
 * @property string $name
 */
class Team extends Model
{
    public const TABLE = 'teams';
    public const ATTRIBUTE_NAME = 'name';

    /**
     * @var string[]
     */
    protected $fillable = [
        self::ATTRIBUTE_NAME,
    ];
}
```

#### Generate migration

In addition to the basic replacements, the following replacements are available for the request:

- `{{ fields }}` - List of table fields based on the attributes

```bash
php artisan generate:migration --create teams --attributes=name:string CreateTeamsTable
```
```php
// generated fields

Schema::create('teams', function (Blueprint $table) {
    // ...
    $table->string('name');
    // ...
});
```

#### Generate controller

In addition to the basic replacements, the following replacements are available for the request:

- `{{ storeRequest }}` - Store request class
- `{{ namespacedStoreRequest }}` - Store request class with namespace
- `{{ updateRequest }}` - Update request class
- `{{ namespacedUpdateRequest }}` - Update request class with namespace

```bash
php artisan generate:controller --model=Team --type=create Teams/CreateController
```

```php
// generated controller

class CreateController extends Controller
{
    /**
     * @return Response
     */
    public function __invoke(): Response
    {
        return inertia('teams/create');
    }

    /**
     * @param StoreRequest $request
     * @return RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $team = Team::create($request->validated());

        return to_route('teams.details', compact('team'));
    }
}
```

#### Generate view

```bash
php artisan generate:view --model Team --type create teams/create
```

```php
// generated view

<script setup>

import { useForm } from '@inertiajs/inertia-vue3';
import { provide } from 'vue';

const form = useForm({
    //
});
provide('form');

function submit() {
    form.submit(route('teams.create'));
}

</script>
<template>

    <form @submit.prevent="submit">
        // ...
        <button :disabled="form.processing">
            Submit
        </button>
    </form>

</template>
```

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
