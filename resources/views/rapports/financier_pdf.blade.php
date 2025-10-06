<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Financier</title>
    <style>
    @page {
        margin: 20mm;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
        color: #333;
        line-height: 1.4;
    }

    .header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 3px solid #4154f1;
        padding-bottom: 20px;
    }

    .header h1 {
        color: #4154f1;
        font-size: 24pt;
        margin: 0 0 10px 0;
    }

    .header .period {
        font-size: 12pt;
        color: #666;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #f8f9fa;
        border-left: 4px solid #4154f1;
        padding: 15px;
        border-radius: 5px;
    }

    .stat-card.success {
        border-left-color: #2eca6a;
    }

    .stat-card.warning {
        border-left-color: #ff771d;
    }

    .stat-card.danger {
        border-left-color: #ff5757;
    }

    .stat-card .label {
        font-size: 10pt;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .stat-card .value {
        font-size: 18pt;
        font-weight: bold;
        color: #012970;
    }

    .section {
        margin: 30px 0;
        page-break-inside: avoid;
    }

    .section-title {
        font-size: 14pt;
        font-weight: bold;
        color: #4154f1;
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-bottom: 2px solid #e9ecef;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    table thead {
        background: #4154f1;
        color: white;
    }

    table th {
        padding: 10px;
        text-align: left;
        font-weight: bold;
        font-size: 10pt;
    }

    table td {
        padding: 8px 10px;
        border-bottom: 1px solid #e9ecef;
        font-size: 10pt;
    }

    table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 9pt;
        font-weight: bold;
    }

    .badge-success {
        background: #d1f2eb;
        color: #0a6847;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
    }

    .badge-danger {
        background: #f8d7da;
        color: #721c24;
    }

    .badge-info {
        background: #d1ecf1;
        color: #0c5460;
    }

    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 9pt;
        color: #666;
        padding: 10px;
        border-top: 1px solid #e9ecef;
    }

    .summary-box {
        background: #e8f0fe;
        padding: 15px;
        border-radius: 5px;
        margin: 20px 0;
    }

    .summary-box h3 {
        margin: 0 0 10px 0;
        color: #4154f1;
        font-size: 12pt;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        border-bottom: 1px dotted #ccc;
    }

    .summary-item:last-child {
        border-bottom: none;
        font-weight: bold;
        font-size: 11pt;
        margin-top: 5px;
        padding-top: 10px;
        border-top: 2px solid #4154f1;
    }
    </style>
</head>

<body>
    <div class="header">
        <h1>RAPPORT FINANCIER</h1>
        <div class="period">
            Période: {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }}
            au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        </div>
        <div style="margin-top: 10px; font-size: 10pt; color: #999;">
            Généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="label">Revenu Total</div>
            <div class="value">{{ number_format($stats['revenu_total'], 0, ',', ' ') }} FCFA</div>
        </div>

        <div class="stat-card">
            <div class="label">Total Factures</div>
            <div class="value">{{ number_format($stats['total_factures']) }}</div>
        </div>

        <div class="stat-card warning">
            <div class="label">Paiements Reçus</div>
            <div class="value">{{ number_format($stats['montant_paiements'], 0, ',', ' ') }} FCFA</div>
        </div>

        <div class="stat-card danger">
            <div class="label">Montant Impayés</div>
            <div class="value">{{ number_format($stats['montant_impayes'], 0, ',', ' ') }} FCFA</div>
        </div>
    </div>

    <!-- Résumé financier -->
    <div class="summary-box">
        <h3>Résumé Financier</h3>
        <div class="summary-item">
            <span>Total Facturé:</span>
            <span>{{ number_format($stats['montant_facture'], 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="summary-item">
            <span>Total Payé:</span>
            <span>{{ number_format($stats['montant_paiements'], 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="summary-item">
            <span>Reste à Encaisser:</span>
            <span>{{ number_format($stats['montant_impayes'], 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="summary-item">
            <span>Taux de Recouvrement:</span>
            <span>{{ $stats['montant_facture'] > 0 ? round(($stats['montant_paiements'] / $stats['montant_facture']) * 100, 1) : 0 }}%</span>
        </div>
    </div>

    <!-- Top Sites par Revenus -->
    <div class="section">
        <h2 class="section-title">Top 5 Sites par Revenus</h2>
        <table>
            <thead>
                <tr>
                    <th>Site</th>
                    <th class="text-center">Nombre Factures</th>
                    <th class="text-right">Montant Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topSites as $site)
                <tr>
                    <td>{{ $site->site_name }}</td>
                    <td class="text-center">{{ $site->nombre_factures }}</td>
                    <td class="text-right">{{ number_format($site->montant_total, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Liste des factures -->
    <div class="section" style="page-break-before: always;">
        <h2 class="section-title">Détail des Factures ({{ $factures->total() }} factures)</h2>
        <table>
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Date</th>
                    <th>Site</th>
                    <th class="text-right">Montant</th>
                    <th class="text-right">Payé</th>
                    <th class="text-right">Reste</th>
                    <th class="text-center">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factures as $facture)
                @php
                $montantPaye = $facture->paiements->sum('montant');
                $reste = $facture->montant_facture - $montantPaye;
                @endphp
                <tr>
                    <td>{{ $facture->numero_facture }}</td>
                    <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                    <td>{{ $facture->site->site_name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($facture->montant_facture, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($montantPaye, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($reste, 0, ',', ' ') }}</td>
                    <td class="text-center">
                        <span
                            class="badge badge-{{ $facture->statut == 'payee' ? 'success' : ($facture->statut == 'en_attente' ? 'warning' : 'info') }}">
                            {{ ucfirst(str_replace('_', ' ', $facture->statut)) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Évolution mensuelle -->
    <div class="section" style="page-break-before: always;">
        <h2 class="section-title">Évolution Mensuelle</h2>
        <table>
            <thead>
                <tr>
                    <th>Mois</th>
                    <th class="text-right">Montant Factures</th>
                    <th class="text-right">Montant Paiements</th>
                    <th class="text-right">Écart</th>
                </tr>
            </thead>
            <tbody>
                @foreach($evolutionMensuelle as $mois)
                @php
                $ecart = $mois['montant_paiements'] - $mois['montant_factures'];
                @endphp
                <tr>
                    <td>{{ $mois['mois'] }}</td>
                    <td class="text-right">{{ number_format($mois['montant_factures'], 0, ',', ' ') }} FCFA</td>
                    <td class="text-right">{{ number_format($mois['montant_paiements'], 0, ',', ' ') }} FCFA</td>
                    <td class="text-right" style="color: {{ $ecart >= 0 ? '#2eca6a' : '#ff5757' }}">
                        {{ number_format($ecart, 0, ',', ' ') }} FCFA
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Document confidentiel - Rapport Financier - Page <span class="pagenum"></span></p>
    </div>
</body>

</html>