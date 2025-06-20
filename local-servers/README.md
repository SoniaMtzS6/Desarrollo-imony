# Servidores Locales para Desarrollo

Este directorio contiene los servidores mock para desarrollo local del proyecto Adminfinister.

## Estructura

```
local-servers/
├── api-server/         # Servidor principal (puerto 3000)
├── email-server/       # Servidor de correo (puerto 3001)
├── pomelo-mock/        # Servidor mock de Pomelo (puerto 3002)
└── settlements-server/ # Servidor de Settlements (puerto 3003)
```

## Instalación

1. Asegúrate de tener Node.js instalado
2. Abre una terminal en este directorio
3. Ejecuta:
   ```bash
   npm install
   ```

## Uso

Puedes iniciar los servidores de tres formas:

1. Todos los servidores a la vez:
   ```bash
   npm run start:all
   ```

2. Servidores individuales:
   ```bash
   npm run start:api         # Inicia solo el servidor API
   npm run start:email       # Inicia solo el servidor de correo
   npm run start:pomelo      # Inicia solo el mock de Pomelo
   npm run start:settlements # Inicia solo el servidor de Settlements
   ```

3. Usando el script de Windows:
   ```bash
   start-local-servers.bat
   ```

## Endpoints Disponibles

### API Server (http://localhost:3000)
- GET `/api/user/v1` - Lista de usuarios
- GET `/api/card/v1` - Lista de tarjetas

### Email Server (http://localhost:3001)
- POST `/email` - Envío de correo individual
- POST `/email/sendmailmasivo` - Envío de correo masivo

### Pomelo Mock (http://localhost:3002)
- POST `/pomelo/token` - Generación de token
- GET `/cards/v1/:cardId` - Información de tarjeta
- GET `/core/accounts/v1/:accountId` - Información de cuenta
- POST `/cards/associations/v1` - Asociación de tarjetas
- POST `/core/transactions/v1` - Procesamiento de transacciones

### Settlements Server (http://localhost:3003)
- GET `/api/settlements/report` - Reporte combinado de settlements y transacciones
- GET `/api/settlements/settlements` - Solo settlements
- GET `/api/settlements/transactions` - Solo transacciones
- GET `/api/settlements/health` - Health check

## Configuración del Servidor de Settlements

El servidor de Settlements requiere configuración de Pomelo. Puedes configurarlo de dos formas:

### 1. Variables de Entorno
```bash
export POMELO_BASE_URL=https://api.pomelo.la
export POMELO_CLIENT_ID=your-client-id
export POMELO_CLIENT_SECRET=your-client-secret
export POMELO_USERNAME=your-username
export POMELO_PASSWORD=your-password
```

### 2. Archivo .env
Crear archivo `.env` en la carpeta `servicios/`:
```env
POMELO_BASE_URL=https://api.pomelo.la
POMELO_CLIENT_ID=your-client-id
POMELO_CLIENT_SECRET=your-client-secret
POMELO_USERNAME=your-username
POMELO_PASSWORD=your-password
```

## Datos de Prueba

Los servidores incluyen datos mock para pruebas. Puedes encontrar estos datos en los archivos de cada servidor.

## Integración con el Frontend

Para usar el servidor de Settlements con el frontend:

1. Configurar el entorno en `servicios/.env`:
   ```env
   SETTLEMENTS_ENV=local
   SETTLEMENTS_LOCAL_URL=http://localhost:3003
   ```

2. Acceder al reporte:
   ```
   http://localhost/servicios/reporte_settlements_v2.php?id_empresa=1
   ``` 