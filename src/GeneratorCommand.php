<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud;

use Illuminate\Console\GeneratorCommand as Command;

/**
 * Class GeneratorCommand
 *
 * @package Brnbio\LaravelCrud
 */
abstract class GeneratorCommand extends Command
{
    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $attributes = [];
        foreach (explode(',', $this->option('attributes') ?? '') as $attribute) {
            $attributes[] = $this->getAttribute($attribute);
        }

        return $attributes;
    }

    /**
     * @param mixed $attribute
     * @return array
     */
    protected function getAttribute(string $attribute): array
    {
        $attribute = explode(':', $attribute);

        return [
            'name' => $attribute[0],
            'type' => $this->getAttributeType($attribute[1] ?? 'string'),
            'nullable' => $attribute[2] ?? false,
        ];
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getAttributeType(string $type): string
    {
        return match ($type) {
            'date', 'datetime', 'time' => 'Carbon',
            default => 'string',
        };
    }
}
