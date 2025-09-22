<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    /**
     * Liste des paiements
     */
    public function index()
    {
        $paiements = Paiement::with('facture')->latest()->paginate(10);
        return view('paiements.index', compact('paiements'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $factures = Facture::all();
        return view('paiements.create', compact('factures'));
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $request->validate([
            'facture_id' => 'required|uuid|exists:factures,facture_id',
            'montant' => 'required|numeric|min:0',
            'mode_paiement' => 'required|string|max:50',
            'paiement_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $data = $request->only(['facture_id', 'montant', 'mode_paiement']);
        $data['paiement_id'] = Str::uuid();

        // Générer automatiquement la référence (par ex. PAY-20250919-XXXXX)
        $data['reference'] = 'PAY-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

        // Date du jour
        $data['date_paiement'] = now();

        // Numéro de paiement auto incrémenté par année
        $numero_paiement = 'PAIE-' . date('Y') . '-' . str_pad(
            Paiement::whereYear('created_at', date('Y'))->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
        $data['numero_paiement'] = $numero_paiement;

        if ($request->hasFile('paiement_photo')) {
            $data['paiement_photo'] = $request->file('paiement_photo')->store('paiements', 'public');
        }

        // 👉 Enregistrer le paiement
        $paiement = Paiement::create($data);

        // 👉 Mettre à jour le statut de la facture
        $facture = Facture::findOrFail($request->facture_id);

        $facture->statut = 'payée';

        $facture->save();

        return redirect()->route('factures.index')->with('success', 'Paiement enregistré et facture mise à jour.');
    }



    /**
     * Afficher un paiement
     */
    public function show(Paiement $paiement)
    {
        return view('paiements.show', compact('paiement'));
    }

    /**
     * Formulaire d’édition
     */
    public function edit(Paiement $paiement)
    {
        $factures = Facture::all();
        return view('paiements.edit', compact('paiement', 'factures'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Paiement $paiement)
    {
        // Vérifier que le paiement n'est ni validé ni annulé
        if (in_array($paiement->statut, ['validé', 'annulé'])) {
            return redirect()->route('paiements.index')
                ->with('warning', 'Impossible de modifier un paiement déjà validé ou annulé.');
        }

        $request->validate([
            'facture_id' => 'required|uuid|exists:factures,facture_id',
            'montant' => 'required|numeric|min:0',
            'mode_paiement' => 'required|string|max:50',
            'paiement_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $data = $request->only(['facture_id', 'montant', 'mode_paiement', 'reference', 'date_paiement']);

        if ($request->hasFile('paiement_photo')) {
            // Supprimer l’ancienne photo si elle existe
            if ($paiement->paiement_photo) {
                Storage::disk('public')->delete($paiement->paiement_photo);
            }
            $data['paiement_photo'] = $request->file('paiement_photo')->store('paiements', 'public');
        }

        $paiement->update($data);

        return redirect()->route('paiements.index')
            ->with('success', 'Paiement mis à jour avec succès.');
    }




    /**
     * Suppression
     */
    public function destroy(Paiement $paiement)
    {
        // Vérifier si le paiement est validé ou annulé
        if (in_array($paiement->statut, ['validé', 'annulé'])) {
            return redirect()->route('paiements.index')
                ->with('warning', 'Impossible de supprimer un paiement validé ou annulé.');
        }

        // Supprimer la preuve si elle existe
        if ($paiement->paiement_photo) {
            Storage::disk('public')->delete($paiement->paiement_photo);
        }

        $paiement->delete();

        return redirect()->route('paiements.index')
            ->with('success', 'Paiement supprimé avec succès.');
    }


    /**
     * Valider un paiement
     */
    public function valider(Paiement $paiement)
    {
        $paiement->update(['statut' => 'validé']);
        return back()->with('success', 'Paiement validé avec succès.');
    }

    /**
     * Annuler un paiement
     */
    public function annuler(Paiement $paiement)
    {
        $paiement->update(['statut' => 'annulé']);
        return back()->with('warning', 'Paiement annulé.');
    }
}
