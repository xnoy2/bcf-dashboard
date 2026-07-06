<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardComment;
use App\Support\CardPresenter;
use Illuminate\Http\Request;

class CardCommentController extends Controller
{
    use AuthorizesWorkspace;

    public function store(Request $request, Card $card)
    {
        $this->cardWorkspace($card);

        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $card->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $data['body'],
        ]);

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }

    public function destroy(Request $request, CardComment $comment)
    {
        $workspace = $this->commentWorkspace($comment);

        // Only the author or the workspace owner may delete a comment.
        abort_unless(
            $comment->user_id === $request->user()->id || $workspace->owner_id === $request->user()->id,
            403
        );

        $card = $comment->card;
        $comment->delete();

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }
}
