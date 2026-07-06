<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Support\CardPresenter;
use Illuminate\Http\Request;

class ChecklistItemController extends Controller
{
    use AuthorizesWorkspace;

    public function store(Request $request, Checklist $checklist)
    {
        $this->checklistWorkspace($checklist);

        $data = $request->validate(['content' => ['required', 'string', 'max:1000']]);

        $checklist->items()->create([
            'content'  => $data['content'],
            'position' => (int) $checklist->items()->max('position') + 1,
        ]);

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($checklist->card->fresh())]);
    }

    public function update(Request $request, ChecklistItem $item)
    {
        $this->checklistItemWorkspace($item);

        $item->update($request->validate([
            'content' => ['sometimes', 'string', 'max:1000'],
            'is_done' => ['sometimes', 'boolean'],
        ]));

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($item->checklist->card->fresh())]);
    }

    public function destroy(ChecklistItem $item)
    {
        $this->checklistItemWorkspace($item);
        $card = $item->checklist->card;
        $item->delete();

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }
}
