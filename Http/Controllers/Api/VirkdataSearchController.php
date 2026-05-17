<?php

namespace Modules\Virkdata\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Companies\Models\Companies;
use Modules\Virkdata\Models\Virkdata;
use Modules\Virkdata\Http\Requests\CompanySearchRequest;

class VirkdataSearchController extends Controller
{
    public function search(CompanySearchRequest $request, $query)
    {
        $query = $this->normalizeString($query);

        // 1️⃣ Lokale companies
        $companies = Companies::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('vat', $query);
            })
            ->with('address')
            ->get();

        $combination = $companies->toArray();

        // 2️⃣ Virkdata API
        $virkResult = $this->searchInVirkData($query);

        // 3️⃣ Tilføj kun, hvis ikke allerede findes lokalt
        if ($virkResult && !$this->isInLocalCompanies($virkResult, $combination)) {
            array_unshift($combination, $virkResult);
        }

        return $combination;
    }

    /**
     * Søg i Virkdata API med caching
     */
    private function searchInVirkData(string $query)
    {
        $cacheKey = $this->makeCacheKey($query);

        // Returner fra cache hvis ikke local
        $cached = null;
        if (!app()->environment('local')) {
            $cached = $this->getFromCache($cacheKey);
        }

        if ($cached) {
            return $cached;
        }

        $settings = Virkdata::active();
        if (!$settings) {
            return ['error' => 'Ingen aktiv Virkdata-token fundet'];
        }

        $company = $settings->searchCompany($query);

        if ($company) {
            if (!app()->environment('local')) {
                $this->storeInCache($cacheKey, $company);
            }
            return $company;
        }

        return null;
    }

    /**
     * Tjekker om Virkdata-resultatet allerede findes blandt lokale companies
     */
    private function isInLocalCompanies(array $virkResult, array $localCompanies): bool
    {
        // Antag at VAT/CVR bruges som unik identifikator
        $vat = $virkResult['vat'] ?? null;
        if (!$vat) return false;

        foreach ($localCompanies as $company) {
            if (($company['vat'] ?? null) === $vat) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hjælpefunktioner
     */
    private function makeCacheKey(string $query): string
    {
        return 'virkdata_search_query_' . md5($query);
    }

    private function getFromCache(string $key): mixed
    {
        return Cache::get($key);
    }

    private function storeInCache(string $key, mixed $data): void
    {
        Cache::put($key, $data, now()->addDay(1));
    }

    private function normalizeString(?string $value): string
    {
        return mb_strtolower(trim($value ?? ''), 'UTF-8');
    }
}
