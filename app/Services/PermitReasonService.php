<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Repositories\Interface\PermitCategoryRepository;
use App\Repositories\Interface\PermitReasonRepository;

use function Sentry\captureException;

class PermitReasonService {
    protected $permitReasonRepository;
    protected $permitCategoryRepository;


    public function __construct(PermitReasonRepository $permitRepository, PermitCategoryRepository $permitCategory) {
        $this->permitReasonRepository = $permitRepository;
        $this->permitCategoryRepository = $permitCategory;
    }


    public function getAllPermitCategory(): ActionResult {
        try {
            $value =  $this->permitCategoryRepository->findAll();
            return new ActionResult(true, "success retrive category data", $value);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed retrive category data", null);
        }
    }
}
