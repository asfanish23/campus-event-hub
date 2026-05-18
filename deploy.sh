#!/bin/bash

# Campus Event Hub Deployment Script
# This script automates the deployment process on the server
# Usage: bash deploy.sh

set -e

WEB_USER="${WEB_USER:-www-data}"

run_as_web_user() {
	if [ "$(id -un)" = "$WEB_USER" ]; then
		"$@"
	else
		sudo -u "$WEB_USER" "$@"
	fi
}

run_as_root_or_sudo() {
	if [ "$(id -u)" -eq 0 ]; then
		"$@"
	else
		sudo "$@"
	fi
}

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
run_as_web_user php artisan cache:clear
echo "Application cache cleared"

# Clear configuration cache
echo ""
echo "Clearing configuration cache..."
run_as_web_user php artisan config:clear
echo "Configuration cache cleared"

# Clear view cache
echo ""
echo "Clearing view cache..."
run_as_web_user php artisan view:clear
echo "View cache cleared"

# Fix file permissions
echo ""
echo "Fixing file permissions..."
run_as_root_or_sudo php fix-permissions.php
echo "File permissions fixed"

# Run any pending migrations
echo ""
echo "Running database migrations..."
run_as_web_user php artisan migrate --force
echo "Database migrations completed"

# Repair permissions again in case migrations or cache commands wrote new files
echo ""
echo "Re-checking file permissions after deployment tasks..."
run_as_root_or_sudo php fix-permissions.php
echo "Post-deploy permission pass complete"

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
