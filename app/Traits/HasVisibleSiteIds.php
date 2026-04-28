<?php

namespace App\Traits;

use App\Models\Site;
use Illuminate\Support\Facades\Cache;

trait HasVisibleSiteIds
{
    protected function getVisibleSiteIds(): array
    {
        $user = auth()->user();

        return Cache::remember(
            'visible_site_ids_' . $user->user_id,
            60,
            function () use ($user) {
                $siteIds = $user->sites()->pluck('sites.site_id')->toArray();
                $responsableSiteIds = Site::where('responsable', $user->user_id)
                    ->pluck('site_id')
                    ->toArray();

                if (!empty($user->site_id)) {
                    $siteIds[] = $user->site_id;
                }

                return array_values(array_unique(array_filter(
                    array_merge($siteIds, $responsableSiteIds)
                )));
            }
        );
    }
}
