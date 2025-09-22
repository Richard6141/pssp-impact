<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ObservationController extends Controller
{
    public function index()
    {
        $observations = Observation::with(['site', 'user'])->latest()->paginate(15);
        return view('observations.index', compact('observations'));
    }

    public function create()
    {
        $sites = Site::all();
        return view('observations.create', compact('sites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'site_id' => 'required|uuid|exists:sites,site_id',
            'contenu' => 'required|string',
            'date_obs' => 'required|date',
        ]);

        Observation::create([
            'observation_id' => Str::uuid(),
            'site_id' => $request->site_id,
            'user_id' => auth()->user()->user_id,
            'contenu' => $request->contenu,
            'date_obs' => $request->date_obs,
        ]);

        return redirect()->route('observations.index')->with('success', 'Observation ajoutée avec succès.');
    }

    public function show(Observation $observation)
    {
        return view('observations.show', compact('observation'));
    }

    public function edit(Observation $observation)
    {
        $sites = Site::all();
        return view('observations.edit', compact('observation', 'sites'));
    }

    public function update(Request $request, Observation $observation)
    {
        $request->validate([
            'site_id' => 'required|uuid|exists:sites,site_id',
            'contenu' => 'required|string',
            'date_obs' => 'required|date',
        ]);

        $observation->update([
            'site_id' => $request->site_id,
            'contenu' => $request->contenu,
            'date_obs' => $request->date_obs,
        ]);

        return redirect()->route('observations.index')->with('success', 'Observation mise à jour avec succès.');
    }

    public function destroy(Observation $observation)
    {
        $observation->delete();
        return redirect()->route('observations.index')->with('success', 'Observation supprimée avec succès.');
    }

    public function trashed()
    {
        $observations = Observation::onlyTrashed()->with(['site', 'user'])->latest()->paginate(15);
        return view('observations.trashed', compact('observations'));
    }

    public function restore($id)
    {
        $observation = Observation::onlyTrashed()->findOrFail($id);
        $observation->restore();
        return redirect()->route('observations.index')->with('success', 'Observation restaurée avec succès.');
    }
}
