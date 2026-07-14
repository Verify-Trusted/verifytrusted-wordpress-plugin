#!/usr/bin/env bash
#
# deploy-wporg.sh - Publish a release to the WordPress.org plugin SVN repo.
#
# Usage:
#   bin/deploy-wporg.sh [version]
#
# If no version is given, it is read from the plugin header. The script:
#   1. Runs bin/build.sh to assemble a clean distributable.
#   2. Checks out the WordPress.org SVN repo (trunk + tags + assets).
#   3. Syncs the build into trunk/ and copies it to tags/<version>/.
#   4. Commits (SVN will prompt for your WordPress.org username/password).
#
# Prerequisites: svn, rsync, and a WordPress.org account with commit access
# to this plugin. The '.org assets (banners/screenshots/icon) live in the SVN
# 'assets/' directory - manage those separately.
#
set -euo pipefail

SLUG="verifytrusted"
SVN_URL="https://plugins.svn.wordpress.org/${SLUG}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SRC_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
MAIN_FILE="$SRC_DIR/$SLUG.php"
BUILD_DIR="$SRC_DIR/dist/$SLUG"
SVN_DIR="$SRC_DIR/dist/svn"

command -v svn   >/dev/null || { echo "ERROR: svn is not installed." >&2; exit 1; }
command -v rsync >/dev/null || { echo "ERROR: rsync is not installed." >&2; exit 1; }

VERSION="${1:-$(grep -m1 -oP 'Version:\s*\K[0-9][0-9A-Za-z.\-]*' "$MAIN_FILE")}"
if [[ -z "$VERSION" ]]; then
	echo "ERROR: no version given and none found in $MAIN_FILE" >&2
	exit 1
fi

# Sanity: the plugin header and readme Stable tag should agree with $VERSION.
README_STABLE="$(grep -m1 -oP 'Stable tag:\s*\K[0-9][0-9A-Za-z.\-]*' "$SRC_DIR/readme.txt" || true)"
if [[ -n "$README_STABLE" && "$README_STABLE" != "$VERSION" ]]; then
	echo "WARNING: readme.txt Stable tag ($README_STABLE) != release version ($VERSION)." >&2
	read -r -p "Continue anyway? [y/N] " reply
	[[ "$reply" =~ ^[Yy]$ ]] || exit 1
fi

echo "==> Building $SLUG $VERSION"
bash "$SCRIPT_DIR/build.sh" >/dev/null

echo "==> Checking out SVN: $SVN_URL"
rm -rf "$SVN_DIR"
svn checkout "$SVN_URL" "$SVN_DIR" --depth immediates
svn update --set-depth infinity "$SVN_DIR/trunk"

TAG_DIR="$SVN_DIR/tags/$VERSION"
if svn info "$SVN_URL/tags/$VERSION" >/dev/null 2>&1; then
	echo "ERROR: tag $VERSION already exists in SVN. Bump the version first." >&2
	exit 1
fi

echo "==> Syncing build into trunk/"
rsync -a --delete --exclude='.svn' "$BUILD_DIR/" "$SVN_DIR/trunk/"

echo "==> Staging additions/deletions"
# Remove files deleted from the build, then add new ones.
svn status "$SVN_DIR/trunk" | awk '/^!/ {print $2}' | xargs -r -I{} svn rm "{}"
svn status "$SVN_DIR/trunk" | awk '/^\?/ {print $2}' | xargs -r -I{} svn add "{}"

echo "==> Copying trunk to tags/$VERSION"
svn copy "$SVN_DIR/trunk" "$TAG_DIR"

echo
echo "==> Review the pending changes:"
svn status "$SVN_DIR"
echo
read -r -p "Commit release $VERSION to WordPress.org? [y/N] " reply
if [[ "$reply" =~ ^[Yy]$ ]]; then
	svn commit "$SVN_DIR" -m "Release ${VERSION}"
	echo "Committed release $VERSION."
else
	echo "Aborted. Nothing was committed. Working copy left at: $SVN_DIR"
fi
