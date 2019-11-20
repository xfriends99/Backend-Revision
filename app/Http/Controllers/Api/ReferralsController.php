<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Users\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\JsonApiResponse;
use Exception;

class ReferralsController extends Controller
{
    use JsonApiResponse;

    /** @var referralService */
    protected $referralService;

    public function __construct(
        ReferralService $referralService
    ) {
        $this->referralService = $referralService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'required|email',
        ]);

        /** @var User $user */
        $user = $request->user();

        try {
            $this->referralService->sendInvitation($user, $request->emails);
        } catch (Exception $exception) {            
            logger($exception->getMessage());
            return self::errorResponse('Error enviando la invitación.', 500);
        }

        return self::success(['message' => 'Invitación enviada correctamente.']);        

    }   
}
