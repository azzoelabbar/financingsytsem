@echo off
chcp 65001 >nul
title Mizan - السجل

cd /d "%~dp0"

echo The last 200 lines from Mizan. Press Ctrl+C to close.
echo آخر 200 سطر من سجل النظام. اضغط Ctrl+C للإغلاق.
echo.

docker compose logs -f --tail 200
