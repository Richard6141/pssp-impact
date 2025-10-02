<?php

namespace App\Http\Controllers;

use App\Models\Validation;
use App\Models\Collecte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ValidationController extends Controller
{
    /**
     * Afficher la liste des collectes et leurs validations.
     */
    public function index()
    {
        $collectes = Collecte::with(['site', 'validation.validator'])
            ->latest()
            ->paginate(10);

        return view('validations.index', compact('collectes'));
    }

    /**
     * Afficher le formulaire de validation pour une collecte.
     */
    public function create($collecte_id)
    {
        $collecte = Collecte::with('site')->findOrFail($collecte_id);

        if ($collecte->validation) {
            return redirect()->route('validations.index')
                ->with('warning', 'Cette collecte a déjà été validée.');
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
            'type_validation' => 'required|string|max:50',
            'commentaire' => 'nullable|string',
            'signature' => 'required|string', // base64 obligatoire
        ]);

        // Vérifier si la collecte n'est pas déjà validée
        $collecte = Collecte::findOrFail($request->collecte_id);
        if ($collecte->validation) {
            return redirect()->route('validations.index')
                ->with('warning', 'Cette collecte a déjà été validée.');
        }

        try {
            // Sauvegarder la signature (base64 → image)
            $signatureData = str_replace('data:image/png;base64,', '', $request->signature);
            $signatureData = str_replace(' ', '+', $signatureData);
            $signaturePath = 'signatures/' . Str::uuid() . '.png';

            if (!Storage::disk('public')->exists('signatures')) {
                Storage::disk('public')->makeDirectory('signatures');
            }

            Storage::disk('public')->put($signaturePath, base64_decode($signatureData));

            // Créer la validation
            Validation::create([
                'validation_id' => Str::uuid(),
                'collecte_id' => $request->collecte_id,
                'validated_by' => auth()->user()->user_id,
                'type_validation' => $request->type_validation,
                'date_validation' => now(),
                'commentaire' => $request->commentaire,
                'signature' => $signaturePath,
            ]);

            // ✅ Mettre à jour la collecte (signature_responsable_site passe à true)
            $collecte->update([
                'signature_responsable_site' => true,
                'statut' => 'en_attente',
            ]);

            return redirect()->route('validations.index')
                ->with('success', 'Collecte validée avec succès.');
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
     * Afficher les détails d'une validation.
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
            ->with('success', 'Validation supprimée avec succès.');
    }
}
