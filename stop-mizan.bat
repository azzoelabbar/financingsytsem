@echo off
chcp 65001 >nul
title Mizan - إيقاف النظام

cd /d "%~dp0"

echo Stopping Mizan...  جارٍ إيقاف النظام...
echo.

docker compose down

echo.
echo Mizan is stopped. Your data is kept safe.
echo تم الإيقاف. بياناتك محفوظة.
echo.
echo Run start-mizan.bat to start it again.
echo.
pause
