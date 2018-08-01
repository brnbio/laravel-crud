@include('laravel-crud::partial/file_header')

/**
 * Class {{ $controller }}
 *
 */
class {{ $controller }} extends Controller
{
@if (in_array('index', $actions))
    /**
     * @return View
     */
    public function index(): View
    {
        ${{ $vars }} = {{ $model }}::query()->paginate();

        return view()->make('{{ $modulePrefix }}{{ $vars }}.index', [
            '{{ $vars }}' => ${{ $vars }},
        ]);
    }

@endif
@if (in_array('view', $actions))
    /**
     * @param {{ $model }} ${{ $var }}
     * @return View
     */
    public function view({{ $model }} ${{ $var }}): View
    {
        return view()->make('{{ $modulePrefix }}{{ $vars }}.view', [
            '{{ $var }}' => ${{ $var }},
        ]);
    }

@endif
@if (in_array('add', $actions))
    /**
     * @return View
     */
    public function create(): View
    {
        return view()->make('{{ $modulePrefix }}{{ $vars }}.create');
    }

    /**
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        ${{ $var }} = new {{ $model }}();

        return $this->save(${{ $var }});
    }

@endif
@if (in_array('edit', $actions))
    /**
     * @param {{ $model }} ${{ $var }}
     * @return View
     */
    public function edit({{ $model }} ${{ $var }}): View
    {
        return view()->make('{{ $vars }}.edit', [
            '{{ $var }}' => ${{ $var }},
        ]);
    }

    /**
     * @param {{ $model }} ${{ $var }}
     * @return RedirectResponse
     */
    public function update({{ $model }} ${{ $var }}): RedirectResponse
    {
        return $this->save(${{ $var }});
    }

@endif
@if (in_array('delete', $actions))
    /**
     * @param {{ $model }} ${{ $var }}
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete({{ $model }} ${{ $var }}): RedirectResponse
    {
        if (${{ $var }}->delete()) {
            flash()->success( __('{{ $model }} has been deleted.'));
        } else {
            flash()->error( __('{{ $model }} could not been deleted. Please, try again.'));
        }

        return redirect()->back();
    }

@endif
@if (array_intersect(['add', 'edit'], $actions))
    /**
     * @param {{ $model }} ${{ $var }}
     * @return RedirectResponse
     */
    private function save({{ $model }} ${{ $var }}): RedirectResponse
    {
        if (${{ $var }}->save()) {
            flash()->success( __('{{ $model }} has been saved.'));
        } else {
            flash()->error( __('{{ $model }} could not been saved. Please, try again.'));
        }

        return redirect()->route('{{ $modulePrefix }}{{ $vars }}.index');
    }
@endif
}
