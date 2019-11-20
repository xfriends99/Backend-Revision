<?php

namespace App\Services\Coupons;

use App\Models\CouponClassification;
use App\Models\User;
use App\Models\Coupon;
use App\Repositories\UserRepository;
use App\Repositories\CouponRepository;
use App\Repositories\CouponClassificationRepository;
use App\Services\Coupons\Entities\CouponEntity;
use App\Services\Coupons\BaseCouponDecorator;
use App\Services\Coupons\Decorators\AmountDecorator;
use App\Services\Coupons\Decorators\PercentDecorator;
use App\Http\Requests\StoreCouponRequest;
use App\Events\UserHasCoupon;
use App\Services\Coupons\Requests\CouponClassificationFactory;
use App\Services\Coupons\Requests\Process\AbstractCouponClassification;
use Illuminate\Support\Facades\DB;
use Exception;

class CouponService
{
    /** @var UserRepository */
    protected $userRepository;

    /** @var CouponRepository */
    protected $couponRepository;

    /** @var CouponRepository */
    protected $couponClassificationRepository;

    /**
     * CouponService constructor.
     * @param UserRepository $userRepository
     * @param CouponRepository $couponRepository
     * @param CouponClassificationRepository $couponClassificationRepository
     */
    public function __construct(
        UserRepository $userRepository,
        CouponRepository $couponRepository,
        CouponClassificationRepository $couponClassificationRepository
    ) {
        $this->userRepository = $userRepository;
        $this->couponRepository = $couponRepository;
        $this->couponClassificationRepository = $couponClassificationRepository;
    }

    /**
     * @param CouponEntity $couponEntity
     * @return Coupon
     * @throws Exception
     */
    public function createCoupon(CouponEntity $couponEntity)
    {   
    	try {
            DB::beginTransaction();

            /** @var Coupon $coupon */
            $coupon = $this->couponRepository->create([
                'description'              => $couponEntity->getDescription(),
                'code'                     => $couponEntity->getCode(),
                'coupon_classification_id' => $couponEntity->getCouponClassificationId(),
                'amount'                   => $couponEntity->getAmount(),
                'percent'                  => $couponEntity->getPercent(),
                'max_amount'               => $couponEntity->getMaxAmount(),
                'max_uses'                 => $couponEntity->getMaxUses(),
                'user_id'                  => $couponEntity->getUserId(),
                'valid_from'               => $couponEntity->getValidFrom(),
                'valid_to'                 => $couponEntity->getValidTo()
            ]);

            DB::commit();
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        if ($coupon->user_id) {
            event(new UserHasCoupon($coupon));
        }

        return $coupon;
    }

    /**
     * @param Coupon $coupon
     * @param CouponEntity $couponEntity
     * @return Coupon
     * @throws Exception
     */
    public function updateCoupon(Coupon $coupon, CouponEntity $couponEntity)
    {   
        /** @var User|null $oldUser */
        $oldUser = $coupon->user;

        try {
            DB::beginTransaction();
            /** @var bool $couponUpdated */
            $this->couponRepository->update($coupon, [
                'description'              => $couponEntity->getDescription(),
                'coupon_classification_id' => $couponEntity->getCouponClassificationId(),
                'amount'                   => $couponEntity->getAmount(),
                'percent'                  => $couponEntity->getPercent(),
                'max_amount'               => $couponEntity->getMaxAmount(),
                'max_uses'                 => $couponEntity->getMaxUses(),
                'user_id'                  => $couponEntity->getUserId(),
                'valid_from'               => $couponEntity->getValidFrom(),
                'valid_to'                 => $couponEntity->getValidTo()
            ]);

            DB::commit();
        } catch (Exception $e) {
            logger($e->getMessage());

            DB::rollBack();

            throw new Exception($e->getMessage());
        }
        
        if ($oldUser && $coupon->user_id && $oldUser->id != $coupon->user_id) {
            event(new UserHasCoupon($coupon));
        }

        return $coupon;
    }

    /**
     * @param StoreCouponRequest $storeCouponRequest
     * @param Coupon|null $coupon
     * @return CouponEntity
     */
    public function parseCoupon(StoreCouponRequest $storeCouponRequest, Coupon $coupon = null)
    {
        /** @var array $attributes */
        $attributes = $storeCouponRequest->validated();

        /** @var CouponClassification $couponClassification */
        $couponClassification = $this->couponClassificationRepository->getById($attributes['coupon_classification_id']);

        /** @var string $code */
        $code = $coupon ? $coupon->code : $this->generateCouponCode();

        /** @var User|null $user */
        $user = isset($attributes['user_id']) ? $this->userRepository->getById($attributes['user_id']) : null;

        return new CouponEntity(
            $couponClassification,
            $attributes['description'],
            $code,
            isset($attributes['max_uses']) ? $attributes['max_uses'] : null,
            isset($attributes['max_amount']) ? $attributes['max_amount'] : null,
            $user,
            isset($attributes['amount']) ? $attributes['amount'] : null,
            isset($attributes['percent']) ? $attributes['percent'] : null,
            isset($attributes['valid_from']) ? $attributes['valid_from'] : null,
            isset($attributes['valid_to']) ? $attributes['valid_to'] : null
        );
    }

    /**
     * @param Coupon $coupon
     * @return CouponEntity
     */
    private function parseCouponEntity(Coupon $coupon)
    {
        return new CouponEntity(
            $coupon->couponClassification,
            $coupon->description,
            $coupon->code,
            $coupon->max_uses,
            $coupon->max_amount,
            $coupon->user,
            $coupon->amount,
            $coupon->percent,
            $coupon->valid_from,
            $coupon->valid_to
        );
    }

    /**
     * @return bool|null|string
     */
    private function generateCouponCode()
    {
        $code = null;
        $available = false;
        while (!$available) {
            /** @var string $code */
            $code = substr(str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890", 50)), 0, 6);

            /** @var string|null $locker */
            $locker = $this->couponRepository->getByCode($code);

            if (!$locker) {
                $available = true;
            }
        }

        return $code;
    }

    /**
     * @param User $user
     * @return CouponEntity
     * @throws Exception
     */
    public function generateReferralCoupon(User $user)
    {   
        /** @var CouponClassification $couponClassification */
        if (!$couponClassification = $this->couponClassificationRepository->getByKey('referred')) {
            throw new Exception('Coupon classification not found');
        }

        /** @var string $code */
        $code = $this->generateCouponCode();

        return new CouponEntity(
            $couponClassification,
            'Cupón de referido',
            $code,
            1,
            null,
            $user,
            null,
            5,
            null,
            null
        );
    }    

    /**
     * @param Coupon $coupon
     * @param float $amountInvoice
     * @return float
     */
    public function applyDiscount(Coupon $coupon, $amountInvoice)
    {
        /** @var CouponEntity $couponEntity */
        $couponEntity = $this->parseCouponEntity($coupon);

        /** @var float $totalAmount */
        $totalAmount = 0;

        /** @var float $totalPercent */
        $totalPercent = 0;

        /** @var float $total */
        $total = 0;
        
        if ($coupon->amount > 0) {
            $amountDecorator = new AmountDecorator($couponEntity);
            $totalAmount = $amountDecorator->totalAmount($amountInvoice);
        }        

        if ($coupon->percent > 0) {
            $percentDecorator = new PercentDecorator($couponEntity);
            $totalPercent = $percentDecorator->totalAmount($amountInvoice);
        }
        
        if (($totalAmount && $totalPercent) > 0) {
            if ($totalAmount <= $totalPercent) {
                $total = $totalPercent;
            } else {
                $total = $totalAmount;
            }            
        } else if ($totalAmount > 0) {
            $total = $totalAmount;
        } else {
            $total = $totalPercent;
        }

        return $this->maxDiscount($coupon, $amountInvoice, $total);
    }

    /**
     * @param Coupon $coupon
     * @param float $amountInvoice
     * @param float $amountTotal
     * @return float
     */
    private function maxDiscount(Coupon $coupon, $amountInvoice, $amountTotal)
    {   
        if ($coupon->max_amount > 0) {
            $maxAmount = $amountInvoice - $coupon->max_amount;
            if ($amountTotal <= $maxAmount) {
                return $maxAmount;            
            }

            return $amountTotal;
        }

        return $amountTotal;        
    }

    /**
     * @param User $user
     * @param $classification_key
     * @param $code
     * @return Coupon
     * @throws Exception
     */
    public function getCouponUserByKeyAndCode(User $user, $classification_key, $code)
    {
        if(!$classification_key || !$code){
            throw new Exception("Error procesando cupón");
        }

        /** @var CouponClassification $couponClassification */
        if(!$couponClassification = $this->couponClassificationRepository->getByKey($classification_key)){
            throw new Exception("Clasificación de Cupón {$classification_key} es invalida");
        }

        /** @var AbstractCouponClassification $couponClassificationProcess */
        $couponClassificationProcess = CouponClassificationFactory::detectClassificationProcessCoupon($couponClassification);

        try{
            DB::beginTransaction();

            /** @var Coupon|null $coupon */
            $coupon = $couponClassificationProcess->search($couponClassification, $code);

            if(!$coupon && !$couponClassification->isClubNacion()){
                throw new Exception('El código de cupón no existe');
            } else if($couponClassification->isClubNacion()) {

                /** @var CouponEntity $couponEntity */
                $couponEntity = $couponClassificationProcess->getCouponEntity($user, $code);

                /** @var Coupon $coupon */
                $coupon = $couponClassificationProcess->createCoupon($couponEntity);
            }

            $couponClassificationProcess->validateCoupon($coupon, $user);

            DB::commit();
        } catch (Exception $e){
            logger($e->getMessage());
            logger($e->getTraceAsString());

            DB::rollBack();

            throw $e;
        }

        return $coupon;
    }
}
