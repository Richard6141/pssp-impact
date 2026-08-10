<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des Sites</title>
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
        <h1>RAPPORT DES SITES</h1>
        <div class="period">
            Période: {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} 
            au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        </div>
        <div style="margin-top: 10px; font-size: 10pt; color: #999;">
            Généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <!-- Stats Générales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total Sites</div>
            <div class="value">{{ number_format($statsGenerales['total_sites'], 0, ',', ' ') }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Sites Actifs (cette période)</div>
            <div class="value">{{ number_format($statsGenerales['sites_actifs'], 0, ',', ' ') }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Moyenne Collectes/Site</div>
            <div class="value">{{ number_format($statsGenerales['moyenne_collectes_par_site'], 1, ',', ' ') }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Moyenne Poids/Site</div>
            <div class="value">{{ \App\Models\Collecte::formatPoids($statsGenerales['moyenne_poids_par_site']) }} kg</div>
        </div>
    </div>

    <!-- Répartition par Département -->
    <div class="section">
        <h2 class="section-title">Répartition par Département</h2>
        <table>
            <thead>
                <tr>
                    <th>Département</th>
                    <th class="text-right">Nombre de sites</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repartitionDepartement as $dept => $count)
                <tr>
                    <td>{{ $dept }}</td>
                    <td class="text-right">{{ $count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Top Sites -->
    <div class="section">
        <h2 class="section-title">Top 10 Sites (par nombre de collectes)</h2>
        <table>
            <thead>
                <tr>
                    <th>Site</th>
                    <th class="text-right">Collectes</th>
                    <th class="text-right">Poids (kg)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topSites as $site)
                <tr>
                    <td>{{ $site->site_name }}</td>
                    <td class="text-right">{{ $site->total_collectes }}</td>
                    <td class="text-right">{{ \App\Models\Collecte::formatPoids($site->poids_total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Sites avec Incidents -->
    @if(count($sitesAvecIncidents) > 0)
    <div class="section">
        <h2 class="section-title">Sites avec Incidents Signalés</h2>
        <table>
            <thead>
                <tr>
                    <th>Site</th>
                    <th class="text-right">Nombre d'Incidents</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sitesAvecIncidents as $site)
                <tr>
                    <td>{{ $site->site_name }}</td>
                    <td class="text-right">{{ $site->total_incidents }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Document confidentiel - Rapport Sites</p>
    </div>
</body>
</html>
