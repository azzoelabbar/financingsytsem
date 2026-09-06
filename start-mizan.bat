@echo off
chcp 65001 >nul
title Mizan - تشغيل النظام

echo ==================================================
echo    ميزان - Mizan Financial Management ^& ERP
echo ==================================================
echo.

cd /d "%~dp0"

docker info >nul 2>&1
if errorlevel 1 (
    echo [!] Docker Desktop is not running.
    echo     برنامج Docker غير مشغل.
    echo.
    echo     Please open Docker Desktop, wait until it says
    echo     "Engine running", then run this file again.
    echo.
    pause
    exit /b 1
)

echo Starting Mizan. The first time takes a few minutes.
echo جارٍ التشغيل. المرة الأولى تستغرق عدة دقائق.
echo.

docker compose up -d --build
if errorlevel 1 (
    echo.
    echo [!] Something went wrong. Send the message above to your administrator.
    echo.
    pause
    exit /b 1
)

echo.
echo Waiting for the system to become ready...
echo جارٍ انتظار جاهزية النظام...

for /l %%i in (1,1,60) do (
    docker compose exec -T mizan wget -q --spider http://127.0.0.1:8080/up >nul 2>&1
    if not errorlevel 1 goto ready
    timeout /t 5 /nobreak >nul
)

echo.
echo [!] The system did not come up in time. Run show-logs.bat to see why.
pause
exit /b 1

:ready
echo.
echo ==================================================
echo   Mizan is running.   النظام يعمل الآن.
echo   http://localhost:8080
echo ==================================================
echo.
echo It stays running in the background and starts again
echo by itself whenever this computer is switched on.
echo.
start "" http://localhost:8080
pause
