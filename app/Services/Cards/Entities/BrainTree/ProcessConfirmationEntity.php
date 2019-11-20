<?php

namespace App\Services\Cards\Entities\BrainTree;

use Braintree\WebhookNotification;
use App\Services\Cards\Entities\ProcessConfirmationEntity as BaseProcessConfirmationEntity;

class ProcessConfirmationEntity extends BaseProcessConfirmationEntity
{
    /** @var WebhookNotification */
    protected $webHookNotification;

    /**
     * @param WebhookNotification $webHookNotification
     */
    public function setWebHookNotification(WebhookNotification $webHookNotification)
    {
        $this->webHookNotification = $webHookNotification;
    }

    /**
     * @return WebhookNotification
     */
    public function getWebHookNotification()
    {
        return $this->webHookNotification;
    }
}