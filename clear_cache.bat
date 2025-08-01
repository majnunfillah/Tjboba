@echo off
echo === CLEARING LARAVEL CACHE ===
echo.

cd /d "c:\bobajetbrain"

echo 1. Clearing application cache...
php artisan cache:clear

echo 2. Clearing config cache...
php artisan config:clear

echo 3. Clearing route cache...
php artisan route:clear

echo 4. Clearing view cache...
php artisan view:clear

echo 5. Clearing compiled files...
php artisan clear-compiled

echo 6. Clearing session files...
php artisan session:clear 2>nul || echo Session clear command not available

echo.
echo === CACHE CLEARED SUCCESSFULLY ===
echo.
echo NEXT STEPS:
echo 1. Restart your web server (if using Laravel Serve: Ctrl+C then 'php artisan serve')
echo 2. Clear browser cache (Ctrl+Shift+Delete)
echo 3. Logout and login again as 'adminkarir'
echo 4. Test SPK checkbox
echo.
pause
