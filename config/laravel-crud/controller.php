<?php

return ['${FILE_HEADER}

namespace App\Http\Controllers;

${USE}

/**
 * Class ${CONTROLLER}
 * 
 */
class ${CONTROLLER} extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        $${VAR}s = ${MODEL}::query()
            ->paginate();

        return view()->make(\'${VAR}s.index\', [
              \'${VAR}s\' => $${VAR}s,
        ]);
    }

    /**
     * @param ${MODEL} $${var}
     * @return View
     */
    public function view(${MODEL} $${var}): View
    {
        return view()->make(\'${VAR}s.view\', [
              \'${VAR}\' => $${VAR},
        ]);
    }

    /**
     * @return View
     */
    public function create(): View
    {
        return view()->make(\'${VAR}s.create\');
    }

    /**
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        $${VAR} = new ${MODEL}();
        
        return $this->save($${VAR});
    }

    /**
     * @param ${MODEL} $${VAR}
     * @return View
     */
    public function edit(${MODEL} $${VAR}): View
    {
        return view()->make(\'${VAR}s.edit\', [
            \'${VAR}\' => $${VAR},
        ]);
    }

    /**
     * @param ${MODEL} $${VAR}
     * @return RedirectResponse
     */
    public function update(${MODEL} $${VAR}): RedirectResponse
    {
        return $this->save($${VAR});
    }

    /**
     * @param ${MODEL} $${VAR}
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(${MODEL} $${VAR}): RedirectResponse
    {
        if ($${VAR}->delete()) {
            flash()->success(\'${MODEL} wurde gelöscht.\');
        } else {
            flash()->error(\'Es ist ein Fehler aufgetreten.\');
        }

        return redirect()->route(\'${VAR}s.index\');
    }
    
    /**
     * @param ${MODEL} $${VAR}
     * @return RedirectResponse
     */
    private function save(${MODEL} $${VAR}): RedirectResponse
    {
        if ($${VAR}->save()) {
            flash()->success(\'${MODEL} wurde gelöscht.\');
        } else {
            flash()->error(\'Es ist ein Fehler aufgetreten.\');
        }

        return redirect()->route(\'${VAR}s.index\');
    }    
}
'];