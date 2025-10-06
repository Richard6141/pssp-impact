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
*/

// Routes publiques
Route::middleware('auth')->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('home');
    Route::get('/dashboard', [IndexController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Routes d'authentification
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

// Routes pour le profil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');
});

Route::group(['prefix' => 'account', 'middleware' => 'auth'], function () {
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile');
});

// === GESTION DES TYPES DE DÉCHETS ===
Route::middleware(['auth'])->prefix('type-dechets')->name('type_dechets.')->group(function () {
    Route::get('/', [TypeDechetController::class, 'index'])
        ->middleware('can:type_dechets.view')
        ->name('index');

    Route::get('/create', [TypeDechetController::class, 'create'])
        ->middleware('can:type_dechets.create')
        ->name('create');

    Route::post('/store', [TypeDechetController::class, 'store'])
        ->middleware('can:type_dechets.create')
        ->name('store');

    Route::get('/{id}/edit', [TypeDechetController::class, 'edit'])
        ->middleware('can:type_dechets.update')
        ->name('edit');

    Route::put('/{id}/update', [TypeDechetController::class, 'update'])
        ->middleware('can:type_dechets.update')
        ->name('update');

    Route::delete('/{id}/delete', [TypeDechetController::class, 'destroy'])
        ->middleware('can:type_dechets.delete')
        ->name('destroy');
});

// === GESTION DES SITES ===
Route::middleware(['auth'])->prefix('sites')->name('sites.')->group(function () {
    Route::get('/', [SiteController::class, 'index'])
        ->middleware('can:sites.view')
        ->name('index');

    Route::get('/create', [SiteController::class, 'create'])
        ->middleware('can:sites.create')
        ->name('create');

    Route::post('/store', [SiteController::class, 'store'])
        ->middleware('can:sites.create')
        ->name('store');

    Route::get('/{id}/show', [SiteController::class, 'show'])
        ->middleware('can:sites.view')
        ->name('show');

    Route::get('/{id}/edit', [SiteController::class, 'edit'])
        ->middleware('can:sites.update')
        ->name('edit');

    Route::put('/{id}/update', [SiteController::class, 'update'])
        ->middleware('can:sites.update')
        ->name('update');

    Route::delete('/{id}/delete', [SiteController::class, 'destroy'])
        ->middleware('can:sites.delete')
        ->name('destroy');
});

// === GESTION DES COLLECTES ===
Route::middleware(['auth'])->prefix('collectes')->name('collectes.')->group(function () {
    Route::get('/', [CollecteController::class, 'index'])
        ->middleware('can:collectes.view')
        ->name('index');

    Route::get('/create', [CollecteController::class, 'create'])
        ->middleware('can:collectes.create')
        ->name('create');

    Route::post('/', [CollecteController::class, 'store'])
        ->middleware('can:collectes.create')
        ->name('store');

    Route::get('/{id}', [CollecteController::class, 'show'])
        ->middleware('can:collectes.view')
        ->name('show');

    Route::get('/{id}/edit', [CollecteController::class, 'edit'])
        ->middleware('can:collectes.update')
        ->name('edit');

    Route::put('/{id}', [CollecteController::class, 'update'])
        ->middleware('can:collectes.update')
        ->name('update');

    Route::delete('/{id}', [CollecteController::class, 'destroy'])
        ->middleware('can:collectes.delete')
        ->name('destroy');

    Route::patch('/{id}/validate', [CollecteController::class, 'validate'])
        ->middleware('can:collectes.validate_final')
        ->name('validate');

    Route::patch('/{id}/invalidate', [CollecteController::class, 'invalidate'])
        ->middleware('can:collectes.validate_final')
        ->name('invalidate');
});

// === GESTION DES FACTURES ===
Route::middleware(['auth'])->prefix('factures')->name('factures.')->group(function () {
    Route::get('/', [FactureController::class, 'index'])
        ->middleware('can:factures.view')
        ->name('index');

    Route::get('/create', [FactureController::class, 'create'])
        ->middleware('can:factures.create')
        ->name('create');

    Route::post('/', [FactureController::class, 'store'])
        ->middleware('can:factures.create')
        ->name('store');

    Route::get('/{facture}', [FactureController::class, 'show'])
        ->middleware('can:factures.view')
        ->name('show');

    Route::get('/{facture}/edit', [FactureController::class, 'edit'])
        ->middleware('can:factures.update')
        ->name('edit');

    Route::put('/{facture}', [FactureController::class, 'update'])
        ->middleware('can:factures.update')
        ->name('update');

    Route::delete('/{facture}', [FactureController::class, 'destroy'])
        ->middleware('can:factures.delete')
        ->name('destroy');

    Route::get('/collectes-by-site/{siteId}', [FactureController::class, 'getCollectesBySite'])
        ->middleware('can:factures.view')
        ->name('collectes-by-site');
});

// Route de debug (à supprimer en production)
Route::get('/debug-factures', [FactureController::class, 'debug'])->middleware('auth');

// === GESTION DES PAIEMENTS ===
Route::middleware(['auth'])->prefix('paiements')->name('paiements.')->group(function () {
    Route::get('/', [PaiementController::class, 'index'])
        ->middleware('can:paiements.view')
        ->name('index');

    Route::get('/create', [PaiementController::class, 'create'])
        ->middleware('can:paiements.record')
        ->name('create');

    Route::post('/', [PaiementController::class, 'store'])
        ->middleware('can:paiements.record')
        ->name('store');

    Route::get('/{paiement}', [PaiementController::class, 'show'])
        ->middleware('can:paiements.view')
        ->name('show');

    Route::get('/{paiement}/edit', [PaiementController::class, 'edit'])
        ->middleware('can:paiements.update')
        ->name('edit');

    Route::put('/{paiement}', [PaiementController::class, 'update'])
        ->middleware('can:paiements.update')
        ->name('update');

    Route::delete('/{paiement}', [PaiementController::class, 'destroy'])
        ->middleware('can:paiements.delete')
        ->name('destroy');

    Route::post('/{paiement}/valider', [PaiementController::class, 'valider'])
        ->middleware('can:paiements.validate')
        ->name('valider');

    Route::patch('/{paiement}/annuler', [PaiementController::class, 'annuler'])
        ->middleware('can:paiements.validate')
        ->name('annuler');
});

// === GESTION DES OBSERVATIONS ===
Route::middleware(['auth'])->prefix('observations')->name('observations.')->group(function () {
    Route::get('/', [ObservationController::class, 'index'])
        ->middleware('can:observations.view')
        ->name('index');

    Route::get('/create', [ObservationController::class, 'create'])
        ->middleware('can:observations.create')
        ->name('create');

    Route::post('/', [ObservationController::class, 'store'])
        ->middleware('can:observations.create')
        ->name('store');

    Route::get('/{observation}', [ObservationController::class, 'show'])
        ->middleware('can:observations.view')
        ->name('show');

    Route::get('/{observation}/edit', [ObservationController::class, 'edit'])
        ->middleware('can:observations.create')
        ->name('edit');

    Route::put('/{observation}', [ObservationController::class, 'update'])
        ->middleware('can:observations.create')
        ->name('update');

    Route::delete('/{observation}', [ObservationController::class, 'destroy'])
        ->middleware('can:observations.delete')
        ->name('destroy');

    Route::get('/trashed', [ObservationController::class, 'trashed'])
        ->middleware('can:observations.view')
        ->name('trashed');

    Route::patch('/{observation}/restore', [ObservationController::class, 'restore'])
        ->middleware('can:observations.create')
        ->name('restore');
});

// === GESTION DES VALIDATIONS ===
Route::middleware(['auth'])->prefix('validations')->name('validations.')->group(function () {
    Route::get('/', [ValidationController::class, 'index'])
        ->middleware('can:validations.view')
        ->name('index');

    Route::get('/create/{collecte_id}', [ValidationController::class, 'create'])
        ->middleware('can:validations.create')
        ->name('create');

    Route::post('/store', [ValidationController::class, 'store'])
        ->middleware('can:validations.create')
        ->name('store');

    Route::get('/{validation}', [ValidationController::class, 'show'])
        ->middleware('can:validations.view')
        ->name('show');

    Route::delete('/{validation}', [ValidationController::class, 'destroy'])
        ->middleware('can:validations.delete')
        ->name('destroy');
});

// === GESTION DES CONFIGURATIONS ===
Route::middleware(['auth'])->group(function () {
    Route::get('/configurations', [ConfigurationController::class, 'index'])
        //->middleware('can:configurations.view')
        ->name('configuration');

    // Routes pour les rôles
    Route::post('/roles', [ConfigurationController::class, 'storeRole'])
        //->middleware('can:roles.create')
        ->name('roles.store');

    Route::delete('/roles/{id}/delete', [ConfigurationController::class, 'destroyRole'])
        ->middleware('can:roles.delete')
        ->name('roles.destroy');

    // Routes pour les permissions
    Route::post('/permissions', [ConfigurationController::class, 'storePermission'])
        //->middleware('can:permissions.assign')
        ->name('permissions.store');

    Route::delete('/permissions/{id}/delete', [ConfigurationController::class, 'destroyPermission'])
        //->middleware('can:permissions.revoke')
        ->name('permissions.destroy');

    // Routes pour l'assignation des rôles
    Route::post('/assign-role', [ConfigurationController::class, 'assignRole'])
        //->middleware('can:users.assign_roles')
        ->name('assign-role');

    Route::get('/users/{user}/roles', [ConfigurationController::class, 'getUserRoles'])
        //->middleware('can:users.view')
        ->name('users.roles');

    // Routes utilitaires pour initialiser le système (Super Admin seulement)
    Route::post('/create-default-permissions', [ConfigurationController::class, 'createDefaultPermissions'])
        //->middleware('role:Super Admin')
        ->name('create-default-permissions');

    Route::post('/create-default-roles', [ConfigurationController::class, 'createDefaultRoles'])
        //->middleware('role:Super Admin')
        ->name('create-default-roles');
});

// === GESTION DES UTILISATEURS ===
Route::middleware(['auth'])->group(function () {
    Route::resource('users', App\Http\Controllers\UserController::class)
        ->middleware('can:users.view');
});

// === RAPPORTS ===
Route::middleware(['auth'])->prefix('rapports')->name('rapports.')->group(function () {
    Route::get('/collectes', [RapportController::class, 'collectes'])
        ->middleware('can:rapports.collectes')
        ->name('collectes');
    Route::get('/financier', [RapportController::class, 'financier'])
        ->middleware('can:rapports.financier')
        ->name('financier');

    Route::get('/sites', [RapportController::class, 'sites'])
        ->middleware('can:rapports.sites')
        ->name('sites');

    // Exports
    Route::middleware('can:rapports.export')->group(function () {
        Route::get('/export/pdf/{type}', [RapportController::class, 'exportPdf'])
            ->middleware('can:export.pdf')
            ->name('export.pdf');

        Route::get('/export/excel/{type}', [RapportController::class, 'exportExcel'])
            ->middleware('can:export.excel')
            ->name('export.excel');
    });
});

// === SYSTÈME (Super Admin uniquement) ===
Route::middleware(['auth'])->prefix('system')->name('system.')->group(function () {
    Route::get('/logs', [SystemController::class, 'logs'])
        ->middleware('can:system.logs')
        ->name('logs');

    Route::get('/backup', [SystemController::class, 'backup'])
        ->middleware('can:system.backup')
        ->name('backup');

    Route::post('/backup/create', [SystemController::class, 'createBackup'])
        ->middleware('can:system.backup')
        ->name('backup.create');

    Route::get('/maintenance', [SystemController::class, 'maintenance'])
        ->middleware('can:system.maintenance')
        ->name('maintenance');

    Route::post('/maintenance/toggle', [SystemController::class, 'toggleMaintenance'])
        ->middleware('can:system.maintenance')
        ->name('maintenance.toggle');

    // Informations système
    Route::get('/info', [SystemController::class, 'info'])
        //->middleware('can:system.info')
        ->name('info');

    Route::get('/database', [SystemController::class, 'database'])
        //->middleware('can:system.database')
        ->name('database');

    // À ajouter dans vos routes
    Route::post('/logs/clear', [SystemController::class, 'clearLogs'])
        ->middleware('can:system.logs')
        ->name('logs.clear');
    Route::get('/logs/download', [SystemController::class, 'downloadLogs'])
        ->middleware('can:system.logs')
        ->name('logs.download');
    Route::get('/backup/{filename}/download', [SystemController::class, 'downloadBackup'])
        ->middleware('can:system.backup')
        ->name('backup.download');
    Route::delete('/backup/{filename}', [SystemController::class, 'deleteBackup'])
        ->middleware('can:system.backup')
        ->name('backup.delete');
    Route::post('/database/optimize', [SystemController::class, 'optimizeDatabase'])
        ->middleware('can:system.database')
        ->name('database.optimize');
    Route::post('/cache/clear', [SystemController::class, 'clearCache'])
        ->middleware('can:system.info')
        ->name('cache.clear');
});

// === API ENDPOINTS POUR RAPPORTS (AJAX) ===
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    // Données pour graphiques
    Route::get('/collectes/chart-data', [RapportController::class, 'getCollectesChartData'])
        ->middleware('can:rapports.collectes')
        ->name('collectes.chart');

    Route::get('/financier/chart-data', [RapportController::class, 'getFinancierChartData'])
        ->middleware('can:rapports.financier')
        ->name('financier.chart');

    Route::get('/sites/chart-data', [RapportController::class, 'getSitesChartData'])
        ->middleware('can:rapports.sites')
        ->name('sites.chart');

    // Autocomplete pour filtres
    Route::get('/sites/search', [SiteController::class, 'search'])
        ->middleware('can:sites.view')
        ->name('sites.search');

    // Dashboard (accessible à tous les utilisateurs authentifiés)
    Route::get('/chart-data', [IndexController::class, 'getChartData'])
        ->name('dashboard.chart-data');

    Route::get('/refresh', [IndexController::class, 'refreshData'])
        ->name('dashboard.refresh');

    Route::get('/filter/{type}/{period}', [IndexController::class, 'filterData'])
        ->name('dashboard.filter');

    Route::get('/collectes-filter/{period}', [IndexController::class, 'filterCollectes'])
        ->name('dashboard.collectes-filter');

    // Dashboard (accessible à tous les utilisateurs authentifiés)
    Route::get('/chart-data', [IndexController::class, 'getChartData'])
        ->name('chart.data');

    Route::get('/refresh', [IndexController::class, 'refreshData'])
        ->name('dashboard.refresh');

    Route::get('/filter-data/{type}/{period}', [IndexController::class, 'filterData'])
        ->name('filter.data');

    Route::get('/filter-collectes/{period}', [IndexController::class, 'filterCollectes'])
        ->name('filter.collectes');
});
