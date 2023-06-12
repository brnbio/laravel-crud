<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
use Brnbio\LaravelCrud\Traits\HasOptionAttributes;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateViewCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class GenerateViewCommand extends GeneratorCommand
{
    use HasOptionAttributes;
    use Macroable;

    /**
     * @var string
     */
    protected $name = 'generate:view';

    /**
     * @var string
     */
    protected $description = 'Create a new view template';

    /**
     * @var string
     */
    protected $type = 'View';

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['attributes', null, InputOption::VALUE_OPTIONAL, 'The attributes of the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the view even if already exists'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a view for the given model'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'The root namespace for the view'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the view file should be created'],
            ['type', null, InputOption::VALUE_OPTIONAL, 'Manually specify the view stub file to use'],
        ];
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        $stub = 'view.stub';
        if ($type = $this->option('type')) {
            $stub = 'view.' . $type . '.stub';
        }

        return $this->resolveStubPath($stub);
    }

    /**
     * @param $name
     * @return string
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return implode('/', [
            base_path($this->option('path') ?: 'resources/views'),
            str_replace('\\', '/', $name) . '.vue',
        ]);
    }

    /**
     * @param string $name
     * @return array
     */
    protected function getReplaceItems(string $name): array
    {
        $replace = parent::getReplaceItems($name);
        $module = '';
        if ($replace['rootNamespace'] !== 'App\\') {
            $module = str($replace['rootNamespace'])->replace('\\', '')->lower()->__toString();
        }

        return array_merge($replace, [
            'module'   => $module ? $module . '.' : '',
            'data'     => $this->buildData($replace['modelVariable']),
            'elements' => $this->buildElements(),
        ]);
    }

    /**
     * @param string $modelVariable
     * @return string
     */
    protected function buildData(string $modelVariable): string
    {
        $data = [];
        foreach ($this->getAttributes() as $attribute) {
            $value = 'null';
            if ($this->option('type') === 'update') {
                $value = 'props.' . $modelVariable . '.' . $attribute['name'];
            }
            $data[] = $attribute['name'] . ': ' . $value . ',';
        }

        return implode(PHP_EOL . '    ', $data);
    }

    /**
     * @return string
     */
    protected function buildElements(): string
    {
        $elements = [];
        foreach ($this->getAttributes() as $attribute) {
            $elements[] = sprintf(
                '<FormControl name="%s" label="%s"%s />',
                $attribute['name'],
                ucfirst($attribute['name']),
                $attribute['nullable'] ? '' : ' required'
            );
        }

        return implode(PHP_EOL . str_repeat(' ', 8), $elements);
    }
}
