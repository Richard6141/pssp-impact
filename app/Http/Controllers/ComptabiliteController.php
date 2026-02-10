<?php

namespace App\Http\Controllers;

use App\Models\EcritureComptable;
use App\Services\ComptabiliteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Site;
use App\Models\User;
use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;

class ComptabiliteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:rapports.financier');
    }

    public function journal(Request $request)
    {
        ComptabiliteService::backfillIfEmpty();

        $query = EcritureComptable::with('user')->orderBy('date_ecriture', 'desc');

        if ($request->filled('type_piece')) {
            $query->where('type_piece', $request->type_piece);
        }
        if ($request->filled('compte')) {
            $compte = $request->compte;
            $query->where(function ($q) use ($compte) {
                $q->where('compte_debit', $compte)->orWhere('compte_credit', $compte);
            });
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_ecriture', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_ecriture', '<=', $request->date_fin);
        }

        $ecritures = $query->paginate(20);

        return view('comptabilite.journal', compact('ecritures'));
    }

    public function export(Request $request)
    {
        ComptabiliteService::backfillIfEmpty();
        $query = $this->exportQuery($request);
        $rows = $query->get();

        $factureIds = $rows->pluck('facture_id')->filter()->unique()->values();
        $paiementsByFacture = Paiement::whereIn('facture_id', $factureIds)
            ->orderBy('date_paiement', 'desc')
            ->get()
            ->groupBy('facture_id');

        $sites = Site::select('site_id', 'site_name')->orderBy('site_name')->get();
        $comptables = User::role('Comptable')->select('user_id', 'firstname', 'lastname')->get();
        $statuts = ['en attente', 'payee', 'payée', 'impayee', 'impayée', 'envoyee', 'envoyée'];

        return view('comptabilite.export', compact('rows', 'sites', 'comptables', 'statuts', 'paiementsByFacture'));
    }

    public function exportCsv(Request $request)
    {
        $rows = $this->exportQuery($request)->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="export_comptable.csv"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'numero_facture', 'date_facture', 'montant_facture', 'statut_facture',
                'site_name', 'firstname', 'lastname', 'numero_paiement', 'montant_paye',
                'date_paiement', 'mode_paiement', 'statut_paiement', 'solde_restant'
            ]);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->numero_facture,
                    $row->date_facture,
                    $row->montant_facture,
                    $row->statut_facture,
                    $row->site_name,
                    $row->firstname,
                    $row->lastname,
                    $row->numero_paiement,
                    $row->montant_paye,
                    $row->date_paiement,
                    $row->mode_paiement,
                    $row->statut_paiement,
                    $row->solde_restant,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel(Request $request)
    {
        $rows = $this->exportQuery($request)->get();

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="export_comptable.xlsx"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'numero_facture', 'date_facture', 'montant_facture', 'statut_facture',
                'site_name', 'firstname', 'lastname', 'numero_paiement', 'montant_paye',
                'date_paiement', 'mode_paiement', 'statut_paiement', 'solde_restant'
            ]);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->numero_facture,
                    $row->date_facture,
                    $row->montant_facture,
                    $row->statut_facture,
                    $row->site_name,
                    $row->firstname,
                    $row->lastname,
                    $row->numero_paiement,
                    $row->montant_paye,
                    $row->date_paiement,
                    $row->mode_paiement,
                    $row->statut_paiement,
                    $row->solde_restant,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function journalPdf(Request $request)
    {
        ComptabiliteService::backfillIfEmpty();

        $query = EcritureComptable::with('user')->orderBy('date_ecriture', 'desc');

        if ($request->filled('type_piece')) {
            $query->where('type_piece', $request->type_piece);
        }
        if ($request->filled('compte')) {
            $compte = $request->compte;
            $query->where(function ($q) use ($compte) {
                $q->where('compte_debit', $compte)->orWhere('compte_credit', $compte);
            });
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_ecriture', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_ecriture', '<=', $request->date_fin);
        }

        $ecritures = $query->get();

        $pdf = Pdf::loadView('comptabilite.journal_pdf', compact('ecritures'));
        return $pdf->download('journal_comptable_' . now()->format('Y-m-d') . '.pdf');
    }

    protected function exportQuery(Request $request)
    {
        $paiementsAgg = DB::table('paiements')
            ->select([
                'facture_id',
                DB::raw('SUM(montant) as montant_paye'),
                DB::raw('MAX(date_paiement) as date_paiement'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(numero_paiement ORDER BY date_paiement DESC), \',\', 1) as numero_paiement'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(mode_paiement ORDER BY date_paiement DESC), \',\', 1) as mode_paiement'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(statut ORDER BY date_paiement DESC), \',\', 1) as statut_paiement'),
            ])
            ->groupBy('facture_id');

        $query = DB::table('factures as f')
            ->leftJoin('sites as s', 'f.site_id', '=', 's.site_id')
            ->leftJoin('users as u', 'f.comptable_id', '=', 'u.user_id')
            ->leftJoinSub($paiementsAgg, 'p', function ($join) {
                $join->on('f.facture_id', '=', 'p.facture_id');
            })
            ->select([
                'f.facture_id as facture_id',
                'f.numero_facture as numero_facture',
                'f.date_facture as date_facture',
                'f.montant_facture as montant_facture',
                'f.statut as statut_facture',
                's.site_name as site_name',
                'u.firstname as firstname',
                'u.lastname as lastname',
                'p.numero_paiement as numero_paiement',
                'p.montant_paye as montant_paye',
                'p.date_paiement as date_paiement',
                'p.mode_paiement as mode_paiement',
                'p.statut_paiement as statut_paiement',
                DB::raw('f.montant_facture - COALESCE(p.montant_paye, 0) as solde_restant'),
            ]);

        if ($request->filled('site_id')) {
            $query->where('f.site_id', $request->site_id);
        }
        if ($request->filled('comptable_id')) {
            $query->where('f.comptable_id', $request->comptable_id);
        }
        if ($request->filled('statut_facture')) {
            $query->where('f.statut', $request->statut_facture);
        }

        return $query;
    }
}
