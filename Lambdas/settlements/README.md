# Lambda Settlements API

Esta Lambda proporciona endpoints para generar reportes de settlements vs transacciones utilizando la API de Pomelo.

## Estructura del Proyecto

```
settlements/
├── pom.xml                          # Configuración de Maven
├── src/
│   ├── assembly/
│   │   └── bin.xml                  # Configuración de assembly para Lambda
│   ├── main/
│   │   ├── java/
│   │   │   └── com/fisinter/settlements/
│   │   │       ├── SettlementsApplication.java    # Clase principal Spring Boot
│   │   │       ├── StreamLambdaHandler.java       # Handler de Lambda
│   │   │       ├── controller/
│   │   │       │   └── SettlementsController.java # Controlador REST
│   │   │       ├── model/
│   │   │       │   ├── SettlementData.java        # Modelo de datos para settlements
│   │   │       │   ├── TransactionData.java       # Modelo de datos para transacciones
│   │   │       │   └── SettlementsReportResponse.java # Modelo de respuesta
│   │   │       └── service/
│   │   │           └── SettlementsService.java    # Lógica de negocio
│   │   └── resources/
│   │       └── application.properties # Configuración de la aplicación
│   └── test/
│       └── java/
│           └── com/fisinter/settlements/
│               └── SettlementsApplicationTests.java
└── README.md                        # Este archivo
```

## Endpoints Disponibles

### 1. Reporte Combinado
```
GET /api/settlements/report
```
Genera un reporte combinado de settlements y transacciones.

**Parámetros:**
- `start_date` (opcional): Fecha de inicio (YYYY-MM-DD)
- `end_date` (opcional): Fecha de fin (YYYY-MM-DD)
- `account_id` (opcional): ID de la cuenta
- `page` (opcional): Número de página (default: 1)
- `size` (opcional): Tamaño de página (default: 10)

### 2. Settlements Específicos
```
GET /api/settlements/settlements
```
Obtiene solo los settlements.

**Parámetros:** Mismos que el reporte combinado.

### 3. Transacciones Específicas
```
GET /api/settlements/transactions
```
Obtiene solo las transacciones.

**Parámetros:** Mismos que el reporte combinado.

### 4. Health Check
```
GET /api/settlements/health
```
Verifica el estado del servicio.

## Configuración

### Variables de Entorno Requeridas

Para producción, configurar las siguientes variables de entorno en AWS Lambda:

- `POMELO_CLIENT_ID`: Client ID de Pomelo
- `POMELO_CLIENT_SECRET`: Client Secret de Pomelo
- `POMELO_USERNAME`: Usuario de Pomelo
- `POMELO_PASSWORD`: Contraseña de Pomelo

### Configuración Local

Para desarrollo local, crear un archivo `application-local.properties`:

```properties
pomelo.api.client-id=tu-client-id
pomelo.api.client-secret=tu-client-secret
pomelo.api.username=tu-username
pomelo.api.password=tu-password
```

## Compilación y Despliegue

### Compilación Local
```bash
mvn clean package
```

### Despliegue a AWS Lambda

1. **Crear el archivo ZIP:**
```bash
mvn clean package -P assembly-zip
```

2. **Subir a AWS Lambda:**
   - Ir a AWS Lambda Console
   - Crear nueva función o actualizar existente
   - Subir el archivo ZIP generado en `target/settlements-1.0.0-bin.zip`
   - Configurar el handler: `com.fisinter.settlements.StreamLambdaHandler::handleRequest`
   - Configurar las variables de entorno

### Configuración de Lambda

- **Runtime:** Java 17
- **Handler:** `com.fisinter.settlements.StreamLambdaHandler::handleRequest`
- **Memory:** 512 MB (recomendado)
- **Timeout:** 30 segundos
- **Architecture:** x86_64

## Integración con API Gateway

Configurar API Gateway para exponer los endpoints:

```
POST /settlements/report
GET /settlements/settlements
GET /settlements/transactions
GET /settlements/health
```

## Autenticación

La Lambda maneja automáticamente la autenticación OAuth2 con Pomelo:
- Obtiene token automáticamente
- Renueva token cuando expira
- Maneja errores de autenticación

## Respuestas

### Formato de Respuesta Exitosa
```json
{
  "success": true,
  "message": "Reporte generado exitosamente",
  "data": [...],
  "summary": {
    "total_settlements": 1000.0,
    "total_transactions": 950.0,
    "total_movements": 1950.0,
    "settlements_count": 10,
    "transactions_count": 15,
    "movements_count": 25,
    "difference": 50.0
  },
  "pagination": {
    "page": 1,
    "size": 10,
    "total": 25,
    "total_pages": 3
  }
}
```

### Formato de Respuesta de Error
```json
{
  "success": false,
  "message": "Error específico del error"
}
```

## Logging

La Lambda utiliza SLF4J para logging. Los logs se envían a CloudWatch.

**Niveles de Log:**
- `INFO`: Operaciones normales
- `ERROR`: Errores y excepciones
- `WARN`: Advertencias

## Monitoreo

### Métricas Recomendadas
- Tiempo de respuesta
- Tasa de error
- Uso de memoria
- Número de invocaciones

### Alertas
- Errores de autenticación con Pomelo
- Timeouts de respuesta
- Errores 5xx

## Seguridad

- Las credenciales se manejan como variables de entorno
- No se almacenan en el código
- Se recomienda usar AWS Secrets Manager para producción
- CORS configurado para permitir acceso desde el frontend

## Troubleshooting

### Problemas Comunes

1. **Error de autenticación:**
   - Verificar credenciales de Pomelo
   - Revisar logs de CloudWatch

2. **Timeout:**
   - Aumentar timeout de Lambda
   - Verificar conectividad con Pomelo

3. **Error de memoria:**
   - Aumentar memoria asignada
   - Optimizar consultas

### Logs de Debug

Para habilitar logs detallados, agregar en `application.properties`:
```properties
logging.level.com.fisinter.settlements=DEBUG
```

## Desarrollo

### Ejecutar Localmente
```bash
mvn spring-boot:run
```

### Tests
```bash
mvn test
```

### Estructura de Tests
- Tests unitarios para servicios
- Tests de integración para controladores
- Tests de configuración 