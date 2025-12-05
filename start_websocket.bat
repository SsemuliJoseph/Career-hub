@echo off
echo =========================================
echo   Career Hub - WebSocket Server Starter
echo =========================================
echo.

REM Check common WAMP PHP paths
set PHP_EXE=

REM Check if PHP is in PATH first
where php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    set PHP_EXE=php
    goto :run_server
)

REM Check for WAMP64 installations
if exist "C:\wamp64\bin\php" (
    for /f "delims=" %%i in ('dir /b /ad "C:\wamp64\bin\php\php*" 2^>nul') do (
        if exist "C:\wamp64\bin\php\%%i\php.exe" (
            set PHP_EXE=C:\wamp64\bin\php\%%i\php.exe
            goto :run_server
        )
    )
)

REM Check for WAMP installations
if exist "C:\wamp\bin\php" (
    for /f "delims=" %%i in ('dir /b /ad "C:\wamp\bin\php\php*" 2^>nul') do (
        if exist "C:\wamp\bin\php\%%i\php.exe" (
            set PHP_EXE=C:\wamp\bin\php\%%i\php.exe
            goto :run_server
        )
    )
)

REM Check for XAMPP
if exist "C:\xampp\php\php.exe" (
    set PHP_EXE=C:\xampp\php\php.exe
    goto :run_server
)

:not_found
echo ERROR: PHP not found!
echo.
echo WAMP is installed but PHP was not found automatically.
echo.
echo Please find your PHP path manually:
echo   1. Open WAMP control panel
echo   2. Left-click WAMP icon in system tray
echo   3. Go to PHP -^> PHP folder
echo   4. Note the path (usually C:\wamp64\bin\php\phpX.X.XX)
echo.
echo Then run this command manually:
echo   "C:\wamp64\bin\php\phpX.X.XX\php.exe" websocket_server.php
echo.
echo Replace phpX.X.XX with your actual PHP version folder
echo.
pause
exit /b 1

:run_server
echo Found PHP: %PHP_EXE%
echo.
echo Starting WebSocket server...
echo Keep this window open! Press Ctrl+C to stop.
echo =========================================
echo.
"%PHP_EXE%" "%~dp0websocket_server.php"
pause
