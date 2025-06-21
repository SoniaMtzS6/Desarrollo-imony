@echo off
echo Iniciando servidores locales de Adminfinister...
echo.

cd /d %~dp0

echo Instalando dependencias...
call npm install

echo.
echo Iniciando servidores...
echo - API Server (puerto 3000)
echo - Email Server (puerto 3001)
echo - Pomelo Mock (puerto 3002)
echo.

start "API Server" cmd /c "npm run start:api"
start "Email Server" cmd /c "npm run start:email"
start "Pomelo Mock" cmd /c "npm run start:pomelo"

echo.
echo Servidores iniciados. Presiona cualquier tecla para cerrar esta ventana...
pause > nul 