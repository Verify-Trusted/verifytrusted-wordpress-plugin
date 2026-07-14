#!/usr/bin/env bash
#
# build.sh - Assemble a clean WordPress.org distributable for the plugin.
#
# Produces:
#   dist/verifytrusted/        the shippable plugin directory
#   dist/verifytrusted.zip     a zip of the same
#
# Everything listed in .distignore is excluded. Run from anywhere.
#
set -euo pipefail

SLUG="verifytrusted"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SRC_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
DIST_DIR="$SRC_DIR/dist"
BUILD_DIR="$DIST_DIR/$SLUG"
MAIN_FILE="$SRC_DIR/$SLUG.php"

if [[ ! -f "$MAIN_FILE" ]]; then
	echo "ERROR: main plugin file not found: $MAIN_FILE" >&2
	exit 1
fi

VERSION="$(grep -m1 -oP 'Version:\s*\K[0-9][0-9A-Za-z.\-]*' "$MAIN_FILE" || true)"
if [[ -z "$VERSION" ]]; then
	echo "ERROR: could not read Version from $MAIN_FILE" >&2
	exit 1
fi

echo "Building $SLUG $VERSION ..."

rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

# Sync source into the build dir, honouring .distignore.
rsync -a --delete \
	--exclude-from="$SRC_DIR/.distignore" \
	--exclude="$SLUG/" \
	"$SRC_DIR/" "$BUILD_DIR/"

# Zip it (top-level folder is the plugin slug, as WordPress expects).
( cd "$DIST_DIR" && rm -f "$SLUG.zip" && zip -rq "$SLUG.zip" "$SLUG" )

echo "Done."
echo "  Directory: $BUILD_DIR"
echo "  Zip:       $DIST_DIR/$SLUG.zip"
echo
echo "Contents:"
( cd "$BUILD_DIR" && find . -type f | sed 's|^\./|  |' | sort )
