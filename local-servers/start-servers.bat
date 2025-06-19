@echo off
echo Iniciando servidores...

:: Iniciar API Server
start "API Server" "C:\Program Files\nodejs\node.exe" "%~dp0api-server/index.js"
echo API Server iniciado en puerto 3000

:: Iniciar Email Server
start "Email Server" "C:\Program Files\nodejs\node.exe" "%~dp0email-server/index.js"
echo Email Server iniciado en puerto 3001

:: Iniciar Pomelo Mock Server
start "Pomelo Mock" "C:\Program Files\nodejs\node.exe" "%~dp0pomelo-mock/index.js"
echo Pomelo Mock Server iniciado en puerto 3002

echo.
echo Todos los servidores están corriendo.
echo Puedes probar los endpoints en:
echo - http://localhost:3000/api/user/v1
echo - http://localhost:3001/email
echo - http://localhost:3002/pomelo/token
echo.
echo Presiona cualquier tecla para detener los servidores...
pause 