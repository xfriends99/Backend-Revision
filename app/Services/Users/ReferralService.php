<?php

namespace App\Services\Users;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Mail\ReferralInvitation;
use Illuminate\Support\Facades\Mail;
use Exception;

class ReferralService
{
    /** @var UserRepository */
    protected $userRepository;

    /**
     * ReferralService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * @param User $user
     * @param array $emails
     * @return bool
     * @throws Exception
     */
    public function sendInvitation(User $user, $emails)
    {   
        $emails_to_send = [];
        foreach ($emails as $email) {            
            // Check if email already exists
            if (!$this->userRepository->filter(compact('email'))->first()) {
                array_push($emails_to_send, $email);
            }                
        }

        if (!empty($emails_to_send)) {
            try {
                // Send mail
                Mail::send(new ReferralInvitation($user, $emails_to_send));
            
                return true;
            } catch (Exception $e) {
                logger($e->getMessage());
                throw new Exception;
            }
        }

        return false;
        
    }   
}