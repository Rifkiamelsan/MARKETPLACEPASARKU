@echo off
title Menjalankan PasarKu - Marketplace Tradisional
color 0A
echo ===================================================================
echo             PASARKU - MARKETPLACE TRADISIONAL
echo         Server Lokal PHP Native & MySQLi (Port 8000)
echo ===================================================================
echo.
echo Memeriksa instalasi PHP di XAMPP...
set PHP_BIN=D:\xampp\php\php.exe

if not exist "%PHP_BIN%" (
    set PHP_BIN=C:\xampp\php\php.exe
)

if not exist "%PHP_BIN%" (
    set PHP_BIN=php
)

echo Menggunakan PHP dari: %PHP_BIN%
echo.
echo Pastikan MySQL di XAMPP Control Panel sudah berstatus "Running" (Hijau).
echo Membuka aplikasi di browser: http://localhost:8000/
echo.
echo Tekan Ctrl+C di jendela ini jika ingin menghentikan server.
echo ===================================================================

start http://localhost:8000/
"%PHP_BIN%" -S localhost:8000
pause
