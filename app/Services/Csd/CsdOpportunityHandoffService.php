<?php

namespace App\Services\Csd;

use App\Models\CsdOpportunity;
use App\Services\Commercial\ClientEngagementService;

class CsdOpportunityHandoffService
{
    public function __construct(private ClientEngagementService $engagements)
    {
    }

    public function notifySalesOnWon(CsdOpportunity $opportunity): void
    {
        $this->engagements->spawnFromWonOpportunity($opportunity);
    }
}
