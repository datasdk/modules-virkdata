<?php

namespace Modules\Virkdata\Console\Commands;

use Illuminate\Console\Command;
use Modules\Virkdata\Models\Virkdata;
use Modules\Virkdata\Models\VirkdataCache;

class UpdateVirkdataCaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan virkdata:update-caches
     */
    protected $signature = 'virkdata:update-caches 
                            {--limit=0 : Limit number of records to update (0 = all)}';

    /**
     * The console command description.
     */
    protected $description = 'Fetch and update Virkdata company info for all cached VAT numbers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $settings = Virkdata::active();

        if (!$settings) {
            $this->error('❌ No active Virkdata token found.');
            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $query = VirkdataCache::query()->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $caches = $query->get();
        $count = $caches->count();

        if ($count === 0) {
            $this->info('ℹ️ No caches found to update.');
            return self::SUCCESS;
        }

        $this->info("🔍 Updating {$count} cached records...");

        foreach ($caches as $cache) {
            $vat = $cache->vat;

            if (!$vat) {
                $this->warn("⚠️ Skipping cache ID {$cache->id} (no VAT)");
                continue;
            }

            $this->line("→ Fetching data for VAT: {$vat}");

            try {
                $data = $settings->searchCompany($vat);

                if ($data) {
                    $cache->update([
                        'company_name' => $data['name'] ?? $cache->company_name,
                        'data' => $data,
                    ]);

                    $this->info("✅ Updated {$vat}");
                } else {
                    $this->warn("⚠️ No data returned for {$vat}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Error updating {$vat}: " . $e->getMessage());
            }

            sleep(1); // Throttle API calls a bit
        }

        $this->info('✅ All cached companies updated successfully.');
        return self::SUCCESS;
    }
}
