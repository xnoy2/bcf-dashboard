<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Checklist;
use App\Support\CardPresenter;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    use AuthorizesWorkspace;

    public function store(Request $request, Card $card)
    {
        $this->cardWorkspace($card);

        $title = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
        ])['title'] ?? 'Checklist';

        $card->checklists()->create([
            'title'    => $title,
            'position' => (int) $card->checklists()->max('position') + 1,
        ]);

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }

    public function destroy(Checklist $checklist)
    {
        $this->checklistWorkspace($checklist);
        $card = $checklist->card;
        $checklist->delete();

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }
}
