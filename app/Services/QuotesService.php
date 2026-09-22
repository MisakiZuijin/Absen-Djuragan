<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Models\Quotes;
use App\Repositories\Interface\QuotesRepository;
use App\Http\Requests\StoreQuoteRequest;

use function Sentry\captureException;

class QuotesService
{
    protected QuotesRepository $quotesRepository;

    public function __construct(QuotesRepository $quotesRepository)
    {
        $this->quotesRepository = $quotesRepository;
    }

    public function store(StoreQuoteRequest $storeQuoteRequest): ActionResult
    {
        try {
            $data = $storeQuoteRequest->validated();

            $data['created_by'] = 'admin';

            $result = $this->quotesRepository->create($data);

            return new ActionResult(true, "Successfully added data into quotes", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }

    public function getAll()
    {
        return Quotes::query();
    }

    public function getByCategory(string $category)
    {
        try {
            $result = $this->quotesRepository->getAll()->where('kategori', $category)->get();
            return new ActionResult(true, "success retrive data quotes by category", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed get data, something weird", null);
        }
    }

    public function delete(int $id)
    {
        try {
            $result = $this->quotesRepository->delete($id);
            return new ActionResult(true, "success delete quotes", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed delete data, something weird", null);
        }
    }
}
