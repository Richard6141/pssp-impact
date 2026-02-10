<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des Collectes</title>
    <style>
        @page { margin: 20mm; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #4154f1; padding-bottom: 20px; }
        .header h1 { color: #4154f1; font-size: 24pt; margin: 0 0 10px 0; }
        .period { font-size: 12pt; color: #666; }
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 30px; }
        .stat-card { background: #f8f9fa; border-left: 4px solid #4154f1; padding: 15px; border-radius: 5px; }
        .stat-card .label { font-size: 10pt; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .stat-card .value { font-size: 18pt; font-weight: bold; color: #012970; }
        .section { margin: 30px 0; page-break-inside: avoid; }
        .section-title { font-size: 14pt; font-weight: bold; color: #4154f1; margin-bottom: 15px; border-bottom: 2px solid #e9ecef; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table thead { background: #4154f1; color: white; }
        table th, table td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #e9ecef; font-size: 10pt; }
        table tbody tr:nth-child(even) { background: #f8f9fa; }
        .text-right { text-align: right; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9pt; color: #666; padding: 10px; border-top: 1px solid #e9ecef; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RAPPORT DES COLLECTES</h1>
        <div class="period">
            Période: {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} 
            au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        </div>
        <div style="margin-top: 10px; font-size: 10pt; color: #999;">
            Généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total Collectes</div>
            <div class="value">{{ number_format($stats['total_collectes'], 0, ',', ' ') }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Poids Total (kg)</div>
            <div class="value">{{ number_format($stats['poids_total'], 2, ',', ' ') }} kg</div>
        </div>
        <div class="stat-card">
            <div class="label">Collectes Validées</div>
            <div class="value">{{ number_format($stats['collectes_validees'], 0, ',', ' ') }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Collectes Signées</div>
            <div class="value">{{ number_format($stats['collectes_signees'], 0, ',', ' ') }}</div>
        </div>
    </div>

    <!-- Répartitions -->
    <div class="section">
        <h2 class="section-title">Répartition par Type de Déchet</h2>
        <table>
            <thead>
                <tr>
                    <th>Type de Déchet</th>
                    <th class="text-right">Nombre de collectes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repartitionTypeDechet as $type => $count)
                <tr>
                    <td>{{ $type }}</td>
                    <td class="text-right">{{ $count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Top Agents -->
    <div class="section">
        <h2 class="section-title">Top 10 Agents</h2>
        <table>
            <thead>
                <tr>
                    <th>Agent</th>
                    <th class="text-right">Collectes Réalisées</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topAgents as $agent)
                <tr>
                    <td>{{ $agent->firstname }} {{ $agent->lastname }}</td>
                    <td class="text-right">{{ $agent->total }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Liste détaillée -->
    <div class="section" style="page-break-before: always;">
        <h2 class="section-title">Liste des Collectes ({{ count($collectes) > 50 ? '50 dernières' : count($collectes) }})</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>N° Collecte</th>
                    <th>Site</th>
                    <th>Type</th>
                    <th class="text-right">Poids (kg)</th>
                    <th>Agent</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($collectes as $collecte)
                <tr>
                    <td>{{ $collecte->date_collecte->format('d/m/Y') }}</td>
                    <td>{{ $collecte->numero_collecte }}</td>
                    <td>{{ $collecte->site->site_name ?? 'N/A' }}</td>
                    <td>{{ $collecte->typeDechet->libelle ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($collecte->poids, 2, ',', ' ') }}</td>
                    <td>{{ $collecte->agent->username ?? 'N/A' }}</td>
                    <td>{{ ucfirst($collecte->statut) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Document confidentiel - Rapport Collectes</p>
    </div>
</body>
</html>
