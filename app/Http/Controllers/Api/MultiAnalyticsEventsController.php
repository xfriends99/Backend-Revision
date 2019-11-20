<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Cloud\MultiAnalyticsFactory;
use App\Traits\JsonApiResponse;
use Illuminate\Http\Request;

class MultiAnalyticsEventsController extends Controller
{
    use JsonApiResponse;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (MultiAnalyticsFactory::trackUser($user, $request->get('event'), MultiAnalyticsFactory::ACTIVECAMPAIGN)) {
            return self::success(['message' => 'Event registered']);
        } else {
            return self::errorResponse("Event not registered", 500);
        }
    }
}
