<?php

namespace App\Services\Campaing;

use App\Repository\Campaign\CampaignRepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignService implements CampaignServiceInterface
{
    /**
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(
        public CampaignRepositoryInterface $campaignRepository
    ) {}

    /**
     * @return AnonymousResourceCollection
     */
    public function getActiveCampaign(): AnonymousResourceCollection
    {
       return $this->campaignRepository->getActiveCampaign();
    }

}
