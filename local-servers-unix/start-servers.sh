#!/bin/bash

echo "Iniciando servidores..."

# Cambiar al directorio del script
cd "$(dirname "$0")"

# Iniciar API Server
echo "Iniciando API Server..."
node api-server/index.js &
API_PID=$!
echo "API Server iniciado en puerto 3000 (PID: $API_PID)"

# Iniciar Email Server
echo "Iniciando Email Server..."
node email-server/index.js &
EMAIL_PID=$!
echo "Email Server iniciado en puerto 3001 (PID: $EMAIL_PID)"

# Iniciar Pomelo Mock Server
echo "Iniciando Pomelo Mock Server..."
node pomelo-mock/index.js &
POMELO_PID=$!
echo "Pomelo Mock Server iniciado en puerto 3002 (PID: $POMELO_PID)"

echo
echo "Todos los servidores están corriendo."
echo "Puedes probar los endpoints en:"
echo "- http://localhost:3000/api/user/v1"
echo "- http://localhost:3001/email"
echo "- http://localhost:3002/pomelo/token"
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