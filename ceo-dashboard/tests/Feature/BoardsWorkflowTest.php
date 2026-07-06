<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Card;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Broad workflow + authorization coverage for the Boards feature.
 */
class BoardsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function seedCard(User $owner): Card
    {
        $ws = Workspace::create(['name' => 'WS', 'color' => '#3B2A4A', 'owner_id' => $owner->id]);
        $ws->members()->attach($owner->id, ['role' => 'owner']);
        $board = $ws->boards()->create(['name' => 'B', 'position' => 0]);
        $list = $board->lists()->create(['name' => 'L', 'position' => 0]);

        return $list->cards()->create(['title' => 'card', 'position' => 0]);
    }

    public function test_labels_create_toggle_and_reject_cross_board(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $card = $this->seedCard($user);
        $board = $card->list->board;

        // Create a label on the board and attach it to the card.
        $label = $this->postJson("/boards/{$board->id}/labels", ['name' => 'Urgent', 'color' => '#B5495B'])
            ->assertOk()->json('label');
        $this->postJson("/cards/{$card->id}/labels", ['label_id' => $label['id']])
            ->assertOk()->assertJsonPath('card.labels.0.id', $label['id']);

        // Toggling again detaches it.
        $this->postJson("/cards/{$card->id}/labels", ['label_id' => $label['id']])
            ->assertOk()->assertJsonCount(0, 'card.labels');

        // A label from a different board cannot be attached.
        $otherBoard = $card->list->board->workspace->boards()->create(['name' => 'B2', 'position' => 1]);
        $otherLabel = $otherBoard->labels()->create(['color' => '#000000']);
        $this->postJson("/cards/{$card->id}/labels", ['label_id' => $otherLabel->id])->assertStatus(422);
    }

    public function test_any_dashboard_user_can_be_assigned_to_a_card(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create(); // not a workspace member
        $this->actingAs($owner);
        $card = $this->seedCard($owner);

        // board_members lists ALL users (both).
        $this->getJson("/cards/{$card->id}")->assertOk()->assertJsonCount(2, 'card.board_members');

        // The non-member user can still be assigned.
        $this->postJson("/cards/{$card->id}/members", ['user_id' => $outsider->id])
            ->assertOk()->assertJsonPath('card.members.0.id', $outsider->id);
    }

    public function test_checklist_progress_and_hide_and_complete_toggle(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $card = $this->seedCard($user);

        $cl = $this->postJson("/cards/{$card->id}/checklists", ['title' => 'Steps'])->assertOk()
            ->json('card.checklists.0');
        $this->postJson("/checklists/{$cl['id']}/items", ['content' => 'a'])->assertOk();
        $this->postJson("/checklists/{$cl['id']}/items", ['content' => 'b'])->assertOk();
        $itemId = \App\Models\Checklist::find($cl['id'])->items()->first()->id;
        $this->patchJson("/checklist-items/{$itemId}", ['is_done' => true])
            ->assertOk()->assertJsonPath('card.checklist.done', 1)->assertJsonPath('card.checklist.total', 2);

        // Complete toggle sets/clears completed_at.
        $this->patchJson("/cards/{$card->id}", ['completed' => true])->assertOk()->assertJsonPath('card.completed', true);
        $this->assertNotNull($card->fresh()->completed_at);
        $this->patchJson("/cards/{$card->id}", ['completed' => false])->assertOk()->assertJsonPath('card.completed', false);
        $this->assertNull($card->fresh()->completed_at);
    }

    public function test_card_dates_set_and_clear(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $card = $this->seedCard($user);

        $this->patchJson("/cards/{$card->id}", ['start_date' => '2026-07-06', 'due_date' => '2026-07-08T17:30'])
            ->assertOk()->assertJsonPath('card.start', '2026-07-06');
        $this->assertNotNull($card->fresh()->due_date);

        $this->patchJson("/cards/{$card->id}", ['start_date' => null, 'due_date' => null])->assertOk();
        $this->assertNull($card->fresh()->start_date);
        $this->assertNull($card->fresh()->due_date);
    }

    public function test_list_reorder_persists_positions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $card = $this->seedCard($user);
        $board = $card->list->board;
        $l2 = $board->lists()->create(['name' => 'L2', 'position' => 1]);
        $l3 = $board->lists()->create(['name' => 'L3', 'position' => 2]);

        $this->postJson("/boards/{$board->id}/lists/reorder", ['order' => [$l3->id, $l2->id, $card->board_list_id]])
            ->assertOk();
        $this->assertSame(0, $l3->fresh()->position);
        $this->assertSame(1, $l2->fresh()->position);
    }

    public function test_comment_delete_restricted_to_author_or_owner(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $card = $this->seedCard($owner);
        $card->list->board->workspace->members()->attach($member->id, ['role' => 'member']);

        // Member posts a comment.
        $this->actingAs($member);
        $comment = $card->comments()->create(['user_id' => $member->id, 'body' => 'hi']);

        // A different member (also in the workspace) cannot delete it.
        $member2 = User::factory()->create();
        $card->list->board->workspace->members()->attach($member2->id, ['role' => 'member']);
        $this->actingAs($member2)->deleteJson("/comments/{$comment->id}")->assertForbidden();

        // The owner can delete any comment.
        $this->actingAs($owner)->deleteJson("/comments/{$comment->id}")->assertOk();
        $this->assertDatabaseMissing('card_comments', ['id' => $comment->id]);
    }

    public function test_non_owner_cannot_manage_members_or_delete_workspace(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $card = $this->seedCard($owner);
        $ws = $card->list->board->workspace;
        $ws->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($member);
        $this->post("/workspaces/{$ws->id}/members", ['email' => 'x@y.com'])->assertForbidden();
        $this->delete("/workspaces/{$ws->id}/members/{$owner->id}")->assertForbidden();
        $this->delete("/workspaces/{$ws->id}")->assertForbidden();
    }

    public function test_deleting_a_card_removes_its_attachment_files(): void
    {
        Storage::fake('boards');
        $user = User::factory()->create();
        $this->actingAs($user);
        $card = $this->seedCard($user);

        $path = "card-{$card->id}/f.txt";
        Storage::disk('boards')->put($path, 'x');
        $card->attachments()->create(['disk' => 'boards', 'path' => $path, 'original_name' => 'f.txt', 'size' => 1]);

        $this->deleteJson("/cards/{$card->id}")->assertOk();
        Storage::disk('boards')->assertMissing($path);
    }

    public function test_deleting_a_workspace_purges_attachment_files(): void
    {
        Storage::fake('boards');
        $user = User::factory()->create();
        $this->actingAs($user);
        $card = $this->seedCard($user);
        $ws = $card->list->board->workspace;

        Storage::disk('boards')->put('p.txt', 'x');
        $card->attachments()->create(['disk' => 'boards', 'path' => 'p.txt', 'original_name' => 'p.txt', 'size' => 1]);

        $this->delete("/workspaces/{$ws->id}")->assertRedirect();
        Storage::disk('boards')->assertMissing('p.txt');
    }

    public function test_outsider_is_forbidden_on_every_write_endpoint(): void
    {
        $owner = User::factory()->create();
        $card = $this->seedCard($owner);
        $board = $card->list->board;
        $list = $card->list;
        $label = $board->labels()->create(['color' => '#000']);
        $checklist = $card->checklists()->create(['title' => 'c']);

        $this->actingAs(User::factory()->create()); // outsider

        $this->getJson("/boards/{$board->id}")->assertForbidden();
        $this->postJson("/boards/{$board->id}/lists", ['name' => 'x'])->assertForbidden();
        $this->postJson("/boards/{$board->id}/labels", ['color' => '#fff'])->assertForbidden();
        $this->postJson("/lists/{$list->id}/cards", ['title' => 'x'])->assertForbidden();
        $this->postJson("/lists/{$list->id}/duplicate")->assertForbidden();
        $this->getJson("/cards/{$card->id}")->assertForbidden();
        $this->patchJson("/cards/{$card->id}", ['title' => 'x'])->assertForbidden();
        $this->postJson("/cards/{$card->id}/labels", ['label_id' => $label->id])->assertForbidden();
        $this->postJson("/cards/{$card->id}/checklists", [])->assertForbidden();
        $this->postJson("/checklists/{$checklist->id}/items", ['content' => 'x'])->assertForbidden();
        $this->postJson("/cards/{$card->id}/comments", ['body' => 'x'])->assertForbidden();
        $this->patchJson("/labels/{$label->id}", ['color' => '#fff'])->assertForbidden();
    }
}
