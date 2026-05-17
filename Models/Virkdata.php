<?php

namespace Modules\Virkdata\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Virkdata extends Model
{
    protected $table = 'virkdata';

    protected $fillable = [
        'auth_token',
        'base_url',
        'active',
    ];


    /**
     * Get the currently active Virkdata credentials.
     */
    public static function active(): ?self
    {

        return self::where('active', 1)->latest()->first();

    }


    /**
     * Search company by VAT or name using Virkdata API.
     * Returnerer array med data — eller false ved fejl.
     */
    public function searchCompany(string $query)
    {


        $url = ($this->base_url ?? 'https://virkdata.dk/api/') 
            . "?search={$query}&format=json&country=dk";


        try {

            $response = Http::withHeaders([
                'Authorization' => trim($this->auth_token),
            ])->timeout(30)->get($url);

        } catch (\Throwable $e) {

            Log::error('Virkdata API-request mislykkedes', [
                'exception' => $e->getMessage(),
                'url' => $url,
            ]);

            return false;

        }


        if ($response->failed()) {
            Log::warning('Virkdata API-fejl (HTTP failed)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }


        $data = $response->json();


        // Tjek for API-fejl
        if ($this->hasApiError($data)) {
            Log::error('Virkdata API returnerede fejlstatus', [
                'status' => $data['status'] ?? null,
                'error' => $data['error'] ?? null,
                'query' => $query,
                'response' => $data,
            ]);

            return false;
        }


        // ✅ Returnér struktureret array
        return [
            "vat" => $data["vat"] ?? null,
            "status" => $data["status"] ?? null,
            "name" => $data["name"] ?? null,
            
            "address" => [
                "street" => $data["address"] ?? null,
                "post_code" => $data["zipcode"] ?? null,
                "city" => $data["city"] ?? null,
            ],

            "contacts" => [
                "phone" => $data["phone"] ?? null,
                "email" => $data["email"] ?? null,
                "website" => $data["website"] ?? null,
                "fax" => $data["fax"] ?? null,
            ],

            "protected" => $data["protected"] ?? false,
            
            "startdate" => $data["startdate"] ?? null,
            "enddate" => $data["enddate"] ?? null,
            "employees" => $data["employees"] ?? 0,
            
            "industrycode" => $data["industrycode"] ?? null,
            "industrydesc" => $data["industrydesc"] ?? null,
            "companytype" => $data["companytype"] ?? null,
            "companydesc" => $data["companydesc"] ?? null
        ];

    }


    /**
     * Tjek om API-svaret indeholder en kendt Virkdata-fejlkode.
     */
    private function hasApiError($data): bool
    {


        if (!is_array($data) || !isset($data['error_code'])) {
            return false;
        }


        $errorCodes = [
            1001, 1002, 1003, // Authentication errors
            2001, 2002,       // Property errors
            3001, 3002,       // Request/value errors
            4001,             // Format error
            5001, 5002,       // Country errors
            6001,             // Test connection
            7001, 7002,       // Quota errors
            8001,             // Too many requests
        ];


        return in_array($data['error_code'], $errorCodes, true);

    }

}
