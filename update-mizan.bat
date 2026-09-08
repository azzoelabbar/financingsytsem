@echo off
chcp 65001 >nul
title Mizan - تحديث النظام

cd /d "%~dp0"

echo ==================================================
echo    ميزان - تحديث النظام / Updating Mizan
echo ==================================================
echo.

docker info >nul 2>&1
if errorlevel 1 (
    echo [!] Docker Desktop is not running. Open it first.
    echo     برنامج Docker غير مشغل. افتحه أولاً.
    pause
    exit /b 1
)

if exist ".git" (
    echo Downloading the latest code from GitHub...
    echo جارٍ تنزيل آخر نسخة من الكود...
    echo.
    git pull
    if errorlevel 1 (
        echo.
        echo [!] Could not download the update. Check your internet connection,
        echo     or that you have no uncommitted local changes.
        echo.
        pause
        exit /b 1
    )
    echo.
)

echo Rebuilding. This takes a few minutes.
echo جارٍ إعادة البناء. يستغرق عدة دقائق.
echo.

docker compose up -d --build
if errorlevel 1 (
    echo.
    echo [!] The rebuild failed. Send the message above to your administrator.
    echo     The previous version is still running.
    echo.
    pause
    exit /b 1
)

echo.
echo Waiting for the system to come back...
echo جارٍ انتظار عودة النظام...

for /l %%i in (1,1,60) do (
    docker compose exec -T mizan wget -q --spider http://127.0.0.1:8080/up >nul 2>&1
    if not errorlevel 1 goto ready
    timeout /t 5 /nobreak >nul
)

echo.
echo [!] It did not come back in time. Run show-logs.bat to see why.
pause
exit /b 1

:ready
echo.
echo ==================================================
echo   Updated.   تم التحديث.
echo   http://localhost:8080
echo ==================================================
echo.
echo Database updates were applied automatically.
echo All your data is unchanged.
echo.
echo تم تطبيق تحديثات قاعدة البيانات تلقائياً.
echo جميع بياناتك كما هي.
echo.
pause
