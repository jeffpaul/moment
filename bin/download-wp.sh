#!/usr/bin/env bash
# Download a WordPress core build into a target directory.
#
# Usage: bash bin/download-wp.sh <version> <target-dir>
#
# version: "latest", "nightly", a release like "7.0", or a pre-release like
# "7.1-RC1" / "7.1-beta2". wp-cli handles latest/nightly/releases; pre-release
# builds are fetched straight from wordpress.org (wp core download does not
# resolve them).

set -euo pipefail

VERSION=${1:?version required}
TARGET=${2:?target dir required}

case "$VERSION" in
	*-*)
		ZIP="/tmp/wordpress-$VERSION.zip"
		curl -sfL "https://wordpress.org/wordpress-$VERSION.zip" -o "$ZIP"
		EXTRACT=$(mktemp -d)
		unzip -q "$ZIP" -d "$EXTRACT"
		mkdir -p "$TARGET"
		mv "$EXTRACT/wordpress/"* "$TARGET/"
		rm -rf "$EXTRACT" "$ZIP"
		echo "WordPress $VERSION installed at $TARGET (direct download)"
		;;
	*)
		wp core download --version="$VERSION" --path="$TARGET"
		;;
esac
