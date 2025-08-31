@echo off
setlocal enabledelayedexpansion

for /r "c:\xampp\htdocs\ECADYB" %%f in (*.css) do (
    echo Updating: %%~nxf
    (
        echo /* Text selection disabled */
        echo * {
        echo   -webkit-user-select: none;  
        echo   -moz-user-select: none;    
        echo   -ms-user-select: none;     
        echo   user-select: none;         
        echo   -webkit-tap-highlight-color: transparent;
        echo   -webkit-touch-callout: none;
        echo}
        echo.
        type "%%f"
    ) > "%%f.new"
    move /y "%%f.new" "%%f" >nul
)

echo All CSS files have been updated.
pause
