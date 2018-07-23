<?php
/**
 * laravel-crud.php
 *
 * @copyright   OEMUS MEDIA AG (https://oemus.com)
 * @author      Frank Heider <f.heider@oemus-media.de>
 * @since       23.07.2018
 */

return [
    'skip' => [
        'constants' => [
            'created_at',
            'updated_at',
            'deleted_at',
        ],
        'getter' => [

        ],
        'setter' => [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
        ]
    ]
];