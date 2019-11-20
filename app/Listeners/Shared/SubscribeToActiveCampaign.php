<?php

namespace App\Listeners\Shared;

use App\Models\User;
use App\Services\Cloud\ActiveCampaignService;
use App\Services\Cloud\MultiAnalyticsFactory;
use Illuminate\Auth\Events\Registered;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubscribeToActiveCampaign extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param Registered $event
     * @throws \Exception
     */
    public function handle(Registered $event)
    {
        /** @var ActiveCampaignService $activeCampaignService */
        $activeCampaignService = app(ActiveCampaignService::class);

        /** @var User $user */
        $user = $event->user;

        // Create contact and track signup event
        if ($activeCampaignService->createContact($user)) {
            MultiAnalyticsFactory::trackUser($user, 'Signup', MultiAnalyticsFactory::ACTIVECAMPAIGN);
        }
    }
}
