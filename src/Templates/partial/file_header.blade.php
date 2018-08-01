
/**
 * {{ $filename }}
 *
 * @copyright   brnb.io (https://brnb.io)
 * @author      Frank Heider <info@brnb.io>
 * @since       {{ date('Y-m-d') }}
 */

declare(strict_types=1);

namespace {{ $namespace }};

@if (!empty($use))
@php
    $use = array_unique($use);
    asort($use);
@endphp
use {{ implode(";\nuse ", $use) }};
@endif