<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
use Brnbio\LaravelCrud\Traits\HasOptionAttributes;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateMigrationCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class GenerateModelCommand extends GeneratorCommand
{
    use HasOptionAttributes;

    /**
     * @var string
     */
    protected $name = 'code:generate:model';

    /**
     * @var string
     */
    protected $description = 'Create a new eloquent model class';

    /**
     * @var string
     */
    protected $type = 'Model';

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['attributes', null, InputOption::VALUE_OPTIONAL, 'The attributes of the model'],
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'The root namespace for the model'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the model file should be created'],
            ['seed', 's', InputOption::VALUE_NONE, 'Create a new seeder for the model'],
            ['table', 't', InputOption::VALUE_OPTIONAL, 'The table to be created'],
        ];
    }

    /**
     * @return false|void
     * @throws FileNotFoundException
     */
    public function handle()
    {
        if (parent::handle() === false && !$this->option('force')) {
            return false;
        }

//        if ($this->option('factory')) {
//            $this->createFactory();
//        }

        if ($this->option('migration')) {
            $this->createMigration();
        }

//        if ($this->option('seed')) {
//            $this->createSeeder();
//        }
    }

    /**
     * TODO:
     * @return void
     */
    protected function createFactory(): void
    {
//        $factory = Str::studly($this->argument('name'));
//        $this->call('make:factory', [
//            'name' => "{$factory}Factory",
//            '--model' => $this->qualifyClass($this->getNameInput()),
//        ]);
    }

    /**
     * @return void
     */
    protected function createMigration(): void
    {
        $table = $this->option('table');
        $this->call('code:generate:migration', [
            'name'         => "create_{$table}_table",
            '--create'     => $table,
            '--attributes' => $this->option('attributes'),
            '--path'       => $this->option('path') . '/database/migrations',
        ]);
    }

    /**
     * TODO:
     * @return void
     */
    protected function createSeeder(): void
    {
//        $seeder = Str::studly(class_basename($this->argument('name')));
//        $this->call('make:seeder', [
//            'name' => "{$seeder}Seeder",
//        ]);
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('model.stub');
    }

    /**
     * @param $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Models';
    }

    /**
     * @param string $name
     * @return array
     */
    protected function getReplaceItems(string $name): array
    {
        return array_merge(parent::getReplaceItems($name), [
            'attributes' => $this->buildAttributes(),
            'fillable'   => $this->buildFillable(),
            'properties' => $this->buildProperties(),
            'table'      => $this->option('table') ?: '',
        ]);
    }

    /**
     * @return string
     */
    protected function buildProperties(): string
    {
        $properties = [];

        foreach ($this->getAttributes() as $attribute) {
            $properties[] = ' * @property ' . $attribute['attributeType'] . ($attribute['nullable'] ? '|null' : '') . ' $' . $attribute['name'];
            if ($attribute['type'] === 'foreignId') {
                $foreign = str($attribute['name'])->replace('_id', '')->camel()->__toString();
                $properties[] = ' * @property ' . ucfirst($foreign) . ($attribute['nullable'] ? '|null' : '') . ' $' . $foreign;
            }
        }

        return implode(PHP_EOL, $properties);
    }

    /**
     * @return string
     */
    private function buildAttributes(): string
    {
        $attributes = [];
        foreach ($this->getAttributes() as $attribute) {
            $attributes[] = 'public const ATTRIBUTE_' . strtoupper($attribute['name']) . ' = \'' . $attribute['name'] . '\';';
        }

        return implode(PHP_EOL . "    ", $attributes);
    }

    /**
     * @return string
     */
    private function buildFillable(): string
    {
        $fillable = [];
        foreach ($this->getAttributes() as $attribute) {
            $fillable[] = 'self::ATTRIBUTE_' . strtoupper($attribute['name']) . ',';
        }

        return implode(PHP_EOL . "        ", $fillable);
    }
}
