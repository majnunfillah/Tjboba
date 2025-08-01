<?php
/**
 * Test SPK Data - Quick Debug
 */

require __DIR__ . '/vendor/autoload.php';

// Load Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Repository\SPKRepository;
use App\Models\DBFLPASS;
use Illuminate\Support\Facades\DB;

echo "=== SPK DATA TEST ===" . PHP_EOL;

try {
    // 1. Test Authentication
    echo "1. Testing Authentication..." . PHP_EOL;
    $user = DBFLPASS::where('USERID', 'adminkarir')->first();
    if (!$user) {
        echo "❌ User adminkarir not found!" . PHP_EOL;
        exit(1);
    }
    echo "✅ User found: " . $user->USERID . PHP_EOL;
    
    // Simulate login
    auth()->login($user);
    echo "✅ User logged in" . PHP_EOL;
    
    // 2. Test Permission
    echo PHP_EOL . "2. Testing User Permissions..." . PHP_EOL;
    try {
        $permissions = $user->getPermissionsName('08103');
        echo "✅ Permissions for 08103: " . implode(', ', $permissions) . PHP_EOL;
    } catch (Exception $e) {
        echo "❌ Permission error: " . $e->getMessage() . PHP_EOL;
    }
    
    // 3. Test Period
    echo PHP_EOL . "3. Testing User Period..." . PHP_EOL;
    $periode = DB::table('dbperiode')->where('USERID', $user->USERID)->first();
    if (!$periode) {
        echo "❌ No period found for user!" . PHP_EOL;
        echo "Setting default period..." . PHP_EOL;
        
        // Insert default period
        DB::table('dbperiode')->insert([
            'USERID' => $user->USERID,
            'TAHUN' => 2024,
            'BULAN' => 12
        ]);
        
        $periode = DB::table('dbperiode')->where('USERID', $user->USERID)->first();
    }
    echo "✅ Period: " . $periode->BULAN . "/" . $periode->TAHUN . PHP_EOL;
    
    // 4. Test SPK Table Exists
    echo PHP_EOL . "4. Testing SPK Table..." . PHP_EOL;
    try {
        $tableExists = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'dbSPK'");
        if (empty($tableExists)) {
            echo "❌ Table dbSPK does not exist!" . PHP_EOL;
            exit(1);
        }
        echo "✅ Table dbSPK exists" . PHP_EOL;
        
        // Check columns
        $columns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'dbSPK' ORDER BY ORDINAL_POSITION");
        echo "📋 SPK Columns: " . implode(', ', array_map(function($col) { return $col->COLUMN_NAME; }, $columns)) . PHP_EOL;
        
    } catch (Exception $e) {
        echo "❌ Table check error: " . $e->getMessage() . PHP_EOL;
    }
    
    // 5. Test SPK Data Count
    echo PHP_EOL . "5. Testing SPK Data..." . PHP_EOL;
    try {
        $totalSpk = DB::table('dbSPK')->count();
        echo "📊 Total SPK records: " . $totalSpk . PHP_EOL;
        
        if ($totalSpk > 0) {
            // Show sample data
            $sample = DB::table('dbSPK')->take(3)->get();
            echo "📋 Sample SPK data:" . PHP_EOL;
            foreach ($sample as $spk) {
                echo "   - NoBukti: " . $spk->NoBukti . ", Tanggal: " . $spk->Tanggal . PHP_EOL;
            }
        }
        
        // Test with period filter
        $filteredSpk = DB::table('dbSPK')
            ->whereYear('Tanggal', $periode->TAHUN)
            ->whereMonth('Tanggal', $periode->BULAN)
            ->count();
        echo "📊 SPK for period " . $periode->BULAN . "/" . $periode->TAHUN . ": " . $filteredSpk . PHP_EOL;
        
    } catch (Exception $e) {
        echo "❌ SPK data error: " . $e->getMessage() . PHP_EOL;
    }
    
    // 6. Test Repository
    echo PHP_EOL . "6. Testing SPK Repository..." . PHP_EOL;
    try {
        $repository = new SPKRepository();
        $spkData = $repository->getAllSpk();
        
        echo "✅ Repository call successful" . PHP_EOL;
        echo "📊 Repository returned: " . (is_array($spkData) ? count($spkData) : (is_object($spkData) ? $spkData->count() : 0)) . " records" . PHP_EOL;
        
        if (!empty($spkData)) {
            $first = is_array($spkData) ? $spkData[0] : $spkData->first();
            if ($first) {
                echo "📋 First record: " . PHP_EOL;
                echo "   - NoBukti: " . ($first->NoBukti ?? 'N/A') . PHP_EOL;
                echo "   - Tanggal: " . ($first->Tanggal ?? 'N/A') . PHP_EOL;
                echo "   - KodeBrg: " . ($first->KodeBrg ?? 'N/A') . PHP_EOL;
            }
        } else {
            echo "⚠️  Repository returned empty data" . PHP_EOL;
        }
        
    } catch (Exception $e) {
        echo "❌ Repository error: " . $e->getMessage() . PHP_EOL;
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . PHP_EOL;
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}

echo PHP_EOL . "=== TEST COMPLETED ===" . PHP_EOL;
?>
