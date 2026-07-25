



echo "🚀 Starting Deployment for Mabrurhut..."

# 1. Pull the latest changes from the main branch
echo "📥 Pulling latest code from Git..."
git pull origin main



# 2. Install/Update PHP dependencies without dev packages
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction


# 3. Build frontend assets with Vite
echo "🎨 Compiling assets with Vite..."
npm install
npm run build


php artisan optimize:clear

php artisan optimize

echo "🔄 Reloading PHP-FPM and Nginx..."
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx

echo "✅ Deployment completed successfully!"
