#!/bin/bash

# Cambiar al directorio del script
cd "$(dirname "$0")"

echo "Instalando dependencias del Email Server..."
npm install

echo "Iniciando Email Server..."
node index.js 