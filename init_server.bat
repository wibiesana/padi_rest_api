@echo off
echo Starting Padi REST API...
echo Server will run on http://localhost:8085
echo Press Ctrl+C to stop the server
echo.
cd ../public
php -S localhost:8085
