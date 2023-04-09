<?php

namespace App\Services\Campaing;

use Illuminate\Http\Resources\Json\JsonResource;

interface CampaignServiceInterface
{
    public function getActiveCampaign() : JsonResource;

}
