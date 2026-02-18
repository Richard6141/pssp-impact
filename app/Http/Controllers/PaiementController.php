<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Facture;
use App\Models\Site;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ComptabiliteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    private function normalizedStatus(?string $status): string
    {
        return (string) Str::of((string) $status)
            ->replace('?', 'e')
            ->ascii()
            ->lower()
            ->trim();
    }

    private function buildReceiptNumber(Paiement $paiement): string
    {
        $year = optional($paiement->created_at)->format('Y') ?? now()->format('Y');
        $sequence = Paiement::query()
            ->whereYear('created_at', $year)
            ->where('created_at', '<=', $paiement->created_at ?? now())
            ->count();

        return 'CCP-' . str_pad((string) max(1, $sequence), 6, '0', STR_PAD_LEFT);
    }

    private function generateReceiptPdf(Paiement $paiement): string
    {
        $paiement->loadMissing(['facture.site']);

        $receiptNumber = $this->buildReceiptNumber($paiement);
        $pdf = Pdf::loadView('paiements.receipt', [
            'paiement' => $paiement,
            'receiptNumber' => $receiptNumber,
            'generatedAt' => now(),
        ])->setPaper('a4');

        $filename = 'recus/recu-' . strtolower($receiptNumber) . '-' . $paiement->paiement_id . '.pdf';

        if ($paiement->recu_comptable && Storage::disk('public')->exists($paiement->recu_comptable)) {
            Storage::disk('public')->delete($paiement->recu_comptable);
        }

        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

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
            $query->whereHas('facture', function ($q) {
                $q->whereIn('site_id', $this->getVisibleSiteIds());
            });
        }

        return $query;
    }

    private function ensurePaiementAllowed(Paiement $paiement): void
    {
        $user = auth()->user();

        if ($user->hasRole('Responsable site')) {
            $allowedSites = $this->getVisibleSiteIds();
            $siteId = optional($paiement->facture)->site_id;
            if (!$siteId || !in_array($siteId, $allowedSites, true)) {
                abort(403);
            }
        }
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
     * Liste des paiements
     */
    public function index()
    {
        $query = Paiement::with(['facture.site'])->latest();
        $this->applyVisibility($query);
        $paiements = $query->paginate(10);
        return view('paiements.index', compact('paiements'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $query = Facture::query();
        $user = auth()->user();
        if ($user->hasRole('Responsable site')) {
            $query->whereIn('site_id', $this->getVisibleSiteIds());
        }

        $factures = $query->get();
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

        $facture = Facture::findOrFail($request->facture_id);
        $this->ensureFactureAllowed($facture);

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

        // Laisser la facture en attente tant que le paiement n'est pas validé
        if ($facture->statut === 'payée') {
            $facture->statut = 'en attente';
            $facture->save();
        }

        ComptabiliteService::recordPaiement($paiement);

        return redirect()->route('factures.index')->with('success', 'Paiement enregistré et facture mise à jour.');
    }



    /**
     * Afficher un paiement
     */
    public function show(Paiement $paiement)
    {
        $paiement->load('facture.site');
        $this->ensurePaiementAllowed($paiement);

        return view('paiements.show', compact('paiement'));
    }

    /**
     * Formulaire d’édition
     */
    public function edit(Paiement $paiement)
    {
        $paiement->load('facture.site');
        $this->ensurePaiementAllowed($paiement);

        $query = Facture::query();
        $user = auth()->user();
        if ($user->hasRole('Responsable site')) {
            $query->whereIn('site_id', $this->getVisibleSiteIds());
        }

        $factures = $query->get();
        return view('paiements.edit', compact('paiement', 'factures'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Paiement $paiement)
    {
        $paiement->load('facture.site');
        $this->ensurePaiementAllowed($paiement);

        // Vérifier que le paiement n'est ni validé ni annulé
        if (in_array($paiement->statut, ['validé'])) {
            return redirect()->route('paiements.index')
                ->with('warning', 'Impossible de modifier un paiement déjà validé ou annulé.');
        }

        $request->validate([
            'facture_id' => 'required|uuid|exists:factures,facture_id',
            'montant' => 'required|numeric|min:0',
            'mode_paiement' => 'required|string|max:50',
            'paiement_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'recu_comptable' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $request->only(['facture_id', 'montant', 'mode_paiement', 'reference', 'date_paiement']);

        $facture = Facture::findOrFail($request->facture_id);
        $this->ensureFactureAllowed($facture);

        // ✅ Mettre le statut à "modifié" lors de la modification
        $data['statut'] = 'modifié';

        if ($request->hasFile('paiement_photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($paiement->paiement_photo) {
                Storage::disk('public')->delete($paiement->paiement_photo);
            }
            $data['paiement_photo'] = $request->file('paiement_photo')->store('paiements', 'public');
        }

        if ($request->hasFile('recu_comptable')) {
            if ($paiement->recu_comptable && Storage::disk('public')->exists($paiement->recu_comptable)) {
                Storage::disk('public')->delete($paiement->recu_comptable);
            }
            $data['recu_comptable'] = $request->file('recu_comptable')->store('recus', 'public');
        }

        $paiement->update($data);

        ComptabiliteService::recordPaiement($paiement);

        return redirect()->route('paiements.index')
            ->with('success', 'Paiement mis à jour avec succès.');
    }




    /**
     * Suppression
     */
    public function destroy(Paiement $paiement)
    {
        $paiement->load('facture.site');
        $this->ensurePaiementAllowed($paiement);

        // Vérifier si le paiement est validé ou annulé
        if (in_array($paiement->statut, ['validé', 'annulé'])) {
            return redirect()->route('paiements.index')
                ->with('warning', 'Impossible de supprimer un paiement validé ou annulé.');
        }

        // Supprimer la preuve si elle existe
        if ($paiement->paiement_photo) {
            Storage::disk('public')->delete($paiement->paiement_photo);
        }
        if ($paiement->recu_comptable) {
            Storage::disk('public')->delete($paiement->recu_comptable);
        }

        $paiement->delete();

        ComptabiliteService::deleteEcrituresFor('paiement', $paiement->paiement_id);

        return redirect()->route('paiements.index')
            ->with('success', 'Paiement supprimé avec succès.');
    }


    /**
     * Valider un paiement
     */
    public function valider(Request $request, Paiement $paiement)
    {
        $paiement->load('facture.site');
        $this->ensurePaiementAllowed($paiement);

        $request->validate([
            'recu_comptable' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('recu_comptable')) {
            if ($paiement->recu_comptable && Storage::disk('public')->exists($paiement->recu_comptable)) {
                Storage::disk('public')->delete($paiement->recu_comptable);
            }
            $receiptPath = $request->file('recu_comptable')->store('recus', 'public');
        } else {
            $receiptPath = $this->generateReceiptPdf($paiement);
        }

        $paiement->update([
            'statut' => 'valide',
            'recu_comptable' => $receiptPath,
        ]);
        $paiement->facture?->update(['statut' => 'payee']);
        return back()->with('success', 'Paiement valid? avec succ?s.');
    }

    /**
     * Annuler un paiement
     */
    public function annuler(Paiement $paiement)
    {
        $paiement->load('facture.site');
        $this->ensurePaiementAllowed($paiement);

        $paiement->update(['statut' => 'rejeté']);
        if ($paiement->facture && $paiement->facture->statut === 'payée') {
            $paiement->facture->update(['statut' => 'en attente']);
        }
        return back()->with('warning', 'Paiement annulé.');
    }

    public function downloadReceipt(Paiement $paiement)
    {
        $paiement->load('facture.site');
        $this->ensurePaiementAllowed($paiement);

        if ($paiement->statut === 'validé' && (!$paiement->recu_comptable || !Storage::disk('public')->exists($paiement->recu_comptable))) {
            $newPath = $this->generateReceiptPdf($paiement);
            $paiement->update(['recu_comptable' => $newPath]);
            $paiement->refresh();
        }

        if (!$paiement->recu_comptable || !Storage::disk('public')->exists($paiement->recu_comptable)) {
            return back()->with('error', 'Aucun recu disponible pour ce paiement.');
        }

        $extension = pathinfo($paiement->recu_comptable, PATHINFO_EXTENSION);
        $filename = 'Recu ' . $this->buildReceiptNumber($paiement) . '.' . $extension;

        return Storage::disk('public')->download($paiement->recu_comptable, $filename);
    }
}
