@include('laravel-crud::partial/file_header')

/**
 * Class {{ $model }}
 *
 */
class {{ $model }} extends Model
{
@foreach ($fields as $key => $type)
    public const ATTRIBUTE_{{ strtoupper($key) }} = '{{ $key }}';
@endforeach

@foreach ($attributes as $attribute => $value)
    /*
     * @var string
     */
    protected ${{ $attribute }} = '{{ $value }}';
@endforeach

@foreach ($fields as $key => $item)
    @include('laravel-crud::partial/model/getter', ['name' => $key, 'type' => $item['type']])

@if (!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
    @include('laravel-crud::partial/model/setter', ['name' => $key, 'type' => $item['type']])

@endif
@endforeach}
