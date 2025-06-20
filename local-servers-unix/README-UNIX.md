# Servidores Locales - Versión Unix/Linux

Esta carpeta contiene scripts de shell (`.sh`) equivalentes a los archivos `.bat` de Windows para ejecutar los servidores locales de Adminfinister.

## Scripts Disponibles

### 1. `start-all-servers.sh`
Inicia todos los servidores con gestión automática de dependencias:
- API Server (puerto 3000)
- Email Server (puerto 3001)
- Pomelo Mock (puerto 3002)
- Settlements Server (puerto 3003)

### 2. `start-servers.sh`
Inicia todos los servidores directamente (sin instalar dependencias):
- API Server (puerto 3000)
- Email Server (puerto 3001)
- Pomelo Mock (puerto 3002)
- Settlements Server (puerto 3003)

### 3. `start-pomelo.sh`
Inicia solo el servidor Pomelo Mock (puerto 3002)

### 4. `email-server/start-email-server.sh`
Inicia solo el servidor de email (puerto 3001)

## Instrucciones de Uso

### 1. Hacer los scripts ejecutables
```bash
chmod +x *.sh
chmod +x email-server/*.sh
```

### 2. Ejecutar los scripts
```bash
# Iniciar todos los servidores
./start-all-servers.sh

# O iniciar servidores individuales
./start-servers.sh
./start-pomelo.sh
./email-server/start-email-server.sh
```

### 3. Detener los servidores
- Presiona `Ctrl+C` para detener todos los servidores
- Los scripts incluyen manejo de señales para limpiar procesos automáticamente

## Endpoints Disponibles

Una vez iniciados los servidores, puedes probar:

- **API Server**: http://localhost:3000/api/user/v1
- **Email Server**: http://localhost:3001/email
- **Pomelo Mock**: http://localhost:3002/pomelo/token
- **Settlements Server**: http://localhost:3003/api/settlements/health

## Servidor de Settlements

El servidor de Settlements (puerto 3003) proporciona endpoints para generar reportes de settlements vs transacciones:

### Endpoints Principales:
- `GET /api/settlements/report` - Reporte combinado
- `GET /api/settlements/settlements` - Solo settlements
- `GET /api/settlements/transactions` - Solo transacciones
- `GET /api/settlements/health` - Health check

### Configuración:
El servidor requiere configuración de Pomelo en variables de entorno:
```bash
export POMELO_BASE_URL=https://api.pomelo.la
export POMELO_CLIENT_ID=your-client-id
export POMELO_CLIENT_SECRET=your-client-secret
export POMELO_USERNAME=your-username
export POMELO_PASSWORD=your-password
```

## Diferencias con la versión Windows

1. **Gestión de procesos**: Los scripts usan `&` para ejecutar en background y `wait` para mantener el script activo
2. **Manejo de señales**: Se usa `trap` para capturar `Ctrl+C` y limpiar procesos
3. **Rutas**: Se usa `$(dirname "$0")` para obtener el directorio del script
4. **Permisos**: Los scripts necesitan permisos de ejecución (`chmod +x`)

## Requisitos

- Node.js instalado
- npm disponible en el PATH
- Bash shell (común en Linux/macOS)

## Solución de Problemas

### Si los scripts no son ejecutables:
```bash
chmod +x *.sh
```

### Si hay problemas de permisos:
```bash
sudo chmod +x *.sh
```

### Para ver los procesos en ejecución:
```bash
ps aux | grep node
```

### Para matar procesos manualmente:
```bash
pkill -f "node.*index.js"
```

### Si el servidor de Settlements no inicia:
```bash
# Verificar dependencias
cd settlements-server
npm install

# Verificar configuración
echo $POMELO_CLIENT_ID
``` 