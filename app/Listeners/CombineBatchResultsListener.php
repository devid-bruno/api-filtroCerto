<?php

namespace App\Listeners;
ini_set('memory_limit', '256M');

use App\Events\AllBatchesProcessed;
use App\Jobs\CombineBatchResults;
use App\Jobs\CombineBatchResultsWpp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CombineBatchResultsListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * @param  AllBatchesProcessed  $event
     * @return void
     */
    public function handle(AllBatchesProcessed $event)
    {
        CombineBatchResults::dispatch($event->batchCount, $event->userId, $event->uuid);
        CombineBatchResultsWpp::dispatch($event->batchCount, $event->userId, $event->uuid);
    }
}
