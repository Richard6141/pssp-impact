<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 12px; }
        .header { text-align: center; margin-bottom: 12px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 4px; }
        .subtitle { font-size: 12px; color: #555; }
        .meta { margin-top: 10px; margin-bottom: 12px; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 4px 0; }
        .box { border: 1px solid #cfcfcf; border-radius: 4px; padding: 10px; margin-bottom: 12px; }
        .line { margin: 4px 0; }
        .line strong { display: inline-block; min-width: 170px; }
        .amount { font-size: 18px; font-weight: bold; color: #0d6efd; margin-top: 8px; }
        .footer { margin-top: 24px; font-size: 11px; color: #666; }
        .sign { margin-top: 28px; width: 100%; }
        .sign td { width: 50%; vertical-align: top; }
        .sign-box { border-top: 1px solid #999; width: 70%; margin-top: 40px; padding-top: 4px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">RECU DE PAIEMENT</div>
        <div class="subtitle">Plateforme PSSP IMPACT+</div>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td><strong>Recu N° :</strong> {{ $receiptNumber }}</td>
                <td style="text-align:right;"><strong>Date d'emission :</strong> {{ $generatedAt->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="line"><strong>Reference paiement :</strong> {{ $paiement->reference ?? '-' }}</div>
        <div class="line"><strong>Numero paiement :</strong> {{ $paiement->numero_paiement ?? '-' }}</div>
        <div class="line"><strong>Date paiement :</strong> {{ optional($paiement->date_paiement)->format('d/m/Y') }}</div>
        <div class="line"><strong>Mode de paiement :</strong> {{ ucfirst($paiement->mode_paiement ?? '-') }}</div>
    </div>

    <div class="box">
        <div class="line"><strong>Facture :</strong> {{ $paiement->facture->numero_facture ?? '-' }}</div>
        <div class="line"><strong>Site :</strong> {{ $paiement->facture?->site?->site_name ?? '-' }}</div>
        <div class="line"><strong>Montant facture :</strong> {{ number_format((float) ($paiement->facture->montant_facture ?? 0), 2, ',', ' ') }} FCFA</div>
        <div class="line"><strong>Montant recu :</strong></div>
        <div class="amount">{{ number_format((float) $paiement->montant, 2, ',', ' ') }} FCFA</div>
    </div>

    <table class="sign">
        <tr>
            <td>
                <div>Responsable du site</div>
                <div class="sign-box">Nom et signature</div>
            </td>
            <td style="text-align:right;">
                <div style="text-align:left; margin-left:auto; width:70%;">Comptable</div>
                <div class="sign-box" style="margin-left:auto;">Nom et signature</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Ce recu est genere automatiquement apres validation comptable du paiement.
    </div>
</body>
</html>
