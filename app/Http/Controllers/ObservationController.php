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

    private function getResponsableSiteIds(): array
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        $siteIds = $user->sites()->pluck('sites.site_id')->all();
        $responsableSiteIds = Site::where('responsable', $user->user_id)->pluck('site_id')->all();
        $siteIds = array_merge($siteIds, $responsableSiteIds);

        if (!empty($user->site_id)) {
            $siteIds[] = $user->site_id;
        }

        return array_values(array_unique(array_filter($siteIds)));
    }

    private function applyObservationVisibility($query)
    {
        if ($this->isAgentCollecte()) {
            $query->where('user_id', auth()->user()->user_id);
        } elseif ($this->isResponsableSite()) {
            $query->whereIn('site_id', $this->getResponsableSiteIds());
        }

        return $query;
    }

    private function ensureObservationAllowed(Observation $observation, bool $forWrite = false): void
    {
        if ($this->isAgentCollecte() && $observation->user_id !== auth()->user()->user_id) {
            abort(403);
        }

        if ($this->isResponsableSite()) {
            if (!in_array($observation->site_id, $this->getResponsableSiteIds(), true)) {
                abort(403);
            }

            if ($forWrite && $observation->user_id !== auth()->user()->user_id) {
                abort(403, "Vous ne pouvez modifier que vos propres observations.");
            }
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
        $sitesQuery = Site::query();
        if ($this->isResponsableSite()) {
            $sitesQuery->whereIn('site_id', $this->getResponsableSiteIds());
        }
        $sites = $sitesQuery->get();
        return view('observations.create', compact('sites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'site_id' => 'required|uuid|exists:sites,site_id',
            'contenu' => 'required|string',
            'date_obs' => 'required|date',
        ]);

        if ($this->isResponsableSite() && !in_array($request->site_id, $this->getResponsableSiteIds(), true)) {
            abort(403);
        }

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
        $this->ensureObservationAllowed($observation, true);
        $sites = Site::all();
        return view('observations.edit', compact('observation', 'sites'));
    }

    public function update(Request $request, Observation $observation)
    {
        $this->ensureObservationAllowed($observation, true);

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
