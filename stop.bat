@echo off
echo ===================================================
echo     Stopping RFID Monitoring System...
echo ===================================================

taskkill /F /T /FI "WINDOWTITLE eq Vite Frontend Server*"
taskkill /F /T /FI "WINDOWTITLE eq Reverb WebSocket Server*"
taskkill /F /T /FI "WINDOWTITLE eq Laravel Web Server*"
taskkill /F /T /FI "WINDOWTITLE eq RFID Reader Bridge*"

if exist running_pids.json (
    del /f /q running_pids.json
)

echo.
echo Goods, stop na tanan terminal!!!
pause