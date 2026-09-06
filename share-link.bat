@echo off
chcp 65001 >nul
title Mizan - رابط المشاركة

cd /d "%~dp0"

echo ==================================================
echo    ميزان - رابط المشاركة / Shareable link
echo ==================================================
echo.

docker info >nul 2>&1
if errorlevel 1 (
    echo [!] Docker Desktop is not running. Open it first.
    echo     برنامج Docker غير مشغل. افتحه أولاً.
    pause
    exit /b 1
)

docker compose ps --status running --services 2>nul | findstr /x "mizan" >nul
if errorlevel 1 (
    echo [!] Mizan is not running. Run start-mizan.bat first.
    echo     النظام غير مشغل. شغّل start-mizan.bat أولاً.
    pause
    exit /b 1
)

echo Creating the link...  جارٍ إنشاء الرابط...
echo.

docker compose --profile quick-link up -d --force-recreate quick-link
if errorlevel 1 (
    echo [!] Could not start the link service.
    pause
    exit /b 1
)

set "LINK="
for /f "usebackq delims=" %%u in (`powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0docker\find-link.ps1"`) do set "LINK=%%u"

if not defined LINK (
    echo.
    echo [!] The link was not ready in time. Try again, or run:
    echo     docker compose logs quick-link
    echo.
    pause
    exit /b 1
)

<nul set /p "=%LINK%" | clip

echo.
echo ==================================================
echo.
echo   %LINK%
echo.
echo ==================================================
echo.
echo   Share this address. Copied to the clipboard.
echo   شارك هذا العنوان. تم نسخه إلى الحافظة.
echo.
echo   It works only while this computer is on and Mizan
echo   is running, and it CHANGES every time you run this
echo   file - so send the new address each time.
echo.
echo   يعمل فقط أثناء تشغيل هذا الجهاز، ويتغير في كل مرة
echo   تشغّل فيها هذا الملف.
echo.
echo   To turn sharing off:  stop-sharing.bat
echo   لإيقاف المشاركة: stop-sharing.bat
echo.
pause
