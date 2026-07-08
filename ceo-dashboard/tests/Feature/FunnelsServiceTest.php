<?php

namespace Tests\Feature;

use App\Services\Ghl\FunnelsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FunnelsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGhl(): void
    {
        config()->set('integrations.ghl.base_url', 'https://services.leadconnectorhq.com');
        config()->set('integrations.ghl.version', '2021-07-28');

        $funnels = [
            ['_id' => 'f1', 'name' => 'Old Funnel', 'dateAdded' => '2025-01-01T00:00:00.000Z', 'dateUpdated' => '2025-02-01T00:00:00.000Z', 'createdBy' => 'u1', 'steps' => [[], []], 'url' => '/old'],
            ['_id' => 'f2', 'name' => 'New Funnel', 'dateAdded' => '2026-06-01T00:00:00.000Z', 'createdBy' => 'ghost', 'steps' => [[]], 'url' => '/new'],
            ['_id' => 'f3', 'name' => 'No Creator', 'dateAdded' => '2025-06-01T00:00:00.000Z', 'steps' => []],
            ['_id' => 'f4', 'name' => 'Deleted One', 'dateAdded' => '2026-07-01T00:00:00.000Z', 'deleted' => true],
        ];
        $users = [['id' => 'u1', 'email' => 'alice@bcf.com']];

        Http::fake([
            '*funnels/funnel/list*' => Http::response(['funnels' => $funnels], 200),
            '*users*'               => Http::response(['users' => $users], 200),
        ]);
    }

    public function test_single_account_parses_sorts_and_resolves_creator_email(): void
    {
        config()->set('integrations.accounts', [
            'bcf' => ['name' => 'BCF', 'ghl' => ['api_key' => 'pit-x', 'location_id' => 'loc1']],
        ]);
        $this->fakeGhl();

        $r = (new FunnelsService())->summary('bcf');

        // Deleted funnel excluded.
        $this->assertSame(3, $r['total']);
        $this->assertNotContains('Deleted One', array_column($r['funnels'], 'name'));

        // Newest first.
        $this->assertSame('New Funnel', $r['funnels'][0]['name']);

        $byName = collect($r['funnels'])->keyBy('name');
        // Creator resolves to email when the user is in the location list…
        $this->assertSame('alice@bcf.com', $byName['Old Funnel']['created_by']);
        // …and is null when the id is unknown or absent.
        $this->assertNull($byName['New Funnel']['created_by']);
        $this->assertNull($byName['No Creator']['created_by']);
        // Steps counted.
        $this->assertSame(2, $byName['Old Funnel']['steps']);
        $this->assertSame(0, $byName['No Creator']['steps']);
    }

    public function test_all_view_merges_accounts_and_tags_each_funnel(): void
    {
        config()->set('integrations.accounts', [
            'bcf' => ['name' => 'BCF', 'ghl' => ['api_key' => 'pit-x', 'location_id' => 'loc1']],
            'bgr' => ['name' => 'BGR', 'ghl' => ['api_key' => 'pit-y', 'location_id' => 'loc2']],
        ]);
        $this->fakeGhl();

        $r = (new FunnelsService())->summary('all');

        // 3 non-deleted funnels per account × 2 accounts.
        $this->assertSame(6, $r['total']);
        $labels = array_unique(array_column($r['funnels'], 'account'));
        sort($labels);
        $this->assertSame(['BCF', 'BGR'], $labels);
    }

    public function test_missing_credentials_returns_empty_without_error(): void
    {
        config()->set('integrations.accounts', [
            'bcf' => ['name' => 'BCF', 'ghl' => ['api_key' => null, 'location_id' => null]],
        ]);
        Http::fake(); // any accidental call returns empty 200

        $r = (new FunnelsService())->summary('bcf');

        $this->assertSame(0, $r['total']);
        $this->assertSame([], $r['funnels']);
    }
}
