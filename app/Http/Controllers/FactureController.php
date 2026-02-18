<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Facture;
use App\Models\Collecte;
use App\Services\ComptabiliteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FactureController extends Controller
{
    private function getVisibleSiteIds(): array
    {
        $user = auth()->user();
        $siteIds = $user->sites()->pluck('sites.site_id')->all();
        $responsableSiteIds = Site::where('responsable', $user->user_id)->pluck('site_id')->all();
        $siteIds = array_merge($siteIds, $responsableSiteIds);

        if (!empty($user->site_id)) {
            $siteIds[] = $user->site_id;
        }

        return array_values(array_unique(array_filter($siteIds)));
    }

    private function applyVisibility($query)
    {
        $user = auth()->user();

        if ($user->hasRole('Responsable site')) {
            $query->whereIn('site_id', $this->getVisibleSiteIds());
        }

        return $query;
    }

    private function ensureFactureAllowed(Facture $facture): void
    {
        $user = auth()->user();

        if ($user->hasRole('Responsable site')) {
            $allowedSites = $this->getVisibleSiteIds();
            if (!in_array($facture->site_id, $allowedSites, true)) {
                abort(403);
            }
        }
    }

    /**
     * Liste des factures
     */
    public function index()
    {
        $query = Facture::with(['site', 'comptable'])->latest();
        $this->applyVisibility($query);
        $factures = $query->paginate(10);
        return view('factures.index', compact('factures'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $sitesQuery = Site::query();
        $user = auth()->user();

        if ($user->hasRole('Responsable site')) {
            $sitesQuery->whereIn('site_id', $this->getVisibleSiteIds());
        }

        $sites = $sitesQuery->get();
        $comptable = Auth::user();

        return view('factures.create', compact('sites', 'comptable'));
    }

    /**
     * Enregistrement d'une nouvelle facture
     */
    public function store(Request $request)
    {
        $request->validate([
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date',
            'montant_facture' => 'required|numeric',
            'site_id' => 'required|exists:sites,site_id',
            'photo_facture' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'collecte_ids' => 'nullable|array',
            'collecte_ids.*' => 'exists:collectes,collecte_id',
        ]);

        $user = auth()->user();
        if ($user && $user->hasRole('Responsable site')) {
            if (!in_array($request->site_id, $this->getVisibleSiteIds(), true)) {
                abort(403);
            }
        }

        // Génération du numéro de facture (méthode la plus fiable)
        $anneeActuelle = date('Y');

        // Récupérer le dernier numéro de facture de l'année
        $derniereFacture = Facture::withTrashed()
            ->whereYear('created_at', $anneeActuelle)
            ->where('numero_facture', 'LIKE', "FAC-{$anneeActuelle}-%")
            ->orderByRaw("CAST(SUBSTRING(numero_facture, 10) AS UNSIGNED) DESC")
            ->first();

        $dernierNumero = 0;
        if ($derniereFacture) {
            // Extraire le numéro de la facture (ex: FAC-2025-0001 -> 1)
            $dernierNumero = (int) substr($derniereFacture->numero_facture, -4);
        }

        $numero_facture = sprintf('FAC-%s-%04d', $anneeActuelle, $dernierNumero + 1);

        // Création de la facture
        $facture = new Facture();
        $facture->date_facture = $request->date_facture;
        $facture->numero_facture = $numero_facture;
        $facture->montant_facture = $request->montant_facture;
        $facture->date_echeance = $request->date_echeance;
        $facture->site_id = $request->site_id;
        $facture->comptable_id = auth()->user()->user_id;
        $facture->save();

        // Gestion du fichier
        if ($request->hasFile('photo_facture')) {
            $path = $request->file('photo_facture')->store('factures', 'public');
            $facture->photo_facture = $path;
            $facture->save();
        }

        // Association des collectes
        if ($request->has('collecte_ids')) {
            $syncData = collect($request->collecte_ids)->mapWithKeys(function ($collecteId) {
                return [$collecteId => ['factureCollecte_id' => (string) \Str::uuid()]];
            })->toArray();

            $facture->collectes()->sync($syncData);
        }

        ComptabiliteService::recordFacture($facture);

        return redirect()->route('factures.index')->with('success', 'Facture ajoutée avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Facture $facture)
    {
        $this->ensureFactureAllowed($facture);

        $sitesQuery = Site::query();
        $user = auth()->user();

        if ($user->hasRole('Responsable site')) {
            $sitesQuery->whereIn('site_id', $this->getVisibleSiteIds());
        }

        $sites = $sitesQuery->get();
        $comptable = Auth::user();

        return view('factures.create', compact('facture', 'sites', 'comptable'));
    }

    /**
     * Mise à jour d'une facture
     */
    public function update(Request $request, Facture $facture)
    {
        $this->ensureFactureAllowed($facture);

        $request->validate([
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date',
            'montant_facture' => 'required|numeric',
            'statut' => 'nullable|string',
            'site_id' => 'required|exists:sites,site_id',
            'photo_facture' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
            'collecte_ids' => 'nullable|array',
            'collecte_ids.*' => 'exists:collectes,collecte_id',
        ]);

        $data = $request->only(['date_facture', 'date_echeance', 'montant_facture', 'statut', 'site_id']);
        $data['comptable_id'] = auth()->id();

        // Gestion du fichier (image ou PDF)
        if ($request->hasFile('photo_facture')) {
            if ($facture->photo_facture && Storage::disk('public')->exists($facture->photo_facture)) {
                Storage::disk('public')->delete($facture->photo_facture);
            }
            $file = $request->file('photo_facture');
            $filename = \Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('factures', $filename, 'public');
            $data['photo_facture'] = $path;
        }

        $facture->update($data);

        // Mise à jour des collectes associées avec UUID
        if ($request->has('collecte_ids')) {
            $syncData = collect($request->collecte_ids)->mapWithKeys(function ($collecteId) {
                return [$collecteId => ['factureCollecte_id' => (string) \Str::uuid()]];
            })->toArray();

            $facture->collectes()->sync($syncData);
        } else {
            // Si aucune collecte sélectionnée, on vide la relation
            $facture->collectes()->detach();
        }

        ComptabiliteService::recordFacture($facture);

        return redirect()->route('factures.index')->with('success', 'Facture mise à jour avec succès.');
    }

    /**
     * Affichage d'une facture
     */
    public function show(Facture $facture)
    {
        $this->ensureFactureAllowed($facture);

        return view('factures.show', compact('facture'));
    }

    /**
     * Suppression d'une facture
     */
    public function destroy(Facture $facture)
    {
        $this->ensureFactureAllowed($facture);

        // Supprimer le fichier si existant
        if ($facture->photo_facture && Storage::disk('public')->exists($facture->photo_facture)) {
            Storage::disk('public')->delete($facture->photo_facture);
        }

        $facture->collectes()->detach(); // retirer les relations
        ComptabiliteService::deleteEcrituresFor('facture', $facture->facture_id);
        $facture->delete();

        return redirect()->route('factures.index')->with('success', 'Facture supprimée avec succès.');
    }

    /**
     * API pour récupérer les collectes d'un site
     */
    public function getCollectesBySite(Request $request, $siteId)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Responsable site')) {
            if (!in_array($siteId, $this->getVisibleSiteIds(), true)) {
                abort(403);
            }
        }

        // Pour la création : collectes non facturées
        $query = Collecte::with(['typeDechet'])
            ->where('site_id', $siteId)
            ->whereDoesntHave('factures');

        // Pour l'édition : inclure aussi les collectes de cette facture
        if ($request->has('facture_id') && $request->facture_id) {
            $factureId = $request->facture_id;
            $query->orWhere(function ($q) use ($siteId, $factureId) {
                $q->where('site_id', $siteId)
                    ->whereHas('factures', function ($fq) use ($factureId) {
                        $fq->where('factures.facture_id', $factureId);
                    });
            });
        }

        $collectes = $query->get()->map(function ($collecte) {
            return [
                'collecte_id' => $collecte->collecte_id,
                'date_collecte' => $collecte->date_collecte,
                'poids' => $collecte->poids,
                'type_dechet' => $collecte->typeDechet->libelle ?? 'N/A'
            ];
        });

        return response()->json($collectes);
    }
}
