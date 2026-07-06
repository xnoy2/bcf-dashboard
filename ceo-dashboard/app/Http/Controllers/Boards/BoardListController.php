<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardList;
use Illuminate\Http\Request;

class BoardListController extends Controller
{
    use AuthorizesWorkspace;

    public function store(Request $request, Board $board)
    {
        $this->boardWorkspace($board);

        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);

        $list = $board->lists()->create([
            'name'     => $data['name'],
            'position' => (int) $board->lists()->max('position') + 1,
        ]);

        return response()->json(['ok' => true, 'list' => ['id' => $list->id, 'name' => $list->name]]);
    }

    public function update(Request $request, BoardList $list)
    {
        $this->listWorkspace($list);

        $list->update($request->validate(['name' => ['required', 'string', 'max:255']]));

        return response()->json(['ok' => true]);
    }

    public function destroy(BoardList $list)
    {
        $this->listWorkspace($list);
        $list->delete();

        return response()->json(['ok' => true]);
    }

    public function reorder(Request $request, Board $board)
    {
        $this->boardWorkspace($board);

        $order = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ])['order'];

        foreach (array_values($order) as $position => $id) {
            // Scope to this board so a forged id can't move another board's list.
            $board->lists()->whereKey($id)->update(['position' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}
