<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Journal comptable</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f5f5f5; text-align: left; }
    </style>
</head>
<body>
    <h1>Journal comptable</h1>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Pièce</th>
                <th>Type</th>
                <th>Débit</th>
                <th>Crédit</th>
                <th>Libellé</th>
                <th>Montant</th>
                <th>Saisi par</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ecritures as $ecriture)
            <tr>
                <td>{{ \Carbon\Carbon::parse($ecriture->date_ecriture)->format('d/m/Y') }}</td>
                <td>{{ $ecriture->numero_piece }}</td>
                <td>{{ ucfirst($ecriture->type_piece) }}</td>
                <td>{{ $ecriture->compte_debit }}</td>
                <td>{{ $ecriture->compte_credit }}</td>
                <td>{{ $ecriture->libelle }}</td>
                <td>{{ number_format($ecriture->montant, 2, ',', ' ') }}</td>
                <td>{{ $ecriture->user?->firstname }} {{ $ecriture->user?->lastname }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8">Aucune écriture.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
