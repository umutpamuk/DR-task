<?php

namespace App\Repository\Campaign;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface CampaignRepositoryInterface
{
    /**
     * @return AnonymousResourceCollection
     */
    public function getActiveCampaign() : AnonymousResourceCollection;

}
