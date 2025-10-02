<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeDechet;
use Illuminate\Support\Str;

class TypeDechetController extends Controller
{
    // Liste
    public function index()
    {
        $types = TypeDechet::latest()->get();
        return view('type_dechets.index', compact('types'));
    }

    // Formulaire création
    public function create()
    {
        return view('type_dechets.create');
    }

    // Sauvegarde
    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:type_dechets,libelle',
            'description' => 'nullable|string',
        ]);

        TypeDechet::create([
            'type_dechet_id' => Str::uuid(),
            'libelle' => $validated['libelle'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('type_dechets.index')->with('success', 'Type de collecte ajouté avec succès.');
    }

    // Edition
    public function edit($id)
    {
        $type = TypeDechet::findOrFail($id);
        return view('type_dechets.create', compact('type'));
    }

    // Mise à jour
    public function update(Request $request, $id)
    {
        $type = TypeDechet::findOrFail($id);

        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:type_dechets,libelle,' . $type->type_dechet_id . ',type_dechet_id',
            'description' => 'nullable|string',
        ]);

        $type->update($validated);

        return redirect()->route('type_dechets.index')->with('success', 'Type de collecte mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $type = TypeDechet::findOrFail($id);

        // Vérifier si le type de déchet est utilisé dans une collecte
        if ($type->collectes()->exists()) {
            return redirect()->route('type_dechets.index')
                ->with('error', 'Impossible de supprimer ce type : il est déjà utilisé dans une collecte.');
        }

        $type->delete();

        return redirect()->route('type_dechets.index')
            ->with('success', 'Type de collecte supprimé avec succès.');
    }
}
