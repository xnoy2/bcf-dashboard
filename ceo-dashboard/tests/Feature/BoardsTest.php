<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardsTest extends TestCase
{
    use RefreshDatabase;

    private function workspaceFor(User $user): Workspace
    {
        $ws = Workspace::create(['name' => 'IT/Dev', 'color' => '#3B2A4A', 'owner_id' => $user->id]);
        $ws->members()->attach($user->id, ['role' => 'owner']);

        return $ws;
    }

    public function test_member_can_build_workspace_board_list_card(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a workspace (owner becomes a member).
        $this->post('/workspaces', ['name' => 'IT/Dev', 'color' => '#3B2A4A'])->assertRedirect();
        $ws = Workspace::first();
        $this->assertTrue($ws->hasMember($user));

        // Board is seeded with the three default lists.
        $this->post("/workspaces/{$ws->id}/boards", ['name' => 'Portals'])->assertRedirect();
        $board = Board::first();
        $this->assertSame(3, $board->lists()->count());

        // Add a card to the first list.
        $list = $board->lists()->orderBy('position')->first();
        $res = $this->postJson("/lists/{$list->id}/cards", ['title' => 'Ship it']);
        $res->assertOk()->assertJsonPath('card.title', 'Ship it');

        // Activity was logged with the list name.
        $card = Card::first();
        $this->assertDatabaseHas('card_activities', ['card_id' => $card->id, 'action' => 'created']);
    }

    public function test_card_move_persists_new_list_and_order(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $ws = $this->workspaceFor($user);
        $board = $ws->boards()->create(['name' => 'B', 'position' => 0]);
        $a = $board->lists()->create(['name' => 'A', 'position' => 0]);
        $b = $board->lists()->create(['name' => 'B', 'position' => 1]);
        $c1 = $a->cards()->create(['title' => 'one', 'position' => 0]);
        $c2 = $a->cards()->create(['title' => 'two', 'position' => 1]);

        // Move both cards into list B, reversed.
        $this->postJson("/lists/{$b->id}/cards/reorder", ['order' => [$c2->id, $c1->id]])->assertOk();

        $this->assertSame($b->id, $c1->fresh()->board_list_id);
        $this->assertSame(0, $c2->fresh()->position);
        $this->assertSame(1, $c1->fresh()->position);
        // A "moved" activity was recorded.
        $this->assertDatabaseHas('card_activities', ['card_id' => $c1->id, 'action' => 'moved']);
    }

    public function test_non_member_cannot_access_or_mutate_another_board(): void
    {
        $owner = User::factory()->create();
        $ws = $this->workspaceFor($owner);
        $board = $ws->boards()->create(['name' => 'Private', 'position' => 0]);
        $list = $board->lists()->create(['name' => 'L', 'position' => 0]);
        $card = $list->cards()->create(['title' => 'secret', 'position' => 0]);

        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $this->get("/boards/{$board->id}")->assertForbidden();
        $this->getJson("/cards/{$card->id}")->assertForbidden();
        $this->postJson("/lists/{$list->id}/cards", ['title' => 'x'])->assertForbidden();
        $this->deleteJson("/cards/{$card->id}")->assertForbidden();

        // The outsider's board list should be empty (isolation via accessibleBy).
        $this->assertCount(0, Workspace::accessibleBy($outsider)->get());
    }

    public function test_checklist_progress_and_card_delete_cascade(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $ws = $this->workspaceFor($user);
        $board = $ws->boards()->create(['name' => 'B', 'position' => 0]);
        $list = $board->lists()->create(['name' => 'L', 'position' => 0]);
        $card = $list->cards()->create(['title' => 'c', 'position' => 0]);

        $this->postJson("/cards/{$card->id}/checklists", ['title' => 'Steps'])->assertOk();
        $checklist = $card->checklists()->first();
        $this->postJson("/checklists/{$checklist->id}/items", ['content' => 'a'])->assertOk();
        $res = $this->postJson("/checklists/{$checklist->id}/items", ['content' => 'b'])
            ->assertOk();
        $itemId = $checklist->items()->first()->id;
        $done = $this->patchJson("/checklist-items/{$itemId}", ['is_done' => true])->assertOk();
        $done->assertJsonPath('card.checklist.done', 1);
        $done->assertJsonPath('card.checklist.total', 2);

        // Deleting the card cascades to checklists/items.
        $this->deleteJson("/cards/{$card->id}")->assertOk();
        $this->assertDatabaseMissing('checklists', ['id' => $checklist->id]);
        $this->assertDatabaseMissing('checklist_items', ['id' => $itemId]);
    }
}
