$nodePath = "C:\Program Files\nodejs\node.exe"
$npmPath = "C:\Program Files\nodejs\npm.cmd"

Write-Host "Iniciando servidores..."

# Iniciar API Server
Start-Process -FilePath $nodePath -ArgumentList "api-server/index.js" -NoNewWindow
Write-Host "API Server iniciado en puerto 3000"

# Iniciar Email Server
Start-Process -FilePath $nodePath -ArgumentList "email-server/index.js" -NoNewWindow
Write-Host "Email Server iniciado en puerto 3001"

# Iniciar Pomelo Mock Server
Start-Process -FilePath $nodePath -ArgumentList "pomelo-mock/index.js" -NoNewWindow
Write-Host "Pomelo Mock Server iniciado en puerto 3002"

Write-Host "Todos los servidores están corriendo. Presiona Ctrl+C para detener."
Write-Host "Puedes probar los endpoints en:"
Write-Host "- http://localhost:3000/api/user/v1"
Write-Host "- http://localhost:3001/email"
Write-Host "- http://localhost:3002/pomelo/token"

# Mantener el script corriendo
while ($true) { Start-Sleep -Seconds 1 } 