<?php

namespace App\Providers;

use App\Repository\Campaign\CampaignRepository;
use App\Repository\Campaign\CampaignRepositoryInterface;
use App\Services\Campaing\CampaignService;
use App\Services\Campaing\CampaignServiceInterface;
use Illuminate\Support\ServiceProvider;

class MyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->bind(CampaignServiceInterface::class, CampaignService::class);
        app()->bind(CampaignRepositoryInterface::class, CampaignRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
