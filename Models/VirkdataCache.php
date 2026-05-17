<?php

namespace Modules\Virkdata\Models;

use Illuminate\Database\Eloquent\Model;

class VirkdataCache extends Model
{
    protected $table = 'virkdata_caches';

    protected $fillable = [
        'company_name',
        'vat',
        'data',
    ];

    protected $casts = [
        'data' => 'array', // gem JSON som array
    ];

    /**
     * Retrieve company data from cache or null.
     */
    public static function getByVat(string $vat): ?self
    {
        return self::where('vat', $vat)->first();
    }

    /**
     * Store company data in cache.
     */
    public static function storeCache(string $vat, array $data): self
    {
        return self::updateOrCreate(
            ['vat' => $vat],
            ['data' => $data]
        );
    }
}
