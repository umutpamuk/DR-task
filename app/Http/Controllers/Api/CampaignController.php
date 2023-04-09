<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Campaing\CampaignService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignController extends Controller
{
    /**
     * @param CampaignService $campaignService
     */
    public function __construct(
       public CampaignService $campaignService
    )
    {}

    /**
     * @return AnonymousResourceCollection
     */
    public function index() : AnonymousResourceCollection
    {
        return $this->campaignService->getActiveCampaign();
    }
}
