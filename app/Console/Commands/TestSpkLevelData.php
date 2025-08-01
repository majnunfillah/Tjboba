<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Repository\SPKRepository;
use Illuminate\Support\Facades\Log;

class TestSpkLevelData extends Command
{
    protected $signature = 'test:spk-level {nobukti?}';
    protected $description = 'Test SPK level 1 and level 2 data structure';

    private $spkRepository;

    public function __construct(SPKRepository $spkRepository)
    {
        parent::__construct();
        $this->spkRepository = $spkRepository;
    }

    public function handle()
    {
        $noBukti = $this->argument('nobukti') ?? '00031/SPK/PWT/022022';

        $this->info("Testing SPK data for: {$noBukti}");
        $this->info("=" . str_repeat("=", 50));

        try {
            // Test level 1 data
            $this->info("\n1. Testing Level 1 Data:");
            $level1Data = $this->spkRepository->getSpkDetailByNoBukti($noBukti);

            if ($level1Data && count($level1Data) > 0) {
                $this->info("   ✓ Found " . count($level1Data) . " level 1 records");
                $this->info("   ✓ Fields: " . implode(', ', array_keys((array)$level1Data[0])));

                // Test level 2 data for each level 1 record
                $this->info("\n2. Testing Level 2 Data:");
                foreach ($level1Data as $index => $level1Row) {
                    $urut = $level1Row->Urut ?? ($index + 1);
                    $this->info("   Testing Level 2 for Urut: {$urut}");

                    $level2Data = $this->spkRepository->getSpkDetailLevel2ByNoBuktiAndUrut($noBukti, $urut);

                    if ($level2Data && count($level2Data) > 0) {
                        $this->info("     ✓ Found " . count($level2Data) . " level 2 records");
                        $this->info("     ✓ Fields: " . implode(', ', array_keys((array)$level2Data[0])));

                        // Show sample data
                        $sample = $level2Data[0];
                        $this->info("     ✓ Sample Level 2 data:");
                        $this->info("       - KodePrs: " . ($sample->KodePrs ?? 'N/A'));
                        $this->info("       - KODEMSN: " . ($sample->KODEMSN ?? 'N/A'));
                        $this->info("       - TANGGAL: " . ($sample->TANGGAL ?? 'N/A'));
                        $this->info("       - QNTSPK: " . ($sample->QNTSPK ?? 'N/A'));
                    } else {
                        $this->warn("     ✗ No level 2 data found for Urut: {$urut}");
                    }
                }
            } else {
                $this->error("   ✗ No level 1 data found for NoBukti: {$noBukti}");
            }

            // Test combined data structure
            $this->info("\n3. Testing Combined Data Structure:");
            $this->info("   This simulates how data will appear in DataTable:");

            $allData = [];
            if ($level1Data) {
                foreach ($level1Data as $index => $level1Row) {
                    // Add level 1 row
                    $level1Row->level = 1;
                    $level1Row->parent_urut = null;
                    $allData[] = $level1Row;

                    // Add level 2 rows
                    $urut = $level1Row->Urut ?? ($index + 1);
                    $level2Data = $this->spkRepository->getSpkDetailLevel2ByNoBuktiAndUrut($noBukti, $urut);

                    if ($level2Data) {
                        foreach ($level2Data as $level2Row) {
                            $level2Row->level = 2;
                            $level2Row->parent_urut = $urut;
                            $allData[] = $level2Row;
                        }
                    }
                }
            }

            $this->info("   ✓ Total combined records: " . count($allData));
            $level1Count = count(array_filter($allData, function($item) { return $item->level == 1; }));
            $level2Count = count(array_filter($allData, function($item) { return $item->level == 2; }));
            $this->info("   ✓ Level 1 records: {$level1Count}");
            $this->info("   ✓ Level 2 records: {$level2Count}");

            $this->info("\n" . str_repeat("=", 60));
            $this->info("✓ Test completed successfully!");

        } catch (\Exception $e) {
            $this->error("✗ Test failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            Log::error('SPK Test Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
