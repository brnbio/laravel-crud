<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\Traits\HasOptionAttributes;
use Exception;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand as BaseCommand;
use Illuminate\Support\Facades\File;

/**
 * Class GenerateMigrationCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class GenerateMigrationCommand extends BaseCommand
{
    use HasOptionAttributes;

    /**
     * @var string
     */
    protected $signature = 'generate:migration
        {name : The name of the migration}
        {--attributes= : The attributes of the migration}
        {--create=     : The table to be created}
        {--path=       : The location where the migration file should be created}
        {--table=      : The table to migrate}
    ';

    /**
     * @var string
     */
    protected $description = 'Create a new migration file';

    /**
     * @param $name
     * @param $table
     * @param $create
     * @return void
     * @throws Exception
     */
    protected function writeMigration($name, $table, $create): void
    {
        $file = $this->creator->create(
            $name, $this->getMigrationPath(), $table, $create
        );
        File::replaceInFile('{{ fields }}', $this->buildFields(), $file);

        $this->components->info(sprintf('Migration [%s] created successfully.', $file));
    }

    /**
     * @return string
     */
    protected function buildFields(): string
    {
        $fields = [];
        foreach ($this->getAttributes() as $attribute) {
            $fields[] = $this->buildField($attribute);
        }

        return implode("\n\t\t\t", $fields);
    }

    /**
     * @param array $attribute
     * @return string
     */
    protected function buildField(array $attribute): string
    {
        $field = sprintf("\$table->%s('%s')", $attribute['type'], $attribute['name']);
        if ($attribute['nullable']) {
            $field .= '->nullable()';
        }
        if ($attribute['type'] === 'foreignId') {
            $field .= '->constrained()->cascadeOnDelete()';
        }

        return $field . ';';
    }
}
