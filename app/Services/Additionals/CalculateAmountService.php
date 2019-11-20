<?php

namespace App\Services\Additionals;

use App\Models\Additional;
use App\Models\Platform;
use App\Repositories\AdditionalRepository;
use App\Services\Additionals\Calculate\BoxForHeavyItemCalculateService;
use App\Services\Additionals\Calculate\BubbleWrapCalculateService;
use App\Services\Additionals\Calculate\Calculate;
use App\Services\Additionals\Calculate\ConsolidateCalculateService;
use App\Services\Additionals\Calculate\ContentPhotographyCalculateService;
use Illuminate\Support\Collection;

class CalculateAmountService
{
    /** @var AdditionalRepository  */
    protected $additionalRepository;

    /**
     * CalculateAmountService constructor.
     * @param AdditionalRepository $additionalRepository
     */
    public function __construct(AdditionalRepository $additionalRepository)
    {
        $this->additionalRepository = $additionalRepository;
    }

    /**
     * @param Collection $additionalsId
     * @param int $purchase_count
     * @param $type
     * @return AdditionalEntity
     */
    public function calculate(Collection $additionalsId, $purchase_count = 1, $type = 'CONSOLIDATION')
    {
        /** @var AdditionalEntity $additionalEntity */
        $additionalEntity = new AdditionalEntity();

        $amount = 0;

        $additionals = $this->getAdditionalsInstanceAndRequired($additionalsId, $type);

        /** @var Additional $additional */
        $additionals->each(function(Additional $additional) use($purchase_count, $additionals, &$additionalEntity, &$amount){
            /** @var Calculate $additionalService */
            $additionalService = $this->detectAdditional($additional);

            $additionalService->setPurchaseCount($purchase_count);

            $amount += $additionalService->getAmount();

            $additional->setAmount($additionalService->getAmount());

            $additionalEntity->pushAdditional($additional);
        });

        $additionalEntity->setAmount($amount);

        return $additionalEntity;
    }

    /**
     * @param Additional $additional
     * @return Calculate|null
     */
    private function detectAdditional(Additional $additional)
    {
        if($additional->key == 'CO'){
            return new ConsolidateCalculateService($additional);
        } else if($additional->key == 'CP'){
            return new ContentPhotographyCalculateService($additional);
        } else if($additional->key == 'BH'){
            return new BoxForHeavyItemCalculateService($additional);
        } else if($additional->key == 'BW'){
            return new BubbleWrapCalculateService($additional);
        }

        return null;
    }

    /**
     * @param Collection $additionalsId
     * @param string $type
     * @return Collection
     */
    private function getAdditionalsInstanceAndRequired(Collection $additionalsId, $type)
    {
        /** @var Platform $platform */
        $platform = current_platform();

        /** @var Collection $additionals */
        $additionals = $this->additionalRepository->findMany($additionalsId->toArray());

        if($type == 'CONSOLIDATION'){
            /** @var Collection $additionalsRequired */
            $additionalsRequired = $this->additionalRepository->filter(['required' => true, 'active' => true, 'platform_key' => $platform->key])->get();

            $additionals = $additionals->merge($additionalsRequired);
        }

        return $additionals;
    }
}