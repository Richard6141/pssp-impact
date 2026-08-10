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
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Couvre les trois retours terrain remontes par les collecteurs le 10/08/2026 :
 *  1. les donnees de la collecte doivent etre visibles sur la page de signature ;
 *  2. les decimales du poids ne doivent plus etre arrondies ;
 *  3. (la partie signature est cote client, cf. validations/create.blade.php).
 */
class RetourTerrainCollecteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesPermissionSeeder::class, RolesSeeder::class]);
    }

    private function makeSite(): Site
    {
        return Site::create([
            'site_name' => 'Hopital de zone ' . Str::random(4),
            'site_departement' => 'Oueme',
            'site_commune' => 'Seme-Kpodji',
            'localisation' => 'Centre-ville',
        ]);
    }

    private function makeTypeDechet(): TypeDechet
    {
        return TypeDechet::create([
            'libelle' => 'DASRI',
            'description' => 'Dechets d\'activites de soins a risques infectieux',
        ]);
    }

    // ---------------------------------------------------------------------
    // 1. Donnees de la collecte visibles par l'agent du site
    // ---------------------------------------------------------------------

    public function test_la_page_de_signature_affiche_date_heure_et_quantite(): void
    {
        $site = $this->makeSite();
        $type = $this->makeTypeDechet();

        $collecteur = User::factory()->create(['firstname' => 'Kossi', 'lastname' => 'Adjovi']);
        $collecte = Collecte::create([
            'numero_collecte' => 'COL-TEST01',
            'date_collecte' => '2026-08-05 09:47:00',
            'poids' => 12.755,
            'type_dechet_id' => $type->type_dechet_id,
            'agent_id' => $collecteur->user_id,
            'site_id' => $site->site_id,
        ]);

        $agentSante = User::factory()->create(['site_id' => $site->site_id]);
        $agentSante->assignRole('Agent santé');

        $response = $this->actingAs($agentSante)
            ->get(route('validations.create', ['collecte_id' => $collecte->collecte_id]));

        $response->assertOk();
        $response->assertSee('COL-TEST01');
        $response->assertSee('05/08/2026');   // date de collecte
        $response->assertSee('09:47');        // heure de collecte
        $response->assertSee('12,755');       // quantite, non arrondie
        $response->assertSee('DASRI');        // type de dechet
        $response->assertSee('Kossi Adjovi'); // collecteur
    }

    public function test_la_liste_des_validations_affiche_les_donnees_de_la_collecte(): void
    {
        $site = $this->makeSite();
        $type = $this->makeTypeDechet();
        $collecteur = User::factory()->create();

        Collecte::create([
            'numero_collecte' => 'COL-TEST02',
            'date_collecte' => '2026-08-06 14:30:00',
            'poids' => 8.25,
            'type_dechet_id' => $type->type_dechet_id,
            'agent_id' => $collecteur->user_id,
            'site_id' => $site->site_id,
        ]);

        $agentSante = User::factory()->create(['site_id' => $site->site_id]);
        $agentSante->assignRole('Agent santé');

        $response = $this->actingAs($agentSante)->get(route('validations.index'));

        $response->assertOk();
        $response->assertSee('06/08/2026');
        $response->assertSee('14:30');
        $response->assertSee('8,25');
    }

    // ---------------------------------------------------------------------
    // 2. Decimales du poids conservees telles qu'enregistrees
    // ---------------------------------------------------------------------

    private function agentCollecte(Site $site): User
    {
        $user = User::factory()->create(['site_id' => $site->site_id]);
        $user->assignRole('Agent collecte');

        return $user;
    }

    public function test_le_poids_conserve_trois_decimales(): void
    {
        $site = $this->makeSite();
        $type = $this->makeTypeDechet();

        $this->actingAs($this->agentCollecte($site))
            ->post(route('collectes.store'), [
                'poids' => '12.755',
                'type_dechet_id' => $type->type_dechet_id,
                'site_id' => $site->site_id,
                'has_incident' => '0',
            ])
            ->assertRedirect(route('collectes.index'));

        $this->assertSame(12.755, (float) Collecte::first()->poids);
    }

    public function test_le_poids_saisi_avec_une_virgule_est_accepte(): void
    {
        $site = $this->makeSite();
        $type = $this->makeTypeDechet();

        $this->actingAs($this->agentCollecte($site))
            ->post(route('collectes.store'), [
                'poids' => '0,125',
                'type_dechet_id' => $type->type_dechet_id,
                'site_id' => $site->site_id,
                'has_incident' => '0',
            ])
            ->assertRedirect(route('collectes.index'));

        $this->assertSame(0.125, (float) Collecte::first()->poids);
    }

    public function test_un_poids_trop_precis_est_refuse_au_lieu_d_etre_arrondi(): void
    {
        $site = $this->makeSite();
        $type = $this->makeTypeDechet();

        $this->actingAs($this->agentCollecte($site))
            ->post(route('collectes.store'), [
                'poids' => '12.7554',
                'type_dechet_id' => $type->type_dechet_id,
                'site_id' => $site->site_id,
                'has_incident' => '0',
            ])
            ->assertSessionHasErrors('poids');

        $this->assertNull(Collecte::first());
    }

    // ---------------------------------------------------------------------
    // Formatage d'affichage : jamais d'arrondi, pas de zeros parasites
    // ---------------------------------------------------------------------

    public static function poidsProvider(): array
    {
        return [
            'trois decimales' => ['12.755', '12,755'],
            'zeros de fin retires' => ['12.500', '12,5'],
            'entier' => ['12.000', '12'],
            'milliers' => ['1234.125', '1 234,125'],
            'petite quantite' => ['0.125', '0,125'],
            'nul' => [null, '0'],
        ];
    }

    #[DataProvider('poidsProvider')]
    public function test_le_poids_est_affiche_sans_arrondi($valeur, string $attendu): void
    {
        $this->assertSame($attendu, Collecte::formatPoids($valeur));
    }

    public function test_le_formulaire_de_modification_reaffiche_le_poids_saisi(): void
    {
        $site = $this->makeSite();
        $type = $this->makeTypeDechet();
        $agent = $this->agentCollecte($site);

        $collecte = Collecte::create([
            'numero_collecte' => 'COL-TEST03',
            'date_collecte' => now(),
            'poids' => 12.755,
            'type_dechet_id' => $type->type_dechet_id,
            'agent_id' => $agent->user_id,
            'site_id' => $site->site_id,
        ]);

        // Le cast decimal:3 renvoie "12.755" : la valeur reinjectee dans le
        // champ number doit rester identique a ce que l'agent avait saisi.
        $this->assertSame('12.755', Collecte::poidsInputValue($collecte->fresh()->poids));
        $this->assertSame('12.5', Collecte::poidsInputValue('12.500'));
        $this->assertSame('120', Collecte::poidsInputValue('120.000'));
        $this->assertSame('', Collecte::poidsInputValue(null));
    }
}
