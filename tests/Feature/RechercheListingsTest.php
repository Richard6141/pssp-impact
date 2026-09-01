<?php

namespace Tests\Feature;

use App\Models\EcritureComptable;
use App\Models\Site;
use App\Models\User;
use App\Models\UserInvitation;
use App\Services\AuditService;
use Database\Seeders\RolesPermissionSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Le client demande un champ de recherche sur toutes les listes : la
 * recherche doit porter sur l'ensemble des enregistrements, pas seulement
 * sur la page affichee (demande du 10/08/2026).
 */
class RechercheListingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesPermissionSeeder::class, RolesSeeder::class]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
    }

    public function test_la_liste_des_utilisateurs_est_filtree_cote_serveur(): void
    {
        User::factory()->create(['firstname' => 'Adjovi', 'lastname' => 'Kossi']);
        User::factory()->create(['firstname' => 'Mensah', 'lastname' => 'Doe']);

        $response = $this->actingAs($this->admin)
            ->get(route('users.index', ['search' => 'Adjovi']));

        $response->assertOk();
        $response->assertSee('Adjovi');
        $response->assertDontSee('Mensah');
    }

    public function test_la_liste_des_utilisateurs_est_filtrable_par_site(): void
    {
        $site = Site::create([
            'site_name' => 'Hopital Saint-Luc',
            'site_departement' => 'Atlantique',
            'site_commune' => 'Abomey-Calavi',
            'localisation' => 'Godomey',
        ]);

        User::factory()->create(['firstname' => 'Rattache', 'site_id' => $site->site_id]);
        User::factory()->create(['firstname' => 'Ailleurs']);

        $response = $this->actingAs($this->admin)
            ->get(route('users.index', ['site_id' => $site->site_id]));

        $response->assertOk();
        $response->assertSee('Rattache');
        $response->assertDontSee('Ailleurs');
    }

    public function test_la_liste_des_invitations_est_filtree_cote_serveur(): void
    {
        $site = Site::create([
            'site_name' => 'Hopital Bethesda',
            'site_departement' => 'Littoral',
            'site_commune' => 'Cotonou',
            'localisation' => 'Centre',
        ]);

        UserInvitation::create([
            'email' => 'trouve@example.com',
            'token' => Str::random(40),
            'site_id' => $site->site_id,
            'expires_at' => now()->addDays(7),
        ]);
        UserInvitation::create([
            'email' => 'autre@example.com',
            'token' => Str::random(40),
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.invitations.index', ['search' => 'trouve']));

        $response->assertOk();
        $response->assertSee('trouve@example.com');
        $response->assertDontSee('autre@example.com');
    }

    public function test_le_journal_comptable_est_filtre_cote_serveur(): void
    {
        EcritureComptable::create([
            'piece_id' => (string) Str::uuid(),
            'user_id' => $this->admin->user_id,
            'date_ecriture' => now(),
            'numero_piece' => 'FA-0001',
            'type_piece' => 'facture',
            'compte_debit' => '411000',
            'compte_credit' => '706000',
            'libelle' => 'Collecte hopital Bethesda',
            'montant' => 150000,
        ]);
        EcritureComptable::create([
            'piece_id' => (string) Str::uuid(),
            'user_id' => $this->admin->user_id,
            'date_ecriture' => now(),
            'numero_piece' => 'FA-0002',
            'type_piece' => 'facture',
            'compte_debit' => '411000',
            'compte_credit' => '706000',
            'libelle' => 'Collecte centre Saint-Luc',
            'montant' => 90000,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('comptabilite.journal', ['search' => 'FA-0001']));

        $response->assertOk();
        $response->assertSee('FA-0001');
        $response->assertDontSee('FA-0002');
    }

    public function test_les_logs_d_audit_sont_filtres_cote_serveur(): void
    {
        $service = app(AuditService::class);

        \App\Models\AuditLog::create([
            'user_id' => $this->admin->user_id,
            'action' => 'login',
            'entity_type' => 'User',
            'description' => 'Connexion reussie',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'performed_at' => now(),
        ]);
        \App\Models\AuditLog::create([
            'user_id' => $this->admin->user_id,
            'action' => 'delete',
            'entity_type' => 'Collecte',
            'description' => 'Suppression collecte',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'performed_at' => now(),
        ]);

        $logs = $service->getLogs(['search' => 'login'], 25);

        $this->assertCount(1, $logs);
        $this->assertSame('login', $logs->first()->action);
    }
}
