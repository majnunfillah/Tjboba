<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== EMERGENCY PERMISSION FIX ===\n\n";

try {
    // 1. Check current database permissions
    echo "1. Checking current database permissions:\n";
    $memorialPerm = \Illuminate\Support\Facades\DB::select("SELECT * FROM DBFLMENU WHERE USERID = 'adminkarir' AND L1 = '02002'");
    $spkPerm = \Illuminate\Support\Facades\DB::select("SELECT * FROM DBFLMENU WHERE USERID = 'adminkarir' AND L1 = '08103'");
    
    echo "   Memorial (02002): " . (count($memorialPerm) > 0 ? "IsOtorisasi1 = " . $memorialPerm[0]->IsOtorisasi1 : "NOT FOUND") . "\n";
    echo "   SPK (08103): " . (count($spkPerm) > 0 ? "IsOtorisasi1 = " . $spkPerm[0]->IsOtorisasi1 : "NOT FOUND") . "\n\n";
    
    // 2. Force update SPK permission to match Memorial
    if (count($memorialPerm) > 0 && count($spkPerm) > 0) {
        echo "2. Forcing SPK permission to match Memorial:\n";
        $memorialOto1 = $memorialPerm[0]->IsOtorisasi1;
        
        $updated = \Illuminate\Support\Facades\DB::update("
            UPDATE DBFLMENU 
            SET IsOtorisasi1 = ? 
            WHERE USERID = 'adminkarir' AND L1 = '08103'
        ", [$memorialOto1]);
        
        echo "   Updated $updated SPK permission record\n";
        echo "   SPK IsOtorisasi1 set to: $memorialOto1\n\n";
    }
    
    // 3. Also check if there are multiple records
    echo "3. Checking for duplicate records:\n";
    $allRecords = \Illuminate\Support\Facades\DB::select("
        SELECT USERID, L1, IsOtorisasi1 
        FROM DBFLMENU 
        WHERE USERID = 'adminkarir' AND L1 IN ('02002', '08103')
        ORDER BY L1
    ");
    
    foreach ($allRecords as $record) {
        $module = $record->L1 == '02002' ? 'Memorial' : 'SPK';
        echo "   $module ({$record->L1}): IsOtorisasi1 = {$record->IsOtorisasi1}\n";
    }
    
    // 4. Test with fresh user instance
    echo "\n4. Testing with fresh user instance:\n";
    $user = App\Models\DBFLPASS::where('USERID', 'adminkarir')->first();
    
    if ($user) {
        // Clear any cached permissions
        auth()->login($user);
        
        $memorialPerms = $user->getPermissionsName('02002');
        $spkPerms = $user->getPermissionsName('08103');
        
        echo "   Memorial permissions: " . json_encode($memorialPerms) . "\n";
        echo "   SPK permissions: " . json_encode($spkPerms) . "\n";
        
        $memorialHasOto = in_array('IsOtorisasi1', $memorialPerms);
        $spkHasOto = in_array('IsOtorisasi1', $spkPerms);
        
        echo "   Memorial has IsOtorisasi1: " . ($memorialHasOto ? 'YES' : 'NO') . "\n";
        echo "   SPK has IsOtorisasi1: " . ($spkHasOto ? 'YES' : 'NO') . "\n\n";
        
        if ($memorialHasOto && !$spkHasOto) {
            echo "5. FOUND THE ISSUE: SPK permission not loading properly!\n";
            echo "   Investigating getPermissionsName method...\n";
            
            // Let's check the raw query
            echo "   Direct database check for SPK:\n";
            $directCheck = \Illuminate\Support\Facades\DB::select("
                SELECT dm.*, dp.USERID as pass_userid
                FROM DBFLMENU dm
                LEFT JOIN DBFLPASS dp ON dm.USERID = dp.USERID
                WHERE dm.USERID = 'adminkarir' AND dm.L1 = '08103'
            ");
            
            if (count($directCheck) > 0) {
                $check = $directCheck[0];
                echo "   Found record: IsOtorisasi1 = {$check->IsOtorisasi1}\n";
                echo "   DBFLPASS link: " . ($check->pass_userid ? 'OK' : 'MISSING') . "\n";
            } else {
                echo "   No record found in direct check!\n";
            }
        } else if (!$memorialHasOto && !$spkHasOto) {
            echo "5. Both modules missing permission - user session issue\n";
        } else if ($memorialHasOto && $spkHasOto) {
            echo "5. Both modules have permission - browser cache issue\n";
        }
    }
    
    echo "\n=== SOLUTION ===\n";
    echo "1. LOGOUT from web application\n";
    echo "2. Clear browser cache completely\n";
    echo "3. LOGIN again as 'adminkarir'\n";
    echo "4. Test SPK checkbox again\n";
    echo "5. If still fails, restart web server\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
