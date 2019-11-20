<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SupportRequested;
use App\Models\User;
use App\Repositories\PackageRepository;
use App\Repositories\PurchaseRepository;
use App\Services\Cloud\MultiAnalyticsFactory;
use App\Traits\JsonApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportsController extends Controller
{
    use JsonApiResponse;

    /** @var PackageRepository */
    private $packageRepository;

    /** @var PurchaseRepository */
    private $purchaseRepository;

    /**
     * SupportsController constructor.
     * @param PackageRepository $packageRepository
     * @param PurchaseRepository $purchaseRepository
     */
    public function __construct(PackageRepository $packageRepository, PurchaseRepository $purchaseRepository)
    {
        $this->packageRepository = $packageRepository;
        $this->purchaseRepository = $purchaseRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tracking' => 'required',
            'subject'  => 'required',
            'body'     => 'required',
        ]);

        /** @var User $user */
        $user = $request->user();

        /** @var array $filters */
        $filters = ['tracking' => $request->get('tracking'), 'user_id' => $user->id];

        // Validate tracking and owner the user
        if (!$this->packageRepository->filter($filters)->first() && !$this->purchaseRepository->filter($filters)->first()) {
            return self::badRequest('El numero de seguimiento no existe');
        }

        try {
            // Track with MultyAnalytics
            MultiAnalyticsFactory::trackUser($user, 'Reclamo');

            // Send mail
            Mail::send(new SupportRequested($request->get('tracking'), $request->get('subject'), $request->get('body'), $user));

            return self::success('Mensaje enviado correctamente.');
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return self::internalServerError();
        }
    }
}
