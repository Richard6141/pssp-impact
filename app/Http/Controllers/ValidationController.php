<?php

namespace App\Http\Controllers;

use App\Models\Collecte;
use App\Models\Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ValidationController extends Controller
{
    private function canSignCollecte(Collecte $collecte): bool
    {
        $user = auth()->user();

        if (!$user || !$collecte->site) {
            return false;
        }

        return (string) $collecte->site->responsable === (string) $user->user_id;
    }

    private function ensureCanSignCollecte(Collecte $collecte): void
    {
        if (!$this->canSignCollecte($collecte)) {
            abort(403, "Vous n'etes pas autorise a signer cette collecte.");
        }
    }

    /**
     * Afficher la liste des collectes et leurs validations.
     */
    public function index()
    {
        $query = Collecte::with(['site', 'validation.validator'])
            ->latest()
            ->orderByDesc('date_collecte');

        $user = auth()->user();
        if ($user && $user->hasRole('Responsable site')) {
            $query->whereHas('site', function ($q) use ($user) {
                $q->where('responsable', $user->user_id);
            });
        }

        $collectes = $query->paginate(10);

        return view('validations.index', compact('collectes'));
    }

    /**
     * Afficher le formulaire de validation pour une collecte.
     */
    public function create($collecte_id)
    {
        $collecte = Collecte::with('site')->findOrFail($collecte_id);
        $this->ensureCanSignCollecte($collecte);

        if ($collecte->validation) {
            return redirect()->route('validations.index')
                ->with('warning', 'Cette collecte a deja ete validee.');
        }

        return view('validations.create', compact('collecte'));
    }

    /**
     * Enregistrer une nouvelle validation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'collecte_id' => 'required|uuid|exists:collectes,collecte_id',
            'commentaire' => 'nullable|string',
            'signature' => 'required|string',
        ]);

        $collecte = Collecte::with('site')->findOrFail($request->collecte_id);
        $this->ensureCanSignCollecte($collecte);

        if ($collecte->validation) {
            return redirect()->route('validations.index')
                ->with('warning', 'Cette collecte a deja ete validee.');
        }

        try {
            // Save signature (base64 -> image)
            $signatureData = str_replace('data:image/png;base64,', '', $request->signature);
            $signatureData = str_replace(' ', '+', $signatureData);
            $signaturePath = 'signatures/' . Str::uuid() . '.png';

            if (!Storage::disk('public')->exists('signatures')) {
                Storage::disk('public')->makeDirectory('signatures');
            }

            Storage::disk('public')->put($signaturePath, base64_decode($signatureData));

            Validation::create([
                'validation_id' => Str::uuid(),
                'collecte_id' => $request->collecte_id,
                'validated_by' => auth()->user()->user_id,
                'type_validation' => 'Partielle',
                'date_validation' => now(),
                'commentaire' => $request->commentaire,
                'signature' => $signaturePath,
            ]);

            $collecte->update([
                'signature_responsable_site' => true,
                'statut' => 'en_attente',
            ]);

            return redirect()->route('validations.index')
                ->with('success', 'Collecte validee avec succes.');
        } catch (\Exception $e) {
            if (isset($signaturePath) && Storage::disk('public')->exists($signaturePath)) {
                Storage::disk('public')->delete($signaturePath);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la validation : ' . $e->getMessage());
        }
    }

    /**
     * Afficher les details d'une validation.
     */
    public function show(Validation $validation)
    {
        $validation->load(['collecte.site', 'validator']);

        return view('validations.show', compact('validation'));
    }

    /**
     * Supprimer une validation (optionnel).
     */
    public function destroy(Validation $validation)
    {
        if ($validation->signature) {
            Storage::disk('public')->delete($validation->signature);
        }

        $validation->delete();

        return redirect()->route('validations.index')
            ->with('success', 'Validation supprimee avec succes.');
    }
}
