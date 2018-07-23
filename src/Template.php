<?php

/**
 * Template.php
 *
 * @copyright   OEMUS MEDIA AG (https://oemus.com)
 * @author      Frank Heider <f.heider@oemus-media.de>
 * @since       23.07.2018
 */

declare(strict_types=1);

namespace Brnbio\LaravelCrud;

use Carbon\Carbon;

/**
 * Class TemplateRenderer
 *
 * @package Brnbio
 * @subpackage Brnbio\LaravelCrud
 */
class Template
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $template;

    /**
     * TemplateRenderer constructor.
     * @param string $template
     * @param array $data
     */
    public function __construct(string $template, array $data = [])
    {
        $this->data = $data;
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $template = config($this->template, [])[0];
        $this->data['file_header'] = $this->renderVars(
            config('laravel-crud.templates.file-header')[0],
            array_merge($this->data, [
                'date' => Carbon::now()->format('Y-m-d'),
            ])
        );

        return $this->renderVars($template, $this->data);
    }

    /**
     * @param string $template
     * @param array $vars
     * @return mixed
     */
    private function renderVars(string $template, array $data)
    {
        preg_match_all('/\$\{([A-Z_]*)\}/is', $template, $templateVars);

        return str_replace(
            $templateVars[0],
            array_map(function ($item) use ($data) {
                return $data[strtolower($item)] ?? '';
            }, $templateVars[1]),
            $template
        );
    }

}
