<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    use AuthorizesWorkspace;

    public function store(Request $request, Board $board)
    {
        $this->boardWorkspace($board);

        $data = $request->validate([
            'name'  => ['nullable', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:9'],
        ]);

        $label = $board->labels()->create($data);

        return response()->json(['ok' => true, 'label' => [
            'id' => $label->id, 'name' => $label->name, 'color' => $label->color,
        ]]);
    }

    public function update(Request $request, Label $label)
    {
        $this->labelWorkspace($label);

        $label->update($request->validate([
            'name'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'max:9'],
        ]));

        return response()->json(['ok' => true]);
    }

    public function destroy(Label $label)
    {
        $this->labelWorkspace($label);
        $label->delete();

        return response()->json(['ok' => true]);
    }
}
