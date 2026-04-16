#!/bin/bash

# Test registration endpoint with proper JSON
echo "Testing registration endpoint..."

# Create a JSON file with valid data
cat > /tmp/register_test.json << 'EOF'
{
  "name": "Test User",
  "email": "testuser12345@example.com",
  "password": "password123"
}
EOF

echo "Sending POST request..."
curl -v -X POST https://aseems.ddns.net/api/register \
  -H 'Content-Type: application/json' \
  -d @/tmp/register_test.json

echo ""
echo "---"
echo "Testing health endpoint..."
curl -s https://aseems.ddns.net/api/health
