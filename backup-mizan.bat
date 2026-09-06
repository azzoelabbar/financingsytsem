@echo off
chcp 65001 >nul
title Mizan - نسخة احتياطية

cd /d "%~dp0"

if not exist "backups" mkdir "backups"

for /f "tokens=1-3 delims=/- " %%a in ("%date%") do set STAMP=%%c-%%b-%%a
set STAMP=%STAMP%_%time:~0,2%%time:~3,2%
set STAMP=%STAMP: =0%

echo Making a backup...  جارٍ إنشاء نسخة احتياطية...
echo.

docker compose exec -T mizan sh -c "cd /data && tar -cf - ." > "backups\mizan-%STAMP%.tar"
if errorlevel 1 (
    echo.
    echo [!] Backup failed. Is Mizan running? Start it with start-mizan.bat first.
    echo.
    pause
    exit /b 1
)

echo.
echo Saved to:  backups\mizan-%STAMP%.tar
echo تم الحفظ في المجلد backups
echo.
echo Keep a copy of this file somewhere else — an external drive or cloud storage.
echo.
pause
