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

class AgentSanteValidationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCollecte(Site $site): Collecte
    {
        $agent = User::factory()->create();

        $typeDechet = TypeDechet::create([
            'libelle' => 'DASRI',
            'description' => 'Déchets d\'activités de soins à risques infectieux',
        ]);

        return Collecte::create([
            'numero_collecte' => 'COL-' . strtoupper(Str::random(6)),
            'date_collecte' => now(),
            'poids' => 12.5,
            'type_dechet_id' => $typeDechet->type_dechet_id,
            'agent_id' => $agent->user_id,
            'site_id' => $site->site_id,
        ]);
    }

    private function makeSite(): Site
    {
        return Site::create([
            'site_name' => 'Hôpital de zone ' . Str::random(4),
            'site_departement' => 'Ouémé',
            'site_commune' => 'Sèmè-Kpodji',
            'localisation' => 'Centre-ville',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesPermissionSeeder::class, RolesSeeder::class]);
    }

    public function test_agent_sante_role_has_validation_permissions(): void
    {
        $agentSante = User::factory()->create();
        $agentSante->assignRole('Agent santé');

        $this->assertTrue($agentSante->can('collectes.validate_site'));
        $this->assertTrue($agentSante->can('validations.create'));
        $this->assertTrue($agentSante->can('validations.view'));
    }

    public function test_agent_sante_can_sign_collecte_of_his_site(): void
    {
        $site = $this->makeSite();
        $collecte = $this->makeCollecte($site);

        $agentSante = User::factory()->create(['site_id' => $site->site_id]);
        $agentSante->assignRole('Agent santé');

        $this->assertTrue($collecte->canBeSignedBy($agentSante));
    }

    public function test_agent_sante_cannot_sign_collecte_of_another_site(): void
    {
        $siteA = $this->makeSite();
        $siteB = $this->makeSite();
        $collecte = $this->makeCollecte($siteA);

        $agentSante = User::factory()->create(['site_id' => $siteB->site_id]);
        $agentSante->assignRole('Agent santé');

        $this->assertFalse($collecte->canBeSignedBy($agentSante));
    }

    public function test_site_responsable_can_still_sign(): void
    {
        $site = $this->makeSite();
        $responsable = User::factory()->create();
        $site->update(['responsable' => $responsable->user_id]);

        $collecte = $this->makeCollecte($site);

        $this->assertTrue($collecte->fresh()->canBeSignedBy($responsable));
    }

    public function test_user_without_permission_cannot_sign_even_on_his_site(): void
    {
        $site = $this->makeSite();
        $collecte = $this->makeCollecte($site);

        // Agent collecte : rattaché au site mais sans collectes.validate_site
        $agentCollecte = User::factory()->create(['site_id' => $site->site_id]);
        $agentCollecte->assignRole('Agent collecte');

        $this->assertFalse($collecte->canBeSignedBy($agentCollecte));
    }
}
