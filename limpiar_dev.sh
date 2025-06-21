#!/bin/bash
# Script de limpieza para dejar solo archivos de producción

echo "Eliminando archivos y carpetas de desarrollo..."

# Archivos SQL y scripts de mantenimiento
declare -a files=(
  "recrear_base_datos.php"
  "recrear_base_datos.sql"
  "crear_base_datos.php"
  "crear_empresa.sql"
  "create_database.php"
  "create_database.sql"
  "create_admin_table.sql"
  "create_test_user.php"
  "create_tarjetas_table.php"
  "create_tarjetas_table.sql"
  "update_usuarios_table.sql"
  "recreate_admin.php"
  "check_admin.php"
  "update_admin_table.php"
  "update_empresas.sql"
  "update_db.php"
  "db_structure.sql"
  "direct_update.php"
  "actualizar_estructura.sql"
  "actualizar_estructura.php"
  "limpiar_tablas.php"
  "limpiar_tablas.sql"
  "verificar_empresas.php"
  "verificar_estructura.php"
  "test_endpoint.php"
  "create_db.php"
  "check_structure.php"
  "t_movimientos_saldo.sql"
  "ejecutar_sql.bat"
  "start-local-servers.bat"
)

for f in "${files[@]}"; do
  if [ -f "$f" ]; then
    echo "Eliminando $f"
    rm "$f"
  fi
done

# Carpetas de desarrollo
if [ -d "local-servers" ]; then
  echo "Eliminando carpeta local-servers/"
  rm -rf local-servers
fi
if [ -d "api" ]; then
  echo "Eliminando carpeta api/"
  rm -rf api
fi

echo "Limpieza completada." 