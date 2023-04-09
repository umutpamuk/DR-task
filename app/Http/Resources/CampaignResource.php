<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) : array
    {
        return [
            'id'                => $this->id,
            'campaign_name'     => $this->campaign_name,
            'campaign_discount' => $this->campaign_discount
        ];
    }
}
