#!/bin/bash

# Campus Event Hub Deployment Script
# This script automates the deployment process on the server
# Usage: bash deploy.sh

set -e

echo "Starting Campus Event Hub Deployment..."
echo "=========================================="

# Navigate to project directory
cd /var/www/campus-event-hub
echo "Changed to project directory"

# Pull latest changes from GitHub
echo ""
echo "Pulling latest changes from GitHub..."
git pull origin main
echo "Latest changes pulled"

# Clear application caches
echo ""
echo "Clearing application caches..."
php artisan cache:clear
echo "Application cache cleared"

# Clear configuration cache
echo ""
echo "Clearing configuration cache..."
php artisan config:clear
echo "Configuration cache cleared"

# Clear view cache
echo ""
echo "Clearing view cache..."
php artisan view:clear
echo "View cache cleared"

# Fix file permissions
echo ""
echo "Fixing file permissions..."
php fix-permissions.php
echo "File permissions fixed"

# Run any pending migrations
echo ""
echo "Running database migrations..."
php artisan migrate --force
echo "Database migrations completed"

# Restart nginx
echo ""
echo "Restarting Nginx..."
sudo systemctl restart nginx
echo "Nginx restarted"

# Success message
echo ""
echo "=========================================="
echo "Deployment complete!"
echo "=========================================="
echo ""
echo "Campus Event Hub has been successfully deployed!"
echo ""
echo "Next steps:"
echo "1. Test the API: https://aseems.ddns.net/api/health"
echo "2. Try registering in the mobile app"
echo ""
echo "Deployed at: $(date)"
