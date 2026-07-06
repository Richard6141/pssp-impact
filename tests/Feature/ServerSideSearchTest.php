<?php

namespace Tests\Feature;

use App\Models\Collecte;
use App\Models\Site;
use App\Models\TypeDechet;
use App\Models\User;
use Database\Seeders\RolesPermissionSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServerSideSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ?TypeDechet $typeDechet = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesPermissionSeeder::class, RolesSeeder::class]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
    }

    private function makeCollecte(string $numero, string $siteName): Collecte
    {
        $site = Site::create([
            'site_name' => $siteName,
            'site_departement' => 'Ouémé',
            'site_commune' => 'Sèmè-Kpodji',
            'localisation' => 'Centre',
        ]);

        $this->typeDechet ??= TypeDechet::create(['libelle' => 'DASRI', 'description' => 'Test']);

        return Collecte::create([
            'numero_collecte' => $numero,
            'date_collecte' => now(),
            'poids' => 10,
            'type_dechet_id' => $this->typeDechet->type_dechet_id,
            'agent_id' => User::factory()->create()->user_id,
            'site_id' => $site->site_id,
        ]);
    }

    public function test_collectes_search_filters_across_all_records(): void
    {
        $this->makeCollecte('COL-AAA111', 'Hôpital Alpha');
        $this->makeCollecte('COL-BBB222', 'Clinique Beta');

        $response = $this->actingAs($this->admin)->get(route('collectes.index', ['search' => 'AAA111']));

        $response->assertOk();
        $response->assertSee('Hôpital Alpha');
        $response->assertDontSee('Clinique Beta');
    }

    public function test_collectes_search_matches_site_name(): void
    {
        $this->makeCollecte('COL-AAA111', 'Hôpital Alpha');
        $this->makeCollecte('COL-BBB222', 'Clinique Beta');

        $response = $this->actingAs($this->admin)->get(route('collectes.index', ['search' => 'Clinique Beta']));

        $response->assertOk();
        $response->assertSee('Clinique Beta');
        $response->assertDontSee('Hôpital Alpha');
    }

    public function test_pagination_links_keep_the_search_parameter(): void
    {
        // 12 collectes correspondantes -> 2 pages : les liens doivent garder ?search=
        for ($i = 1; $i <= 12; $i++) {
            $this->makeCollecte('COL-FILTRE' . Str::padLeft((string) $i, 2, '0'), 'Site ' . $i);
        }

        $response = $this->actingAs($this->admin)->get(route('collectes.index', ['search' => 'FILTRE']));

        $response->assertOk();
        $response->assertSee('search=FILTRE', false);
    }
}
