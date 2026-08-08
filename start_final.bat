@echo off
echo DÉMARRAGE CORRECT DES SERVEURS
echo ==============================

echo 1. Démarrage du Backend PHP sur port 8080...
cd /d "C:\laragon\www\daba\backend"
start "Backend PHP" cmd /k "php -S 0.0.0.0:8080"

echo 2. Attente démarrage backend...
timeout /t 3 /nobreak >nul

echo 3. Test de l'API...
powershell -Command "try { Invoke-WebRequest -Uri 'http://127.0.0.1:8080/products/get_all.php?per_page=3' -UseBasicParsing | Select-Object StatusCode } catch { $_.Exception.Message }"

echo 4. Démarrage du Frontend...
cd /d "C:\laragon\www\daba\frontend"
start "Frontend Vite" cmd /k "npm run dev"

echo.
echo ✅ SERVEURS DÉMARRÉS !
echo.
echo 🔗 Backend: http://localhost:8080
echo 🌐 Frontend: sera disponible sur un port 5xxx
echo 🎯 Dashboard: http://localhost:5xxx/admin
echo.
echo 👤 Connexion admin: admin@daba.local / Admin123!
echo.
echo 🛍️  Produits:  produits avec vraies images
echo 🏪 Store:  produits premium
echo 📂 Catégories:  catégories
echo.
echo 📊 Total:  produits avec images !
echo.
pause
