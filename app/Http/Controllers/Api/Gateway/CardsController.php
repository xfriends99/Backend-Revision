<?php

namespace App\Http\Controllers\Api\Gateway;

use App\Http\Controllers\Api\Transformers\CardTransformer;
use App\Http\Requests\StoreCardRequest;
use App\Models\Card;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Repositories\CardRepository;
use App\Repositories\PackageRepository;
use App\Repositories\PaymentGatewayRepository;
use App\Services\Cards\GatewayFactory;
use Illuminate\Http\Request;
use App\Traits\JsonApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\DB;

class CardsController extends Controller
{
    use JsonApiResponse;

    /** @var  CardRepository */
    protected $cardRepository;

    /** @var  PackageRepository */
    protected $packageRepository;

    /** @var  paymentGatewayRepository */
    protected $paymentGatewayRepository;

    /**
     * CardsController constructor.
     * @param CardRepository $cardRepository
     * @param PackageRepository $packageRepository
     * @param PaymentGatewayRepository $paymentGatewayRepository
     */
    public function __construct(CardRepository $cardRepository,
                                PackageRepository $packageRepository,
                                PaymentGatewayRepository $paymentGatewayRepository)
    {
        $this->cardRepository = $cardRepository;
        $this->packageRepository = $packageRepository;
        $this->paymentGatewayRepository = $paymentGatewayRepository;
    }

    /**
     * @param StoreCardRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(StoreCardRequest $request)
    {
        /** @var User $user */
        $user = $request->user();

        /** @var PaymentGateway $paymentGateway */
        $paymentGateway = $this->paymentGatewayRepository->getByKey($request->get('key'));

        try {
            /** @var Card $card */
            $card = GatewayFactory::addCard($user, $paymentGateway, $request->validated());

            $fractal = fractal($card, new CardTransformer());
            $fractal->addMeta(['error' => false]);

            return response()->json($fractal->toArray(), 201, [], JSON_PRETTY_PRINT);

        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return self::errorResponse('Error cargando la tarjeta de crédito.', 500);
        }
    }

    /**
     * @param Request $request
     * @param $card_id
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function update(Request $request, $card_id)
    {
        /** @var Card $card */
        if (!$card = $this->cardRepository->getById($card_id)) {
            return self::errorResponse('Tarjeta no encontrada.', 404);
        }

        try {
            DB::beginTransaction();
            GatewayFactory::markAsDefault($card);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return self::errorResponse('Error actualizando la tarjeta.', 500);
        }

        return self::success(['message' => 'Tarjeta actualizada correctamente.']);
    }

    /**
     * @param Request $request
     * @param $card_id
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function destroy(Request $request, $card_id)
    {
        /** @var Card $card */
        if (!$card = $this->cardRepository->getById($card_id)) {
            return self::errorResponse('Tarjeta no encontrada.', 404);
        }

        /** @var User $user */
        $user = $request->user();

        if ($user->getCardsCount() <= 1) {
            return self::errorResponse('Debe agregar una tarjeta antes de eliminar la actual.', 500);
        }

        if ($card->isDefault()) {
            return self::errorResponse('No se puede eliminar una tarjeta activa. Active otra antes de eliminarla.', 500);
        }

        if(!GatewayFactory::deleteCard($card)){
            return self::errorResponse('Error eliminando la tarjeta.', 500);
        }

        return self::success(['message' => 'Tarjeta eliminada correctamente.']);
    }

}
