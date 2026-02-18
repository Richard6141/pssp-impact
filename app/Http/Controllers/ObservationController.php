<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ObservationController extends Controller
{
    private function isAgentCollecte(): bool
    {
        return auth()->user()->hasRole('Agent collecte');
    }

    private function isResponsableSite(): bool
    {
        return auth()->user()->hasRole('Responsable site');
    }

    private function applyObservationVisibility($query)
    {
        if ($this->isAgentCollecte()) {
            $query->where('user_id', auth()->user()->user_id);
        }

        return $query;
    }

    private function ensureObservationAllowed(Observation $observation): void
    {
        if ($this->isAgentCollecte() && $observation->user_id !== auth()->user()->user_id) {
            abort(403);
        }
    }

    public function index()
    {
        $observations = $this->applyObservationVisibility(
            Observation::with(['site', 'user'])
        )->latest()->paginate(15);

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

        return redirect()->route('observations.index')->with('success', 'Observation ajoutee avec succes.');
    }

    public function show(Observation $observation)
    {
        $this->ensureObservationAllowed($observation);
        return view('observations.show', compact('observation'));
    }

    public function edit(Observation $observation)
    {
        $this->ensureObservationAllowed($observation);
        $sites = Site::all();
        return view('observations.edit', compact('observation', 'sites'));
    }

    public function update(Request $request, Observation $observation)
    {
        $this->ensureObservationAllowed($observation);

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

        return redirect()->route('observations.index')->with('success', 'Observation mise a jour avec succes.');
    }

    public function destroy(Observation $observation)
    {
        if ($this->isResponsableSite()) {
            return redirect()
                ->route('observations.index')
                ->with('error', 'Suppression interdite: un responsable site ne peut pas supprimer une observation.');
        }

        $this->ensureObservationAllowed($observation);
        $observation->delete();

        return redirect()->route('observations.index')->with('success', 'Observation supprimee avec succes.');
    }

    public function trashed()
    {
        $observations = $this->applyObservationVisibility(
            Observation::onlyTrashed()->with(['site', 'user'])
        )->latest()->paginate(15);

        return view('observations.trashed', compact('observations'));
    }

    public function restore($id)
    {
        $observation = Observation::onlyTrashed()->findOrFail($id);
        $this->ensureObservationAllowed($observation);
        $observation->restore();

        return redirect()->route('observations.index')->with('success', 'Observation restauree avec succes.');
    }
}
