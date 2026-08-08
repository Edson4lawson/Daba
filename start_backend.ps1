# Script de démarrage du serveur PHP avec routeur
# Usage: .\start_backend.ps1

$backendDir = "c:\laragon\www\daba\backend"
$port = 8080

Write-Host "Démarrage du serveur PHP backend sur le port $port..." -ForegroundColor Green
Write-Host "Routeur: index.php" -ForegroundColor Yellow
Write-Host "URL: http://localhost:$port" -ForegroundColor Cyan
Write-Host ""
Write-Host "Appuyez sur Ctrl+C pour arrêter" -ForegroundColor Gray
Write-Host ""

php -S localhost:$port -t $backendDir $backendDir\index.php
