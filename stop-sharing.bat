@echo off
chcp 65001 >nul
title Mizan - إيقاف المشاركة

cd /d "%~dp0"

echo Turning the shared link off...  جارٍ إيقاف رابط المشاركة...
echo.

docker compose --profile quick-link --profile link down --remove-orphans quick-link link 2>nul
docker rm -f mizan-quick-link mizan-link >nul 2>&1

echo.
echo Sharing is off. Mizan keeps running on this computer.
echo تم إيقاف المشاركة. النظام ما زال يعمل على هذا الجهاز.
echo.
echo Anyone holding the old address can no longer reach it.
echo لم يعد الرابط القديم صالحاً لأي شخص.
echo.
pause
