@echo off
echo ===================================================
echo     Starting RFID Monitoring System...
echo ===================================================

echo [1/4] Starting Vite Frontend Server...
start "Vite Frontend Server" cmd /k "cd backend && npm run dev"

echo [2/4] Starting Reverb WebSocket Server...
start "Reverb WebSocket Server" cmd /k "cd backend && php artisan reverb:start"

echo [3/4] Starting Laravel HTTP Web Server...
start "Laravel Web Server" cmd /k "cd backend && php artisan serve --port=8000"

echo [4/4] Starting RFID Reader Bridge...
start "RFID Reader Bridge" cmd /k "cd backend\rfid-bridge && node read-epc.js"

echo.
echo Waiting 5 seconds for all servers to load...
timeout /t 5 /nobreak >nul

echo Opening browser at http://127.0.0.1:8000 ...
start http://127.0.0.1:8000/

echo.
echo Goods!!!
echo Double-click ang stop.bat, para ma-stop
