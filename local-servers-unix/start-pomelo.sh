#!/bin/bash

echo "Iniciando servidor Pomelo..."

# Cambiar al directorio del script
cd "$(dirname "$0")"

# Iniciar el servidor Pomelo
node pomelo-mock/index.js 