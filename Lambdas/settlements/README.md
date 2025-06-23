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
│   │   │       ├── entity/
│   │   │       │   └── Properties.java            # Entidad para tabla properties
│   │   │       ├── model/
│   │   │       │   ├── SettlementData.java        # Modelo de datos para settlements
│   │   │       │   ├── TransactionData.java       # Modelo de datos para transacciones
│   │   │       │   └── SettlementsReportResponse.java # Modelo de respuesta
│   │   │       ├── repository/
│   │   │       │   └── PropertiesRepository.java  # Repositorio para properties
│   │   │       └── service/
│   │   │           └── SettlementsService.java    # Lógica de negocio
│   │   └── resources/
│   │       └── application.properties # Configuración de la aplicación
│   └── test/
│       └── java/
│           └── com/fisinter/settlements/
│               └── SettlementsApplicationTests.java
├── settlements_properties.sql       # Script para propiedades de BD
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

### Base de Datos RDS

La Lambda se conecta a la base de datos RDS para obtener las credenciales de Pomelo desde la tabla `properties`.

**Configuración de BD en application.properties:**
```properties
spring.datasource.url=jdbc:mysql://finister.cfeq6oo6ouvl.us-east-2.rds.amazonaws.com:3306/fisinter
spring.datasource.username=fisinteradmin
spring.datasource.password=RuG22twX7x6Nufxpdaxo
spring.datasource.driver-class-name=com.mysql.cj.jdbc.Driver
spring.jpa.database-platform=org.hibernate.dialect.MySQLDialect
```

### Propiedades Requeridas en la Tabla Properties

La Lambda requiere las siguientes propiedades en la tabla `properties`:

- `base.url`: URL base de la API de Pomelo (https://api.pomelo.la)
- `client.id`: Client ID de Pomelo
- `client.secret`: Client Secret de Pomelo
- `audience`: Audience de Pomelo (https://auth-prod.pomelo.la)
- `grant.type`: Tipo de grant (client_credentials)
- `token.uri.solicitar`: URI para solicitar token (/oauth/token)
- `movement.uri`: URI para transacciones (/core/transactions/v1)
- `settlement.uri`: URI para settlements (/core/settlements/v1) - se inserta automáticamente

**Ejecutar el script SQL:**
```sql
-- Ver settlements_properties.sql para el script completo
INSERT IGNORE INTO properties (name, value) 
VALUES ('settlement.uri', '/core/settlements/v1');
```

### Variables de Entorno (Opcionales)

Para desarrollo local, se pueden configurar variables de entorno:

- `SETTLEMENTS_ENV`: Entorno (local/production)
- `SETTLEMENTS_LAMBDA_URL`: URL de la Lambda en producción
- `SETTLEMENTS_LOCAL_URL`: URL del servidor local

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

### Configuración de Lambda

- **Runtime:** Java 17
- **Handler:** `com.fisinter.settlements.StreamLambdaHandler::handleRequest`
- **Memory:** 1024 MB (recomendado)
- **Timeout:** 60 segundos
- **Architecture:** x86_64

### Configuración de IAM

La Lambda necesita permisos para:
- Conectar a RDS MySQL
- Escribir logs en CloudWatch
- Acceder a Secrets Manager (opcional)

**Política IAM mínima:**
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "logs:CreateLogGroup",
                "logs:CreateLogStream",
                "logs:PutLogEvents"
            ],
            "Resource": "arn:aws:logs:*:*:*"
        },
        {
            "Effect": "Allow",
            "Action": [
                "ec2:CreateNetworkInterface",
                "ec2:DescribeNetworkInterfaces",
                "ec2:DeleteNetworkInterface"
            ],
            "Resource": "*"
        }
    ]
}
```

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
- Obtiene credenciales desde la base de datos
- Obtiene token automáticamente usando client_credentials
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
- Conexiones a base de datos

### Alertas
- Errores de autenticación con Pomelo
- Timeouts de respuesta
- Errores 5xx
- Errores de conexión a RDS

## Seguridad

- Las credenciales se manejan desde la base de datos RDS
- No se almacenan en el código
- CORS configurado para dominios específicos
- Conexiones a RDS encriptadas

## Troubleshooting

### Problemas Comunes

1. **Error de autenticación:**
   - Verificar credenciales en la tabla properties
   - Revisar logs de CloudWatch
   - Verificar conectividad con Pomelo

2. **Error de conexión a BD:**
   - Verificar configuración de RDS
   - Revisar Security Groups
   - Verificar credenciales de BD

3. **Timeout:**
   - Aumentar timeout de Lambda
   - Verificar conectividad con Pomelo
   - Optimizar consultas a BD

4. **Error de memoria:**
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

## Checklist para Producción

- [x] Configuración de base de datos RDS
- [x] Propiedades de Pomelo en tabla properties
- [x] Configuración de CORS segura
- [x] Manejo de errores robusto
- [x] Logging apropiado
- [ ] Configuración de API Gateway
- [ ] Configuración de IAM roles
- [ ] Tests de integración
- [ ] Monitoreo y alertas
- [ ] Documentación de deployment 