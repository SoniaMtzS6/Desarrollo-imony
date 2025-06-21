# Integración de Settlements API - Entornos Local y Producción

Este documento explica cómo configurar y usar tanto el entorno local como el de producción para el servicio de Settlements.

## 🏗️ Arquitectura

### Entorno Local
- **Servidor:** Node.js en `local-servers/settlements-server/`
- **Puerto:** 3003
- **Configuración:** Variables de entorno en `.env`

### Entorno Producción
- **Servidor:** AWS Lambda en `Lambdas/settlements/`
- **URL:** API Gateway configurado
- **Configuración:** Variables de entorno en AWS Lambda

## 📁 Estructura de Archivos

```
servicios/
├── config_settlements.php          # Configuración principal (ambos entornos)
├── reporte_settlements.php         # Reporte original (solo Pomelo directo)
├── reporte_settlements_v2.php      # Reporte nuevo (usa API)
├── exportar_settlements.php        # Exportación
├── env.example                     # Ejemplo de variables de entorno
├── .env                           # Variables de entorno (crear localmente)
└── README_INTEGRACION.md          # Este archivo

Lambdas/settlements/               # Lambda para producción
local-servers/settlements-server/  # Servidor local para desarrollo
```

## ⚙️ Configuración

### 1. Variables de Entorno

Crear archivo `.env` en la carpeta `servicios/`:

```bash
# Copiar el archivo de ejemplo
cp servicios/env.example servicios/.env
```

Editar `servicios/.env`:

```env
# Entorno: 'local' para desarrollo, 'production' para producción
SETTLEMENTS_ENV=local

# URL de la Lambda en producción (reemplazar con tu URL real)
SETTLEMENTS_LAMBDA_URL=https://your-api-gateway-url.amazonaws.com/prod

# URL del servidor local
SETTLEMENTS_LOCAL_URL=http://localhost:3003

# Configuración de Pomelo (solo para servidor local)
POMELO_BASE_URL=https://api.pomelo.la
POMELO_CLIENT_ID=tu-client-id
POMELO_CLIENT_SECRET=tu-client-secret
POMELO_USERNAME=tu-username
POMELO_PASSWORD=tu-password
```

### 2. Configuración de Entornos

#### Para Desarrollo Local:
```env
SETTLEMENTS_ENV=local
```

#### Para Producción:
```env
SETTLEMENTS_ENV=production
```

## 🚀 Uso

### Desarrollo Local

1. **Iniciar servidor local:**
```bash
cd local-servers/settlements-server
npm install
npm start
```

2. **Configurar entorno:**
```env
SETTLEMENTS_ENV=local
```

3. **Acceder al reporte:**
```
http://localhost/servicios/reporte_settlements_v2.php?id_empresa=1
```

### Producción

1. **Desplegar Lambda:**
```bash
cd Lambdas/settlements
mvn clean package -P assembly-zip
# Subir ZIP a AWS Lambda
```

2. **Configurar entorno:**
```env
SETTLEMENTS_ENV=production
SETTLEMENTS_LAMBDA_URL=https://tu-api-gateway.amazonaws.com/prod
```

3. **Acceder al reporte:**
```
https://tu-dominio.com/servicios/reporte_settlements_v2.php?id_empresa=1
```

## 🔄 Cambio de Entornos

### Opción 1: Cambio Manual
Editar el archivo `.env`:
```env
# Para desarrollo
SETTLEMENTS_ENV=local

# Para producción
SETTLEMENTS_ENV=production
```

### Opción 2: Cambio Automático
El sistema puede detectar automáticamente el entorno basado en el dominio:

```php
// En config_settlements.php
$isLocal = $_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '.local') !== false;
define('SETTLEMENTS_ENV', $isLocal ? 'local' : 'production');
```

## 📊 Reportes Disponibles

### Reporte Original (solo Pomelo directo)
- **Archivo:** `reporte_settlements.php`
- **Uso:** Llamadas directas a Pomelo API
- **Ventaja:** No requiere servidor adicional
- **Desventaja:** Más lento, sin paginación

### Reporte Nuevo (con API)
- **Archivo:** `reporte_settlements_v2.php`
- **Uso:** Usa la API (local o Lambda)
- **Ventaja:** Más rápido, con paginación, mejor UX
- **Desventaja:** Requiere servidor adicional

## 🔧 Endpoints de la API

### Reporte Combinado
```
GET /api/settlements/report
```

### Settlements Específicos
```
GET /api/settlements/settlements
```

### Transacciones Específicas
```
GET /api/settlements/transactions
```

### Health Check
```
GET /api/settlements/health
```

## 🛠️ Troubleshooting

### Problemas Comunes

1. **Error de conexión:**
   - Verificar que el servidor local esté corriendo
   - Verificar URL de la Lambda en producción
   - Revisar configuración de CORS

2. **Error de autenticación:**
   - Verificar credenciales de Pomelo
   - Revisar variables de entorno

3. **Datos vacíos:**
   - Verificar fechas de filtro
   - Verificar ID de empresa
   - Revisar logs del servidor

### Logs de Debug

Para habilitar logs detallados:

```php
// En config_settlements.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Verificar Estado del Servicio

```php
$health = checkSettlementsHealth();
var_dump($health);
```

## 🔒 Seguridad

### Variables de Entorno
- **Nunca** commitear archivo `.env` al repositorio
- Usar `.env.example` como plantilla
- Configurar variables en AWS Lambda para producción

### CORS
- Configurado para permitir acceso desde el frontend
- Restringir en producción según necesidades

### SSL
- Verificación SSL habilitada en producción
- Deshabilitada en desarrollo local

## 📈 Monitoreo

### Métricas a Monitorear
- Tiempo de respuesta de la API
- Tasa de error
- Uso de memoria
- Número de invocaciones

### Alertas Recomendadas
- Errores de autenticación
- Timeouts de respuesta
- Errores 5xx

## 🚀 Despliegue

### Checklist de Despliegue

- [ ] Lambda compilada y desplegada
- [ ] API Gateway configurado
- [ ] Variables de entorno configuradas
- [ ] CORS configurado
- [ ] Health check funcionando
- [ ] Reporte v2 probado
- [ ] Exportación funcionando

### Rollback
Si hay problemas, cambiar temporalmente a:
```env
SETTLEMENTS_ENV=local
```

Y usar el reporte original:
```
reporte_settlements.php
```

## 📞 Soporte

Para problemas o preguntas:
1. Revisar logs del servidor
2. Verificar configuración de entorno
3. Probar endpoints individualmente
4. Revisar documentación de la Lambda 