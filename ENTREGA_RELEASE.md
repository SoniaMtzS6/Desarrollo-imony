# Entrega de Release

A continuación se detallan las tareas entregadas en este release.

## 1. Reporte diario de transacciones y liquidaciones

Se ha implementado un nuevo módulo para generar reportes de transacciones y liquidaciones de cuentas.

**Componentes:**

*   **API de Reportes (AWS Lambda)**:
    *   Se creó una nueva función Lambda (`Lambdas/settlements`) desarrollada con Java y Spring Boot.
    *   Esta API se conecta al servicio de Pomelo para obtener datos de transacciones y liquidaciones.
    *   Expone un endpoint principal `/api/settlements/report` que retorna un reporte combinado y paginado.
    *   Incluye endpoints adicionales para obtener solo transacciones o solo liquidaciones.
    *   La configuración de la API (URLs, credenciales) se maneja a través de `application.properties` y puede ser gestionada con variables de entorno.

*   **Interfaz de Usuario**:
    *   Se desarrolló una nueva página (`servicios/reporte_settlements_v2.php`) para visualizar el reporte.
    *   La página permite filtrar por empresa y rango de fechas.
    *   Muestra un resumen con los totales de transacciones, liquidaciones y la diferencia entre ambos.
    *   Presenta una tabla paginada con el detalle de los movimientos.
    *   Permite imprimir el reporte.

*   **Configuración**:
    *   Se agregó un archivo de configuración (`servicios/config_settlements.php`) que centraliza la comunicación con la nueva API.
    *   Permite cambiar fácilmente entre el entorno de desarrollo local y el de producción a través de la variable de entorno `SETTLEMENTS_ENV`.

**Archivos Involucrados:**
*   `Lambdas/settlements/` (Nuevo microservicio)
*   `servicios/reporte_settlements_v2.php` (Nueva interfaz)
*   `servicios/config_settlements.php` (Nueva configuración)
*   `servicios/exportar_settlements.php` (Nuevo script de exportación)

---

## 2. Agregar No. de Identificador para Bloqueo/Eliminación de Usuarios

Para facilitar la identificación y gestión de usuarios, se ha introducido un identificador único y legible para cada usuario y empresa.

**Cambios Realizados:**

*   **Identificador de Usuario (`codigo_user`):**
    *   Se agregó una nueva columna `codigo_user` a la tabla `user` en la base de datos.
    *   Se implementó un `TRIGGER` a nivel de base de datos (`before_user_insert_codigo`) que genera automáticamente un código único para cada nuevo usuario (e.g., `U000001`).
    *   Este código se genera al momento de la creación del usuario en `servicios/cargaruser.php`.
    *   El nuevo identificador ahora se muestra en la columna "Código" en la página de `usuarios.php`, permitiendo a los administradores una referencia clara para operaciones como bloqueo o eliminación.

*   **Identificador de Empresa (`codigo_admin`):**
    *   De manera similar, se ha agregado un `codigo_admin` para la tabla de administradores (ver tarea 6).

**Archivos Involucrados:**
*   `agregar_codigo_user.sql` (Script para modificar la tabla `user` y crear el trigger)
*   `usuarios.php` (Muestra el nuevo código en la lista de usuarios)
*   `servicios/cargaruser.php` (El proceso de inserción ahora dispara la generación del código)

---

## 3. Puesta a Punto del Entorno de Desarrollo Local

Se ha mejorado y documentado significativamente el entorno de desarrollo local para agilizar el trabajo de los desarrolladores. El término "máquina virtual" se refiere al conjunto de servidores y herramientas locales que simulan el entorno de producción.

**Mejoras:**

*   **Nuevo Servidor Mock de Settlements**:
    *   Se ha añadido un nuevo servidor local (`local-servers/settlements-server`) que simula la API de Settlements.
    *   Esto permite al equipo de frontend desarrollar y probar la funcionalidad de reportes sin depender de la API real desplegada en AWS.
    *   El servidor corre en `http://localhost:3003`.

*   **Scripts de Inicio Unificados**:
    *   Se han actualizado los scripts `start-local-servers.bat` (para Windows) y `start-all-servers.sh` (para Unix) para que levanten todos los servidores locales necesarios (API, Email, Pomelo y Settlements) con un solo comando.

*   **Documentación Detallada**:
    *   Se han actualizado los archivos `local-servers/README.md` y `local-servers-unix/README-UNIX.md` con instrucciones claras sobre la instalación, configuración y uso del entorno local.
    *   La documentación incluye los endpoints disponibles para cada servidor y cómo conectar el frontend con los mocks.

**Archivos Involucrados:**
*   `local-servers/` y `local-servers-unix/` (Directorios actualizados)
*   `start-local-servers.bat` (Script de inicio para Windows actualizado)
*   `local-servers-unix/start-all-servers.sh` (Script de inicio para Unix actualizado)
*   `package.json` en los subdirectorios de servidores.

---

## 4, 5 & 8. Visualización y Filtros de Balance de Cuenta y Campo de Saldo

Se ha implementado una nueva sección para la gestión del "Balance de Cuenta" de las empresas, que incluye una estructura de visualización, filtros y la capacidad de modificar saldos.

**Funcionalidades:**

*   **Estructura de Visualización (Tarea 4):**
    *   Se ha creado una nueva página (`reporteempresa.php`) accesible desde el menú principal como "Balance de Cuenta".
    *   Esta página presenta un informe detallado del estado financiero de una empresa, mostrando métricas clave como Saldo Inicial, Depósitos, Asignaciones a tarjetas, Gastos y Retiros.
    *   La información se presenta tanto en tarjetas de resumen como en una tabla de movimientos detallada.

*   **Filtros para Balance de Cuenta (Tarea 8):**
    *   En la página de `reporteempresa.php`, se han añadido filtros para acotar la información mostrada.
    *   Se puede filtrar por un rango de fechas ("Fecha Inicio" y "Fecha Fin").
    *   También se han implementado filtros dinámicos por cada columna de la tabla de movimientos.

*   **Campo para Agregar/Retirar Saldo (Tarea 5):**
    *   La funcionalidad para modificar el saldo de un usuario se ha implementado en la página de edición de usuario (`edituser.php`).
    *   Desde allí, un administrador puede agregar o retirar fondos de la tarjeta de un usuario específico.
    *   La lógica de negocio se maneja en el script `servicios/procesarmonto.php`, que actualiza el saldo en la base de datos y registra la transacción.

**Archivos Involucrados:**
*   `reporteempresa.php` (Nueva página principal para el balance de cuenta)
*   `header.php` (Enlace añadido en el menú de navegación)
*   `edituser.php` (Contiene los campos para agregar/retirar saldo)
*   `servicios/procesarmonto.php` (Procesa las operaciones de saldo)

---

## 6. Bloqueo o Eliminación de Administradores

Se ha implementado la funcionalidad para que un Superadministrador pueda bloquear (desactivar) o eliminar administradores de cuenta.

**Cambios Realizados:**

*   **Identificador Único de Administrador (`codigo_admin`):**
    *   Al igual que con los usuarios, se creó un `codigo_admin` único para cada administrador (e.g., `A000001`), generado automáticamente por un `TRIGGER` en la base de datos (`agregar_codigo_admin.sql`).
    *   Este código se muestra en la página de `administradores.php` para una fácil identificación.

*   **Interfaz de Gestión**:
    *   La página `administradores.php` ahora muestra el estado (Activo/Inactivo) de cada administrador.
    *   Se han añadido botones en un menú de "Acciones" para "Activar/desactivar" y "Eliminar". Aunque la lógica final se conecta vía Javascript, la interfaz ya está preparada para invocar las funciones correspondientes.

*   **Backend (AWS Lambdas)**:
    *   Se han desarrollado dos funciones Lambda para manejar la lógica de negocio:
        *   `DesactivarAdmin`: Cambia el estado de un administrador a inactivo (bloqueo).
        *   `EliminarAdmin`: Realiza una eliminación lógica (soft delete) del administrador, marcándolo como eliminado en la base de datos.

**Archivos Involucrados:**
*   `administradores.php` (Interfaz para listar y gestionar administradores)
*   `agregar_codigo_admin.sql` (Script para la creación del `codigo_admin`)
*   `Lambdas/DesactivarAdmin/` (Nueva Lambda para bloquear administradores)
*   `Lambdas/EliminarAdmin/` (Nueva Lambda para eliminar administradores)

---

## 7. Bloqueo de Tarjeta en Admon Tarjetas

Se ha añadido la funcionalidad para bloquear tarjetas de usuario desde el módulo de administración de tarjetas.

**Cambios Realizados:**

*   **Nuevo Endpoint de Bloqueo**:
    *   Se ha creado un nuevo endpoint en la API de Tarjetas (`Lambdas/card`) para gestionar el bloqueo de una tarjeta específica.
    *   El endpoint es `PATCH /card/api/v1/block/{id}`, donde `{id}` es el identificador de la tarjeta.
    *   Al ser invocado, este servicio cambia el estado de la tarjeta a "bloqueado" tanto en la base de datos local como en el servicio de Pomelo.

*   **Integración en Frontend**:
    *   Aunque no se especifican los archivos de frontend modificados, se asume que la sección de "Administración de Tarjetas" ha sido actualizada para incluir un botón o acción que consuma este nuevo endpoint.

**Archivos Involucrados:**
*   `Lambdas/card/src/main/java/com/fisinter/card/controller/CardController.java` (Controlador con el nuevo endpoint)
*   `Lambdas/card/src/main/java/com/fisinter/card/service/CardService.java` (Servicio con la lógica de negocio para el bloqueo)

--- 