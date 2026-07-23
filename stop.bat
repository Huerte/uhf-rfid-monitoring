@echo off
echo ===================================================
echo     Stopping RFID Monitoring System...
echo ===================================================

powershell -NoProfile -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -ne $null } | Where-Object { $cmd = $_.CommandLine; ($cmd -match 'npm run dev') -or ($cmd -match 'artisan reverb:start') -or ($cmd -match 'artisan serve') -or ($cmd -match 'read-epc.js') } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }"

if exist running_pids.json (
    del /f /q running_pids.json
)

echo.
echo Goods, stop na tanan terminal!!!
pause
