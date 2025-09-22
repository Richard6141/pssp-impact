<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Collecte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    public function index()
    {
        $incidents = Incident::with(['collecte', 'reporter'])->latest()->paginate(10);
        return view('incidents.index', compact('incidents'));
    }

    public function create()
    {
        $collectes = Collecte::all();
        return view('incidents.create', compact('collectes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'collecte_id' => 'required|exists:collectes,collecte_id',
            'description' => 'required|string',
            'date_incident' => 'required|date',
        ]);

        Incident::create([
            'collecte_id' => $request->collecte_id,
            'reported_by' => Auth::id(),
            'description' => $request->description,
            'date_incident' => $request->date_incident,
            'statut' => 'ouvert',
        ]);

        return redirect()->route('incidents.index')->with('success', 'Incident enregistré avec succès.');
    }

    public function show(Incident $incident)
    {
        return view('incidents.show', compact('incident'));
    }

    public function edit(Incident $incident)
    {
        $collectes = Collecte::all();
        return view('incidents.edit', compact('incident', 'collectes'));
    }

    public function update(Request $request, Incident $incident)
    {
        $request->validate([
            'collecte_id' => 'required|exists:collectes,collecte_id',
            'description' => 'required|string',
            'date_incident' => 'required|date',
            'statut' => 'required|string',
        ]);

        $incident->update($request->only('collecte_id', 'description', 'date_incident', 'statut'));

        return redirect()->route('incidents.index')->with('success', 'Incident mis à jour avec succès.');
    }

    public function destroy(Incident $incident)
    {
        $incident->delete();
        return redirect()->route('incidents.index')->with('success', 'Incident supprimé avec succès.');
    }
}
