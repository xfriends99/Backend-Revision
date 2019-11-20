<?php

namespace App\Jobs\Packages;

use App\Events\EventWasReceived;
use App\Models\Package;
use App\Repositories\PackageRepository;
use App\Services\Mailamericas\Tracking\Checkpoints\EventsService;
use App\Services\Packages\Events\PackageEventEntity;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Exception;

class UpdatePackageStatusJob implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Package
     */
    protected $package;

    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function handle()
    {
        try {
            /** @var PackageRepository $packageRepository */
            $packageRepository = app()->make(PackageRepository::class);

            /** @var EventsService $eventsService  */
            $eventsService = app()->make(EventsService::class);

            /** @var Carbon $dateStartEvents */
            $dateStartEvents = Carbon::now()->subHours(12);

            /** @var bool $mark_package */
            $mark_package = false;

            $events = $eventsService->search(['tracking' => $this->package->tracking])->getEvents();
            foreach ($events as $event)
            {
                /** @var PackageEventEntity $packageEventEntity */
                $packageEventEntity = new PackageEventEntity();

                $packageEventEntity->initialize((array) $event);

                $packageEventEntity->setCode('PF-1');

                if($packageEventEntity->isEventToNotify() && $dateStartEvents->lt($packageEventEntity->getParseDate())){
                    event(new EventWasReceived($packageEventEntity, $this->package));
                }

                if($packageEventEntity->isDelivered()){
                    $mark_package = true;
                }
            }

            if($mark_package){
                $packageRepository->markAsProcessed($this->package);
            }

            return true;
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());
            echo $e->getMessage();

            throw $e;
        }
    }
}
