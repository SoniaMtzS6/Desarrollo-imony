@echo off
echo Iniciando servidores locales de Adminfinister...
echo.

cd /d %~dp0

echo Instalando dependencias del servidor de correo...
cd local-servers\email-server
call npm install express cors body-parser
start "Email Server" cmd /c "node index.js"

echo.
echo Instalando dependencias del servidor API...
cd ..\api-server
call npm install express cors body-parser
start "API Server" cmd /c "node index.js"

echo.
echo Instalando dependencias del servidor Pomelo...
cd ..\pomelo-mock
call npm install express cors body-parser
start "Pomelo Mock" cmd /c "node index.js"

echo.
echo Servidores iniciados:
echo - Email Server: http://localhost:3001
echo - API Server: http://localhost:3000
echo - Pomelo Mock: http://localhost:3002
echo.
echo Presiona cualquier tecla para cerrar esta ventana...
pause > nul 