# Reporte de Movimientos vs Liquidaciones (Settlements)

Este módulo permite generar reportes comparativos entre movimientos (transacciones) y liquidaciones (settlements) utilizando la API de Pomelo.

## Características

- **Reporte visual**: Interfaz web con gráficos y tablas comparativas
- **Filtros de fecha**: Permite seleccionar períodos específicos
- **Exportación múltiple**: CSV, Excel y PDF
- **Integración con Pomelo**: Utiliza la API oficial de Pomelo
- **Seguridad**: Validación de sesión y autenticación de dos factores

## Archivos del módulo

- `reporte_settlements.php` - Reporte principal con interfaz web
- `exportar_settlements.php` - Exportación en diferentes formatos
- `config_pomelo.php` - Configuración de credenciales de Pomelo
- `README_SETTLEMENTS.md` - Este archivo de documentación

## Configuración

### 1. Credenciales de Pomelo

Edita el archivo `config_pomelo.php` y reemplaza las credenciales:

```php
// Credenciales de la API de Pomelo
define('POMELO_CLIENT_ID', 'TU_CLIENT_ID_REAL');
define('POMELO_CLIENT_SECRET', 'TU_CLIENT_SECRET_REAL');
```

**Para obtener las credenciales:**

1. Accede al [Dashboard de Pomelo](https://dashboard.pomelo.la/)
2. Ve a la sección de API Keys
3. Crea una nueva API Key o usa una existente
4. Copia el Client ID y Client Secret

### 2. Verificación de configuración

El sistema validará automáticamente que las credenciales estén configuradas correctamente. Si no lo están, mostrará un mensaje de error indicando que debes configurar las credenciales.

## Uso

### Acceso al reporte

1. Inicia sesión en el sistema
2. Completa la autenticación de dos factores
3. En el menú lateral, haz clic en "Reporte Settlements"
4. El reporte se cargará automáticamente con los datos del mes actual

### Filtros disponibles

- **Fecha de inicio**: Selecciona desde qué fecha quieres ver los datos
- **Fecha de fin**: Selecciona hasta qué fecha quieres ver los datos
- **Empresa**: Automáticamente filtra por la empresa del usuario logueado

### Exportación

El reporte incluye botones para exportar en diferentes formatos:

- **CSV**: Formato de texto separado por comas
- **Excel**: Formato de hoja de cálculo (.xls)
- **PDF**: Formato de documento imprimible

## Estructura de datos

### Settlements (Liquidaciones)
- ID único del settlement
- ID de la cuenta
- Monto
- Moneda
- Estado (completed, pending, failed)
- Fecha de creación

### Transactions (Transacciones)
- ID único de la transacción
- ID de la cuenta
- Monto
- Moneda
- Estado
- Descripción
- Fecha de creación

## API Endpoints utilizados

- `POST /oauth/token` - Autenticación OAuth2
- `GET /core/settlements/v1` - Obtener settlements
- `GET /core/transactions/v1` - Obtener transacciones
- `GET /core/accounts/v1/{accountId}` - Información de cuenta

## Filtros de API

Los siguientes filtros están disponibles para las consultas:

### Settlements
- `filter[account_id]` - Filtrar por ID de cuenta
- `filter[created_at][gte]` - Fecha de inicio (ISO 8601)
- `filter[created_at][lte]` - Fecha de fin (ISO 8601)

### Transactions
- `filter[account_id]` - Filtrar por ID de cuenta
- `filter[created_at][gte]` - Fecha de inicio (ISO 8601)
- `filter[created_at][lte]` - Fecha de fin (ISO 8601)

## Manejo de errores

El sistema incluye manejo de errores para:

- **Credenciales no configuradas**: Muestra mensaje instructivo
- **Error de autenticación**: Indica problemas con las credenciales
- **Error de conexión**: Problemas de red o API no disponible
- **Sin datos**: Mensaje cuando no hay movimientos en el período

## Logs

Los errores se registran en el log de PHP con el prefijo "Pomelo API Error". Puedes revisar estos logs para diagnosticar problemas:

```bash
tail -f /var/log/php_errors.log
```

## Seguridad

- **Validación de sesión**: Solo usuarios autenticados pueden acceder
- **Autenticación de dos factores**: Requerida para acceder al reporte
- **Filtrado por empresa**: Los usuarios solo ven datos de su empresa
- **Sanitización de datos**: Todos los datos se escapan para prevenir XSS

## Personalización

### Modificar estilos

Los estilos están definidos en el archivo `reporte_settlements.php`. Puedes modificar:

- Colores de las tarjetas de resumen
- Estilos de la tabla
- Colores de los badges de estado

### Agregar nuevos filtros

Para agregar nuevos filtros, modifica:

1. El formulario HTML en `reporte_settlements.php`
2. La lógica de procesamiento de parámetros
3. Las funciones de consulta en `config_pomelo.php`

### Extender funcionalidad

El módulo está diseñado para ser extensible. Puedes:

- Agregar nuevos tipos de exportación
- Implementar más gráficos
- Agregar filtros adicionales
- Integrar con otros sistemas

## Troubleshooting

### Error: "Las credenciales de Pomelo no están configuradas"

1. Verifica que hayas editado `config_pomelo.php`
2. Asegúrate de que las credenciales sean correctas
3. Verifica que no haya espacios extra en las credenciales

### Error: "Error al obtener token de acceso"

1. Verifica que las credenciales sean válidas
2. Confirma que tienes acceso a la API de Pomelo
3. Revisa la conectividad a internet
4. Verifica los logs de error

### No se muestran datos

1. Verifica que las cuentas tengan `id_account` configurado
2. Confirma que haya movimientos en el período seleccionado
3. Verifica que las cuentas estén activas en Pomelo

### Error de exportación

1. Verifica permisos de escritura en el servidor
2. Confirma que el formato solicitado esté soportado
3. Revisa que haya datos para exportar

## Soporte

Para soporte técnico o preguntas sobre este módulo, contacta al equipo de desarrollo o consulta la documentación de la API de Pomelo en https://docs.pomelo.la/ 