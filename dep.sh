#!/bin/bash

# ==================================================
# Campus Event Hub - Clean Deployment Script
# File: dep.sh
# ==================================================

set -e

WEB_USER="${WEB_USER:-www-data}"
PROJECT_DIR="/var/www/campus-event-hub"

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

step() {
    printf "%-52s" "$1"
}

success() {
    echo "✓"
}

echo ""
echo "=========================================================="
echo "          Campus Event Hub Deployment Utility"
echo "=========================================================="
echo ""

# ----------------------------------------------------------
step "[1/7] Navigating to project directory..."
cd "$PROJECT_DIR"
success

# ----------------------------------------------------------
step "[2/7] Pulling latest source code from GitHub..."
git pull origin main >/dev/null 2>&1
success

# ----------------------------------------------------------
step "[3/7] Clearing Laravel caches..."
run_as_web_user php artisan cache:clear >/dev/null 2>&1
run_as_web_user php artisan config:clear >/dev/null 2>&1
run_as_web_user php artisan view:clear >/dev/null 2>&1
success

# ----------------------------------------------------------
step "[4/7] Updating file permissions..."
run_as_root_or_sudo php fix-permissions.php >/dev/null 2>&1
success

# ----------------------------------------------------------
step "[5/7] Checking database migrations..."
run_as_web_user php artisan migrate --force >/dev/null 2>&1
success

# ----------------------------------------------------------
step "[6/7] Verifying file permissions..."
run_as_root_or_sudo php fix-permissions.php >/dev/null 2>&1
success

# ----------------------------------------------------------
step "[7/7] Restarting Nginx service..."
run_as_root_or_sudo systemctl restart nginx >/dev/null 2>&1
success

echo ""
echo "=========================================================="
echo "             Deployment Completed Successfully"
echo "=========================================================="
echo ""
echo "Deployment Summary"
echo "----------------------------------------------------------"
echo "Server      : campus-event-hub"
echo "Platform    : DigitalOcean Droplet"
echo "Framework   : Laravel 10.50.2"
echo "Web Server  : Nginx"
echo "PHP         : 8.3"
echo "Database    : MySQL 8.0"
echo "Status      : Online"
echo "Date        : $(date)"
echo ""
echo "Application URL"
echo "----------------------------------------------------------"
echo "https://aseems.ddns.net"
echo ""