<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST LOGOUT FUNCTION ===\n\n";

try {
    // Test apakah route logout ada
    echo "1. Testing logout route:\n";
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $logoutRouteExists = false;
    
    foreach ($routes as $route) {
        if ($route->getName() === 'logout') {
            $logoutRouteExists = true;
            echo "   ✓ Logout route exists: " . $route->uri() . " [" . implode(',', $route->methods()) . "]\n";
            break;
        }
    }
    
    if (!$logoutRouteExists) {
        echo "   ✗ Logout route NOT found\n";
    }
    
    // Test apakah controller logout ada
    echo "\n2. Testing AuthController logout method:\n";
    $controllerExists = class_exists('\App\Http\Controllers\AuthController');
    echo "   AuthController exists: " . ($controllerExists ? "YES" : "NO") . "\n";
    
    if ($controllerExists) {
        $methodExists = method_exists('\App\Http\Controllers\AuthController', 'logout');
        echo "   logout method exists: " . ($methodExists ? "YES" : "NO") . "\n";
    }
    
    // Test apakah form logout ada di navbar
    echo "\n3. Testing navbar logout form:\n";
    $navbarPath = 'resources/views/components/navbar.blade.php';
    if (file_exists($navbarPath)) {
        $navbarContent = file_get_contents($navbarPath);
        $hasOnclick = strpos($navbarContent, 'onclick="Logout()"') !== false;
        $hasForm = strpos($navbarContent, 'id="formLogout"') !== false;
        $hasRoute = strpos($navbarContent, "route('logout')") !== false;
        
        echo "   navbar file exists: YES\n";
        echo "   has onclick=\"Logout()\": " . ($hasOnclick ? "YES" : "NO") . "\n";
        echo "   has formLogout: " . ($hasForm ? "YES" : "NO") . "\n";
        echo "   has logout route: " . ($hasRoute ? "YES" : "NO") . "\n";
    } else {
        echo "   navbar file exists: NO\n";
    }
    
    // Test apakah helper.js ada dan benar
    echo "\n4. Testing helper.js configuration:\n";
    $helperPath = 'public/assets/js/helper.js';
    if (file_exists($helperPath)) {
        $helperContent = file_get_contents($helperPath);
        $hasGlobalExport = strpos($helperContent, 'window.$globalVariable = $globalVariable') !== false;
        $hasLogoutFunction = strpos($helperContent, 'window.Logout = function') !== false;
        
        echo "   helper.js exists: YES\n";
        echo "   exports to window.$globalVariable: " . ($hasGlobalExport ? "YES" : "NO") . "\n";
        echo "   has global Logout function: " . ($hasLogoutFunction ? "YES" : "NO") . "\n";
    } else {
        echo "   helper.js exists: NO\n";
    }
    
    // Test apakah base-function.js memiliki Logout method
    echo "\n5. Testing base-function.js Logout method:\n";
    $baseFunctionPath = 'public/assets/js/base-function.js';
    if (file_exists($baseFunctionPath)) {
        $baseFunctionContent = file_get_contents($baseFunctionPath);
        $hasLogoutMethod = strpos($baseFunctionContent, 'Logout: function') !== false;
        $hasFormSubmit = strpos($baseFunctionContent, '"#formLogout"') !== false;
        
        echo "   base-function.js exists: YES\n";
        echo "   has Logout method: " . ($hasLogoutMethod ? "YES" : "NO") . "\n";
        echo "   submits formLogout: " . ($hasFormSubmit ? "YES" : "NO") . "\n";
    } else {
        echo "   base-function.js exists: NO\n";
    }
    
    echo "\n=== RECOMMENDATIONS ===\n";
    echo "1. Clear browser cache completely\n";
    echo "2. Hard refresh the page (Ctrl+F5)\n";
    echo "3. Check browser console for JavaScript errors\n";
    echo "4. Test logout button functionality\n";
    echo "5. If still not working, check browser Network tab for failed requests\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
