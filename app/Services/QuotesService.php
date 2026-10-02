<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Models\Quotes;
use App\Repositories\Interface\QuotesRepository;
use App\Http\Requests\StoreQuoteRequest;
use Illuminate\Support\Facades\Cache;

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

            // Bersihkan cache seketika agar langsung tampil tanpa delay
            $this->clearQuotesCache($data['kategori'] ?? null);

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

    public function getByCategory(string $category): ActionResult
    {
        try {
            // Ambil data langsung dari database secara realtime
            $result = Quotes::where('kategori', $category)->orderByDesc('id')->get();
            return new ActionResult(true, "success retrive data quotes by category", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed get data, something weird", null);
        }
    }

    public function delete(int $id): ActionResult
    {
        try {
            $quote = Quotes::find($id);
            $category = $quote?->kategori;

            $result = $this->quotesRepository->delete($id);

            // Bersihkan cache seketika agar langsung hilang dari tampilan
            $this->clearQuotesCache($category);

            return new ActionResult(true, "success delete quotes", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed delete data, something weird", null);
        }
    }

    private function clearQuotesCache(?string $category = null): void
    {
        try {
            Cache::forget("quotes_category_quote");
            Cache::forget("quotes_category_ultah");
            if ($category) {
                Cache::forget("quotes_category_{$category}");
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
