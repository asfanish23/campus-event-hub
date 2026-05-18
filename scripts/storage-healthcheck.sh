#!/usr/bin/env bash

set -euo pipefail

PROJECT_ROOT="${1:-/var/www/campus-event-hub}"

required_paths=(
  "$PROJECT_ROOT/storage"
  "$PROJECT_ROOT/storage/app"
  "$PROJECT_ROOT/storage/app/public"
  "$PROJECT_ROOT/storage/framework"
  "$PROJECT_ROOT/storage/framework/cache"
  "$PROJECT_ROOT/storage/framework/cache/data"
  "$PROJECT_ROOT/storage/framework/sessions"
  "$PROJECT_ROOT/storage/framework/views"
  "$PROJECT_ROOT/storage/logs"
  "$PROJECT_ROOT/bootstrap/cache"
)

missing=0

echo "Checking Laravel runtime directories under: $PROJECT_ROOT"

for path in "${required_paths[@]}"; do
  if [[ ! -d "$path" ]]; then
    echo "MISSING: $path"
    missing=1
    continue
  fi

  if [[ ! -w "$path" ]]; then
    echo "NOT WRITABLE: $path"
    missing=1
    continue
  fi

  echo "OK: $path"
done

exit "$missing"