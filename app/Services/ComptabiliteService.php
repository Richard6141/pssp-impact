<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\Paiement;
use App\Models\EcritureComptable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ComptabiliteService
{
    public static function recordFacture(Facture $facture): void
    {
        self::deleteEcrituresFor('facture', $facture->facture_id);

        $accounts = config('finance.accounts');
        $devise = config('finance.devise', 'XOF');

        $tva = (float) ($facture->tva ?? 0);
        $montantTtc = (float) ($facture->montant_ttc ?? $facture->montant_facture);

        if ($facture->montant_ht === null || $facture->montant_tva === null || $facture->montant_ttc === null) {
            if ($tva > 0) {
                $montantHt = round($montantTtc / (1 + ($tva / 100)), 4);
                $montantTva = round($montantTtc - $montantHt, 4);
            } else {
                $montantHt = $montantTtc;
                $montantTva = 0.0;
            }

            $facture->montant_ht = $montantHt;
            $facture->montant_tva = $montantTva;
            $facture->montant_ttc = $montantTtc;
            $facture->save();
        } else {
            $montantHt = (float) $facture->montant_ht;
            $montantTva = (float) $facture->montant_tva;
        }

        $userId = Auth::id() ?? $facture->comptable_id;
        $date = $facture->date_facture;

        // Écriture HT
        self::createEcriture([
            'date_ecriture' => $date,
            'numero_piece' => $facture->numero_facture,
            'type_piece' => 'facture',
            'piece_id' => $facture->facture_id,
            'compte_debit' => $accounts['client'],
            'compte_credit' => $accounts['revenu'],
            'libelle' => 'Facture ' . $facture->numero_facture,
            'montant' => $montantHt,
            'devise' => $devise,
            'user_id' => $userId,
        ]);

        // Écriture TVA si nécessaire
        if ($montantTva > 0) {
            self::createEcriture([
                'date_ecriture' => $date,
                'numero_piece' => $facture->numero_facture,
                'type_piece' => 'facture',
                'piece_id' => $facture->facture_id,
                'compte_debit' => $accounts['client'],
                'compte_credit' => $accounts['tva'],
                'libelle' => 'TVA Facture ' . $facture->numero_facture,
                'montant' => $montantTva,
                'devise' => $devise,
                'user_id' => $userId,
            ]);
        }
    }

    public static function recordPaiement(Paiement $paiement): void
    {
        self::deleteEcrituresFor('paiement', $paiement->paiement_id);

        $paiement->loadMissing('facture');
        $accounts = config('finance.accounts');
        $devise = config('finance.devise', 'XOF');
        $userId = Auth::id() ?? $paiement->facture?->comptable_id;

        self::createEcriture([
            'date_ecriture' => $paiement->date_paiement,
            'numero_piece' => $paiement->numero_paiement ?? $paiement->reference,
            'type_piece' => 'paiement',
            'piece_id' => $paiement->paiement_id,
            'compte_debit' => $accounts['banque'],
            'compte_credit' => $accounts['client'],
            'libelle' => 'Paiement ' . ($paiement->numero_paiement ?? $paiement->reference),
            'montant' => $paiement->montant,
            'devise' => $devise,
            'user_id' => $userId,
        ]);
    }

    public static function deleteEcrituresFor(string $typePiece, string $pieceId): void
    {
        EcritureComptable::where('type_piece', $typePiece)
            ->where('piece_id', $pieceId)
            ->delete();
    }

    public static function backfillIfEmpty(): void
    {
        if (EcritureComptable::count() > 0) {
            return;
        }

        Facture::withTrashed()->chunk(100, function ($factures) {
            foreach ($factures as $facture) {
                self::recordFacture($facture);
            }
        });

        Paiement::withTrashed()->chunk(100, function ($paiements) {
            foreach ($paiements as $paiement) {
                self::recordPaiement($paiement);
            }
        });
    }

    protected static function createEcriture(array $data): void
    {
        $data['ecriture_id'] = (string) Str::uuid();
        EcritureComptable::create($data);
    }
}
