#!/bin/sh
# Simple PHP syntax check for all PHP files in the project

if ! command -v php >/dev/null 2>&1; then
  echo "PHP executable not found. Install PHP to run syntax checks." >&2
  exit 1
fi

set -e
find "$(dirname "$0")/.." -name '*.php' -print0 | xargs -0 -n 1 php -l

