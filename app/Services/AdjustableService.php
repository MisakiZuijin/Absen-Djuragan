<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Repositories\Interface\AdjustableAttdRepository;
use App\Utils\AdjustableStatus;
use Illuminate\Support\Facades\DB;

class AdjustableService {

    protected AdjustableAttdRepository $adjustableAttdRepository;

    public function __construct(AdjustableAttdRepository $adjustableAttdRepository) {
        $this->adjustableAttdRepository = $adjustableAttdRepository;
    }

    public function updateStatusAdjustable(int $adjustableId, int $isAccepted) {
        try {

            $this->adjustableAttdRepository->update($adjustableId, ["is_approved" => $isAccepted]);

            return new ActionResult(true, "success update status adjustable");
        } catch (\Exception $e) {
            LogConsole::info($e->getMessage());
            return new ActionResult(false, $e->getMessage());
        }
    }
}
