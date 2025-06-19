#!/bin/bash

echo "Iniciando servidores locales de Adminfinister..."
echo

# Cambiar al directorio del script
cd "$(dirname "$0")"

echo "Instalando dependencias..."
npm install

echo
echo "Iniciando servidores..."
echo "- API Server (puerto 3000)"
echo "- Email Server (puerto 3001)"
echo "- Pomelo Mock (puerto 3002)"
echo

# Iniciar servidores en background
echo "Iniciando API Server..."
npm run start:api &
API_PID=$!

echo "Iniciando Email Server..."
npm run start:email &
EMAIL_PID=$!

echo "Iniciando Pomelo Mock..."
npm run start:pomelo &
POMELO_PID=$!

echo
echo "Servidores iniciados con PIDs:"
echo "- API Server: $API_PID"
echo "- Email Server: $EMAIL_PID"
echo "- Pomelo Mock: $POMELO_PID"
echo
echo "Presiona Ctrl+C para detener todos los servidores..."

# Función para limpiar al salir
cleanup() {
    echo
    echo "Deteniendo servidores..."
    kill $API_PID 2>/dev/null
    kill $EMAIL_PID 2>/dev/null
    kill $POMELO_PID 2>/dev/null
    echo "Servidores detenidos."
    exit 0
}

# Capturar Ctrl+C
trap cleanup SIGINT

# Mantener el script corriendo
wait 