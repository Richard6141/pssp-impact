<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\CollecteController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TypeDechetController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\ObservationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\backend\IndexController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\Auth\UserProfileController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware('auth')->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('home');
    Route::get('/dashboard', [IndexController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
Route::group(['prefix' => 'auth'], function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
    Route::get('/password/reset', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Routes pour le profil (à ajouter dans routes/web.php)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');
});

Route::group(['prefix' => 'account', 'middleware' => 'auth'], function () {
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile');
});

// Type de collectes (type_dechets)
Route::group(['middleware' => 'auth'], function () {
    Route::get('/type-dechets', [TypeDechetController::class, 'index'])->name('type_dechets.index');
    Route::get('/type-dechets/create', [TypeDechetController::class, 'create'])->name('type_dechets.create');
    Route::post('/type-dechets/store', [TypeDechetController::class, 'store'])->name('type_dechets.store');
    Route::get('/type-dechets/{id}/edit', [TypeDechetController::class, 'edit'])->name('type_dechets.edit');
    Route::put('/type-dechets/{id}/update', [TypeDechetController::class, 'update'])->name('type_dechets.update');
    Route::delete('/type-dechets/{id}/delete', [TypeDechetController::class, 'destroy'])->name('type_dechets.destroy');
});

Route::group(['middleware' => 'auth'], function () {
    // Gestion des sites
    Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
    Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
    Route::post('/sites/store', [SiteController::class, 'store'])->name('sites.store');
    Route::get('/sites/{id}/show', [SiteController::class, 'show'])->name('sites.show');
    Route::get('/sites/{id}/edit', [SiteController::class, 'edit'])->name('sites.edit');
    Route::put('/sites/{id}/update', [SiteController::class, 'update'])->name('sites.update');
    Route::delete('/sites/{id}/delete', [SiteController::class, 'destroy'])->name('sites.destroy');
});

Route::group(['middleware' => 'auth'], function () {
    // Collectes
    Route::get('/collectes', [App\Http\Controllers\CollecteController::class, 'index'])->name('collectes.index');
    Route::get('/collectes/create', [App\Http\Controllers\CollecteController::class, 'create'])->name('collectes.create');
    Route::post('/collectes', [App\Http\Controllers\CollecteController::class, 'store'])->name('collectes.store');
    Route::get('/collectes/{id}', [App\Http\Controllers\CollecteController::class, 'show'])->name('collectes.show');
    Route::get('/collectes/{id}/edit', [App\Http\Controllers\CollecteController::class, 'edit'])->name('collectes.edit');
    Route::put('/collectes/{id}', [App\Http\Controllers\CollecteController::class, 'update'])->name('collectes.update');
    Route::delete('/collectes/{id}', [App\Http\Controllers\CollecteController::class, 'destroy'])->name('collectes.destroy');
});

Route::group(['middleware' => 'auth'], function () {

    // Liste de toutes les factures
    Route::get('/factures', [FactureController::class, 'index'])->name('factures.index');

    // Formulaire pour créer une facture
    Route::get('/factures/create', [FactureController::class, 'create'])->name('factures.create');

    // Enregistrement d'une nouvelle facture
    Route::post('/factures', [FactureController::class, 'store'])->name('factures.store');

    // Afficher une facture spécifique
    Route::get('/factures/{facture}', [FactureController::class, 'show'])->name('factures.show');

    // Formulaire pour modifier une facture
    Route::get('/factures/{facture}/edit', [FactureController::class, 'edit'])->name('factures.edit');

    // Mettre à jour une facture
    Route::put('/factures/{facture}', [FactureController::class, 'update'])->name('factures.update');

    // Supprimer une facture
    Route::delete('/factures/{facture}', [FactureController::class, 'destroy'])->name('factures.destroy');

    // Route pour récupérer les collectes d'un site via AJAX
    Route::get('factures/collectes-by-site/{siteId}', [FactureController::class, 'getCollectesBySite'])
        ->name('factures.collectes-by-site');

    Route::get('/debug-factures', [FactureController::class, 'debug']);
});

Route::middleware(['auth'])->group(function () {
    // Ressource Paiement
    Route::resource('paiements', PaiementController::class);

    // Route spécifique pour marquer un paiement comme validé
    Route::post('paiements/{paiement}/valider', [PaiementController::class, 'valider'])
        ->name('paiements.valider');


    // Route spécifique pour marquer un paiement comme annulé
    Route::patch('paiements/{paiement}/annuler', [PaiementController::class, 'annuler'])
        ->name('paiements.annuler');
});

Route::middleware('auth')->group(function () {
    Route::resource('observations', ObservationController::class);

    // Optionnel pour voir les soft deleted
    Route::get('observations/trashed', [ObservationController::class, 'trashed'])->name('observations.trashed');
    Route::patch('observations/{observation}/restore', [ObservationController::class, 'restore'])->name('observations.restore');

    Route::prefix('validations')->name('validations.')->group(function () {
        Route::get('/', [ValidationController::class, 'index'])->name('index');
        Route::get('/create/{collecte_id}', [ValidationController::class, 'create'])->name('create');
        Route::post('/store', [ValidationController::class, 'store'])->name('store');
        Route::get('/{validation}', [ValidationController::class, 'show'])->name('show');
        Route::delete('/{validation}', [ValidationController::class, 'destroy'])->name('destroy');
    });

    Route::get('/configurations', [ConfigurationController::class, 'index'])->name('configuration');

    // Routes pour les rôles
    Route::post('/roles', [ConfigurationController::class, 'storeRole'])->name('roles.store');
    Route::delete('/roles/{id}/delete', [ConfigurationController::class, 'destroyRole'])->name('roles.destroy');

    // Routes pour les permissions
    Route::post('/permissions', [ConfigurationController::class, 'storePermission'])->name('permissions.store');
    Route::delete('/permissions/{id}/delete', [ConfigurationController::class, 'destroyPermission'])->name('permissions.destroy');

    // Routes pour l'assignation des rôles
    Route::post('/assign-role', [ConfigurationController::class, 'assignRole'])->name('assign-role');
    Route::get('/users/{user}/roles', [ConfigurationController::class, 'getUserRoles'])->name('users.roles');

    // Routes utilitaires pour initialiser le système
    Route::post('/create-default-permissions', [ConfigurationController::class, 'createDefaultPermissions'])->name('create-default-permissions');
    Route::post('/create-default-roles', [ConfigurationController::class, 'createDefaultRoles'])->name('create-default-roles');
});

Route::middleware('auth')->group(function () {

    // === GESTION DES UTILISATEURS ===
    Route::resource('users', App\Http\Controllers\UserController::class)->middleware('permission:users.view');

    // === RAPPORTS ===
    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/collectes', [App\Http\Controllers\RapportController::class, 'collectes'])->name('collectes');
        Route::get('/financier', [App\Http\Controllers\RapportController::class, 'financier'])->name('financier');
        Route::get('/sites', [App\Http\Controllers\RapportController::class, 'sites'])->name('sites');

        // Exports (nécessite permission export)
        Route::middleware('permission:rapports.export')->group(function () {
            Route::get('/export/pdf/{type}', [App\Http\Controllers\RapportController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel/{type}', [App\Http\Controllers\RapportController::class, 'exportExcel'])->name('export.excel');
        });
    });

    // === SYSTÈME (Super Admin uniquement) ===
    Route::prefix('system')->name('system.')->middleware('role:Super Admin')->group(function () {
        Route::get('/logs', [App\Http\Controllers\SystemController::class, 'logs'])->name('logs');
        Route::get('/backup', [App\Http\Controllers\SystemController::class, 'backup'])->name('backup');
        Route::post('/backup/create', [App\Http\Controllers\SystemController::class, 'createBackup'])->name('backup.create');
        Route::get('/maintenance', [App\Http\Controllers\SystemController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance/toggle', [App\Http\Controllers\SystemController::class, 'toggleMaintenance'])->name('maintenance.toggle');

        // Informations système
        Route::get('/info', [App\Http\Controllers\SystemController::class, 'info'])->name('info');
        Route::get('/database', [App\Http\Controllers\SystemController::class, 'database'])->name('database');
    });

    // === API ENDPOINTS POUR RAPPORTS (AJAX) ===
    Route::prefix('api')->name('api.')->group(function () {
        // Données pour graphiques
        Route::get('/collectes/chart-data', [App\Http\Controllers\RapportController::class, 'getCollectesChartData'])->name('collectes.chart');
        Route::get('/financier/chart-data', [App\Http\Controllers\RapportController::class, 'getFinancierChartData'])->name('financier.chart');
        Route::get('/sites/chart-data', [App\Http\Controllers\RapportController::class, 'getSitesChartData'])->name('sites.chart');

        // Autocomplete pour filtres
        Route::get('/sites/search', [App\Http\Controllers\SiteController::class, 'search'])->name('sites.search');
        //Route::get('/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('users.search');
        Route::get('/chart-data', [IndexController::class, 'getChartData'])->name('dashboard.chart-data');
        Route::get('/refresh', [IndexController::class, 'refreshData'])->name('dashboard.refresh');
        Route::get('/filter/{type}/{period}', [IndexController::class, 'filterData'])->name('dashboard.filter');
        Route::get('/collectes-filter/{period}', [IndexController::class, 'filterCollectes'])->name('dashboard.collectes-filter');
    });
    Route::patch('/collectes/{id}/validate', [CollecteController::class, 'validate'])
        ->name('collectes.validate');

    // ✅ Route pour invalider une collecte
    Route::patch('/collectes/{id}/invalidate', [CollecteController::class, 'invalidate'])
        ->name('collectes.invalidate');
});
