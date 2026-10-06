@echo off
chcp 65001 >nul
echo ========================================================
echo   KHOI DONG FOOD FINDER WEB SERVER
echo ========================================================
echo.

set PHP_BIN=C:\xampp\php\php.exe

if not exist "%PHP_BIN%" (
    where php >nul 2>&1
    if %errorlevel% equ 0 (
        set PHP_BIN=php
    ) else (
        echo [LOI] Khong tim thay PHP tai C:\xampp\php\php.exe hoac trong PATH!
        echo Vui long kiem tra lai XAMPP.
        pause
        exit /b 1
    )
)

echo Dang mo trinh duyet tai http://localhost:8000 ...
start http://localhost:8000

echo.
echo Server dang chay tai http://localhost:8000
echo Nhan Ctrl+C de dung server.
echo.
"%PHP_BIN%" -S localhost:8000 -t "%~dp0public"
