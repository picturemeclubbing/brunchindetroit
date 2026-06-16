@echo off
setlocal EnableExtensions

REM Brunch in Detroit - Local Site Runner
REM Opens the project folder, prints local setup instructions, checks common requirements,
REM then starts the PHP built-in server at http://localhost:8080

set "PROJECT_DIR=F:\brunch"
set "PHP_EXE=php"
set "XAMPP_PHP=C:\xampp\php\php.exe"
set "MYSQLADMIN_EXE=C:\xampp\mysql\bin\mysqladmin.exe"
set "MYSQL_EXE=C:\xampp\mysql\bin\mysql.exe"
set "DB_NAME=brunchindetroit"
set "LOCAL_URL=http://localhost:8080"

echo.
echo ==================================================
echo  Brunch in Detroit - Local Development Runner
echo ==================================================
echo.
echo This will run the site locally using PHP's built-in server.
echo.
echo Requirements:
echo   1. XAMPP MySQL should be running.
echo   2. Project folder should exist at: %PROJECT_DIR%
echo   3. Local database should be named: %DB_NAME%
echo   4. Stop the server anytime with CTRL + C.
echo.
echo Main local URLs:
echo   Home:       %LOCAL_URL%/
echo   Directory:  %LOCAL_URL%/directory.php
echo   Blog:       %LOCAL_URL%/blog.php
echo   Admin:      %LOCAL_URL%/admin/login.php
echo.
echo First-time database setup, only if needed:
echo   %MYSQL_EXE% --default-character-set=utf8mb4 -u root %DB_NAME% ^< database\schema.sql
echo   %MYSQL_EXE% --default-character-set=utf8mb4 -u root %DB_NAME% ^< database\seed.sql
echo   %MYSQL_EXE% --default-character-set=utf8mb4 -u root %DB_NAME% ^< database\demo-venues.sql
echo   %MYSQL_EXE% --default-character-set=utf8mb4 -u root %DB_NAME% ^< database\demo-menu.sql
echo   %MYSQL_EXE% --default-character-set=utf8mb4 -u root %DB_NAME% ^< database\demo-blog.sql
echo.
echo NOTE: This runner does NOT auto-import database files.
echo It only starts the local PHP website after showing the setup reminders.
echo.

cd /d "%PROJECT_DIR%" || (
    echo ERROR: Could not open project folder:
    echo %PROJECT_DIR%
    pause
    exit /b 1
)

if not exist "public\index.php" (
    echo ERROR: public\index.php was not found.
    echo Make sure this batch file points to the correct project folder.
    pause
    exit /b 1
)

where php >nul 2>&1
if errorlevel 1 (
    if exist "%XAMPP_PHP%" (
        set "PHP_EXE=%XAMPP_PHP%"
    ) else (
        echo ERROR: PHP was not found in PATH and XAMPP PHP was not found at:
        echo %XAMPP_PHP%
        echo.
        echo Install PHP, add PHP to PATH, or update this batch file's XAMPP_PHP path.
        pause
        exit /b 1
    )
)

echo Checking PHP...
"%PHP_EXE%" -v
echo.

if exist "%MYSQLADMIN_EXE%" (
    echo Checking XAMPP MySQL...
    "%MYSQLADMIN_EXE%" ping -u root >nul 2>&1
    if errorlevel 1 (
        echo WARNING: MySQL did not respond.
        echo Open XAMPP Control Panel and start MySQL before using DB-backed pages.
        echo You may still start the PHP server, but pages that need the database may fail.
        echo.
        choice /c YN /m "Start PHP server anyway"
        if errorlevel 2 (
            echo Canceled.
            pause
            exit /b 1
        )
    ) else (
        echo MySQL is running.
    )
) else (
    echo WARNING: Could not find mysqladmin at:
    echo %MYSQLADMIN_EXE%
    echo Skipping MySQL check.
)
echo.

echo Starting local site...
echo URL: %LOCAL_URL%
echo Press CTRL + C in this window to stop the server.
echo.

start "" "%LOCAL_URL%"
"%PHP_EXE%" -S localhost:8080 -t public

echo.
echo Local server stopped.
pause
endlocal
