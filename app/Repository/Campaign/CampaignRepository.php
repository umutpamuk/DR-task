<?php

namespace App\Repository\Campaign;

use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignRepository implements CampaignRepositoryInterface
{

    /**
     * @return AnonymousResourceCollection
     */
    public function getActiveCampaign(): AnonymousResourceCollection
    {
        $campaigns = Campaign::all();
        return CampaignResource::collection($campaigns);
    }

}
