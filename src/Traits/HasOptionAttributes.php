<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Traits;

/**
 * Trait HasOptionAttributes
 */
trait HasOptionAttributes
{
    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $attributes = [];
        if ($this->hasOption('attributes') && !empty($this->option('attributes'))) {
            foreach (explode(',', $this->option('attributes') ?? '') as $attribute) {
                $attributes[] = $this->getAttribute($attribute);
            }
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
            'type' => $attribute[1] ?? 'string',
            'attributeType' => $this->getAttributeType($attribute[1] ?? 'string'),
            'nullable' => boolval($attribute[2] ?? false),
        ];
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getAttributeType(string $type): string
    {
        if (str_contains($type, 'integer') || $type === 'foreignId') {
            return 'int';
        }

        if (str_contains($type, 'boolean')) {
            return 'bool';
        }

        return match ($type) {
            'date', 'datetime', 'time' => 'Carbon',
            default => 'string',
        };
    }
}
