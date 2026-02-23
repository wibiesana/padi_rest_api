@echo off
REM Run the Padi CLI init command
php "%~dp0padi" init

IF %ERRORLEVEL% EQU 0 (
    echo.
    echo Setup completed via Padi CLI. Happy coding!
) ELSE (
    echo.
    echo Setup finished with errors. Exit code %ERRORLEVEL%.
)

pause