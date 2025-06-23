# Checklist de Deployment para Producción - Lambda Settlements

## ✅ Configuración de Base de Datos

- [x] Configuración de RDS en application.properties
- [x] Entidad Properties creada
- [x] PropertiesRepository implementado
- [x] Script SQL para propiedades creado

## ✅ Configuración de Credenciales

- [x] Credenciales de Pomelo en tabla properties
- [x] Autenticación client_credentials implementada
- [x] URLs dinámicas desde base de datos

## ✅ Configuración de Seguridad

- [x] CORS configurado para dominios específicos
- [x] Credenciales no hardcodeadas en código
- [x] Manejo de errores robusto

## 🔧 Configuración de AWS Lambda

### Crear/Actualizar Lambda Function

1. **Runtime:** Java 17
2. **Handler:** `com.fisinter.settlements.StreamLambdaHandler::handleRequest`
3. **Memory:** 1024 MB
4. **Timeout:** 60 segundos
5. **Architecture:** x86_64

### Variables de Entorno (Opcionales)
- `SETTLEMENTS_ENV`: production
- `SETTLEMENTS_LAMBDA_URL`: URL de API Gateway

### IAM Role
Crear rol con permisos mínimos:
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

## 🔧 Configuración de API Gateway

### Crear API Gateway
1. **API Type:** REST API
2. **Endpoint Type:** Regional

### Crear Resources y Methods
```
POST /settlements/report
GET /settlements/settlements
GET /settlements/transactions
GET /settlements/health
```

### Configurar CORS
- **Access-Control-Allow-Origin:** https://adminfinister.com,https://www.adminfinister.com
- **Access-Control-Allow-Methods:** GET,POST,OPTIONS
- **Access-Control-Allow-Headers:** Content-Type,Authorization,X-Requested-With

### Configurar Integration
- **Integration Type:** Lambda Function
- **Lambda Function:** settlements-lambda
- **Use Lambda Proxy integration:** Yes

## 🔧 Configuración de RDS

### Security Groups
- Permitir acceso desde Lambda VPC
- Puerto 3306 (MySQL)

### Subnet Groups
- Configurar en subnets privadas
- Mismo VPC que Lambda

## 🔧 Configuración de VPC

### Lambda VPC Configuration
- **VPC:** Mismo que RDS
- **Subnets:** Subnets privadas
- **Security Groups:** Permitir salida a internet y acceso a RDS

## 📋 Pasos de Deployment

### 1. Compilar y Empaquetar
```bash
cd Lambdas/settlements
mvn clean package -P assembly-zip
```

### 2. Subir a Lambda
- Ir a AWS Lambda Console
- Crear nueva función o actualizar existente
- Subir archivo: `target/settlements-1.0.0-bin.zip`
- Configurar handler y variables de entorno

### 3. Configurar API Gateway
- Crear API Gateway
- Configurar endpoints
- Configurar CORS
- Deploy API

### 4. Actualizar Frontend
- Actualizar `servicios/config_settlements.php` con URL de API Gateway
- Configurar `SETTLEMENTS_LAMBDA_URL`

### 5. Ejecutar Script SQL
```sql
-- Ejecutar settlements_properties.sql
INSERT IGNORE INTO properties (name, value) 
VALUES ('settlement.uri', '/core/settlements/v1');
```

## 🔍 Testing

### Health Check
```bash
curl -X GET https://your-api-gateway-url.amazonaws.com/prod/settlements/health
```

### Reporte de Settlements
```bash
curl -X GET "https://your-api-gateway-url.amazonaws.com/prod/settlements/report?start_date=2024-01-01&end_date=2024-01-31"
```

### Verificar Logs
- Revisar CloudWatch Logs
- Verificar conexión a RDS
- Verificar autenticación con Pomelo

## 🚨 Monitoreo y Alertas

### CloudWatch Metrics
- Duration
- Errors
- Throttles
- Concurrent executions

### CloudWatch Alarms
- Error rate > 5%
- Duration > 45 seconds
- Throttles > 0

### Log Insights Queries
```
fields @timestamp, @message
| filter @message like /ERROR/
| sort @timestamp desc
| limit 20
```

## 🔧 Troubleshooting

### Problemas Comunes

1. **Error de conexión a RDS**
   - Verificar Security Groups
   - Verificar VPC configuration
   - Verificar credenciales

2. **Error de autenticación con Pomelo**
   - Verificar propiedades en BD
   - Verificar conectividad a internet
   - Revisar logs de autenticación

3. **Timeout**
   - Aumentar timeout de Lambda
   - Verificar conectividad
   - Optimizar consultas

4. **CORS errors**
   - Verificar configuración de API Gateway
   - Verificar dominios permitidos
   - Verificar headers

## 📞 Contacto

Para problemas de deployment:
- Revisar CloudWatch Logs
- Verificar configuración de AWS
- Contactar al equipo de DevOps 