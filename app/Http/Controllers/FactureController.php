<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Facture;
use App\Models\Collecte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FactureController extends Controller
{
    /**
     * Liste des factures
     */
    public function index()
    {
        $factures = Facture::with(['site', 'comptable'])->latest()->paginate(10);
        return view('factures.index', compact('factures'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $sites = Site::all();
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
            'montant_facture' => 'required|numeric',
            'site_id' => 'required|exists:sites,site_id',
            'photo_facture' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // max 5 Mo
            'collecte_ids' => 'nullable|array',
            'collecte_ids.*' => 'exists:collectes,collecte_id',
        ]);
        $numero_facture = 'FAC-' . date('Y') . '-' . str_pad(
            Facture::whereYear('created_at', date('Y'))->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        // Création de la facture
        $facture = new Facture();
        $facture->date_facture = $request->date_facture;
        $facture->numero_facture = $numero_facture;
        $facture->montant_facture = $request->montant_facture;
        $facture->site_id = $request->site_id;
        $facture->comptable_id = auth()->user()->user_id; // utilisateur connecté
        $facture->save();

        // Gestion du fichier
        if ($request->hasFile('photo_facture')) {
            $path = $request->file('photo_facture')->store('factures', 'public');
            $facture->photo_facture = $path;
            $facture->save();
        }

        // Association des collectes avec UUID pour la pivot table
        if ($request->has('collecte_ids')) {
            $syncData = collect($request->collecte_ids)->mapWithKeys(function ($collecteId) {
                return [$collecteId => ['factureCollecte_id' => (string) \Str::uuid()]];
            })->toArray();

            $facture->collectes()->sync($syncData);
        }

        return redirect()->route('factures.index')->with('success', 'Facture ajoutée avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Facture $facture)
    {
        $sites = Site::all();
        $comptable = Auth::user();

        return view('factures.create', compact('facture', 'sites', 'comptable'));
    }

    /**
     * Mise à jour d'une facture
     */
    public function update(Request $request, Facture $facture)
    {
        $request->validate([
            'date_facture' => 'required|date',
            'montant_facture' => 'required|numeric',
            'statut' => 'nullable|string',
            'site_id' => 'required|exists:sites,site_id',
            'photo_facture' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
            'collecte_ids' => 'nullable|array',
            'collecte_ids.*' => 'exists:collectes,collecte_id',
        ]);

        $data = $request->only(['date_facture', 'montant_facture', 'statut', 'site_id']);
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

        return redirect()->route('factures.index')->with('success', 'Facture mise à jour avec succès.');
    }

    /**
     * Affichage d'une facture
     */
    public function show(Facture $facture)
    {
        return view('factures.show', compact('facture'));
    }

    /**
     * Suppression d'une facture
     */
    public function destroy(Facture $facture)
    {
        // Supprimer le fichier si existant
        if ($facture->photo_facture && Storage::disk('public')->exists($facture->photo_facture)) {
            Storage::disk('public')->delete($facture->photo_facture);
        }

        $facture->collectes()->detach(); // retirer les relations
        $facture->delete();

        return redirect()->route('factures.index')->with('success', 'Facture supprimée avec succès.');
    }

    /**
     * API pour récupérer les collectes d'un site
     */
    public function getCollectesBySite(Request $request, $siteId)
    {
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
