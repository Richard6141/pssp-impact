<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FinancierExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Collection des données à exporter
     */
    public function collection()
    {
        return $this->data['factures'];
    }

    /**
     * Mapping des données pour chaque ligne
     */
    public function map($facture): array
    {
        $montantPaye = $facture->paiements->sum('montant');
        $reste = $facture->montant_facture - $montantPaye;

        return [
            $facture->numero_facture,
            $facture->date_facture->format('d/m/Y'),
            $facture->site->site_name ?? 'N/A',
            $facture->montant_facture,
            $montantPaye,
            $reste,
            ucfirst(str_replace('_', ' ', $facture->statut)),
            $facture->paiements->count(),
        ];
    }

    /**
     * En-têtes des colonnes
     */
    public function headings(): array
    {
        return [
            'Numéro Facture',
            'Date',
            'Site',
            'Montant Facture (FCFA)',
            'Montant Payé (FCFA)',
            'Reste à Payer (FCFA)',
            'Statut',
            'Nombre Paiements',
        ];
    }

    /**
     * Styles du tableau
     */
    public function styles(Worksheet $sheet)
    {
        // Titre du rapport
        $sheet->insertNewRowBefore(1, 3);
        $sheet->setCellValue('A1', 'RAPPORT FINANCIER');
        $sheet->setCellValue('A2', 'Période: ' . $this->data['dateDebut'] . ' au ' . $this->data['dateFin']);

        // Statistiques principales
        $sheet->setCellValue('A3', 'STATISTIQUES');
        $sheet->setCellValue('B3', 'Revenu Total: ' . number_format($this->data['stats']['revenu_total'], 0, ',', ' ') . ' FCFA');
        $sheet->setCellValue('D3', 'Total Factures: ' . $this->data['stats']['total_factures']);
        $sheet->setCellValue('F3', 'Impayés: ' . number_format($this->data['stats']['montant_impayes'], 0, ',', ' ') . ' FCFA');

        // Style pour le titre
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '4154f1']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ]);

        $sheet->mergeCells('A1:H1');

        // Style pour les stats
        $sheet->getStyle('A3:H3')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F0FE']
            ],
            'font' => [
                'bold' => true
            ]
        ]);

        // Style pour les en-têtes (ligne 4)
        $sheet->getStyle('A4:H4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4154f1']
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Auto-size des colonnes
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Bordures pour toutes les données
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A4:H' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        return [];
    }

    /**
     * Titre de la feuille
     */
    public function title(): string
    {
        return 'Rapport Financier';
    }
}
