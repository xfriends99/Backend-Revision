<?php

namespace App\Listeners\Shared;

use App\Listeners\PlatformListener;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\Warehouses\Lars\LockerService;
use Illuminate\Auth\Events\Verified;

class NotifyLarsLockerCreated extends PlatformListener
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Auth\Events\Verified $event
     * @return void
     */
    public function handle(Verified $event)
    {
        /** @var LockerService $lockerService */
        $lockerService = app(LockerService::class);

        /** @var UserRepository $userRepository */
        $userRepository = app(UserRepository::class);

        /** @var User $user */
        $user = $userRepository->getById($event->user->getAuthIdentifier());

        $lockerService->registerUser($user);
    }
}