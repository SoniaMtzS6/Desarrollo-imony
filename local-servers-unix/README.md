# Servidores Locales para Desarrollo

Este directorio contiene los servidores mock para desarrollo local del proyecto Adminfinister.

## Estructura

```
local-servers/
├── api-server/     # Servidor principal (puerto 3000)
├── email-server/   # Servidor de correo (puerto 3001)
└── pomelo-mock/    # Servidor mock de Pomelo (puerto 3002)
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
   npm run start:api     # Inicia solo el servidor API
   npm run start:email   # Inicia solo el servidor de correo
   npm run start:pomelo  # Inicia solo el mock de Pomelo
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

## Datos de Prueba

Los servidores incluyen datos mock para pruebas. Puedes encontrar estos datos en los archivos de cada servidor. 