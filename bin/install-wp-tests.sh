#!/usr/bin/env bash
# Install the WordPress PHPUnit test library + a core build for integration tests.
#
# Usage: bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]
#
# Unlike the classic wp-scaffold script this uses a git sparse checkout of
# WordPress/wordpress-develop instead of svn, so it runs anywhere git exists.
# wp-version accepts "nightly"/"trunk" (default — required while the plugin
# targets WP 7.0 pre-release) or a tag like "7.0".

set -euo pipefail

if [ $# -lt 3 ]; then
	echo "Usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]" >&2
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4:-localhost}
WP_VERSION=${5:-nightly}
SKIP_DB_CREATE=${6:-false}

TMPDIR_BASE=${TMPDIR:-/tmp}
TMPDIR_BASE=$(echo "$TMPDIR_BASE" | sed -e "s/\/$//")
WP_TESTS_DIR=${WP_TESTS_DIR:-$TMPDIR_BASE/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR:-$TMPDIR_BASE/wordpress}

download() {
	if command -v curl >/dev/null; then
		curl -sL "$1" -o "$2"
	else
		wget -nv -O "$2" "$1"
	fi
}

install_core() {
	if [ -f "$WP_CORE_DIR/wp-settings.php" ]; then
		echo "Core already present at $WP_CORE_DIR"
		return
	fi

	mkdir -p "$WP_CORE_DIR"

	if [ "$WP_VERSION" = "nightly" ] || [ "$WP_VERSION" = "trunk" ]; then
		local zip="$TMPDIR_BASE/wordpress-nightly.zip"
		download https://wordpress.org/nightly-builds/wordpress-latest.zip "$zip"
		unzip -q "$zip" -d "$TMPDIR_BASE/wordpress-nightly-extract"
		mv "$TMPDIR_BASE/wordpress-nightly-extract/wordpress/"* "$WP_CORE_DIR/"
		rm -rf "$TMPDIR_BASE/wordpress-nightly-extract" "$zip"
	else
		local tar="$TMPDIR_BASE/wordpress-$WP_VERSION.tar.gz"
		download "https://wordpress.org/wordpress-$WP_VERSION.tar.gz" "$tar"
		tar --strip-components=1 -zxmf "$tar" -C "$WP_CORE_DIR"
		rm -f "$tar"
	fi

	echo "Core installed at $WP_CORE_DIR"
}

install_test_suite() {
	if [ -f "$WP_TESTS_DIR/includes/functions.php" ]; then
		echo "Test library already present at $WP_TESTS_DIR"
	else
		local ref="trunk"
		if [ "$WP_VERSION" != "nightly" ] && [ "$WP_VERSION" != "trunk" ]; then
			ref="$WP_VERSION"
		fi

		local checkout="$TMPDIR_BASE/wordpress-develop-sparse"
		rm -rf "$checkout"
		git clone --quiet --depth=1 --filter=blob:none --sparse \
			--branch "$ref" \
			https://github.com/WordPress/wordpress-develop.git "$checkout" 2>/dev/null \
			|| git clone --quiet --depth=1 --filter=blob:none --sparse \
				https://github.com/WordPress/wordpress-develop.git "$checkout"
		git -C "$checkout" sparse-checkout set tests/phpunit/includes tests/phpunit/data

		mkdir -p "$WP_TESTS_DIR"
		cp -R "$checkout/tests/phpunit/includes" "$WP_TESTS_DIR/includes"
		cp -R "$checkout/tests/phpunit/data" "$WP_TESTS_DIR/data"
		cp "$checkout/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config-sample.php"
		rm -rf "$checkout"
		echo "Test library installed at $WP_TESTS_DIR"
	fi

	if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
		local config="$WP_TESTS_DIR/wp-tests-config.php"
		cp "$WP_TESTS_DIR/wp-tests-config-sample.php" "$config"
		# Portable in-place sed (BSD sed on macOS requires a backup suffix).
		sed -i.bak "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$config"
		sed -i.bak "s:__DIR__ . '/src/':'$WP_CORE_DIR/':" "$config"
		sed -i.bak "s/youremptytestdbnamehere/$DB_NAME/" "$config"
		sed -i.bak "s/yourusernamehere/$DB_USER/" "$config"
		sed -i.bak "s/yourpasswordhere/$DB_PASS/" "$config"
		sed -i.bak "s|localhost|$DB_HOST|" "$config"
		rm -f "$config.bak"
		echo "Config written at $config"
	fi
}

install_db() {
	if [ "$SKIP_DB_CREATE" = "true" ]; then
		return
	fi

	local host_arg="--host=$DB_HOST"
	local pass_arg=""
	if [ -n "$DB_PASS" ]; then
		pass_arg="--password=$DB_PASS"
	fi

	# shellcheck disable=SC2086
	mysqladmin create "$DB_NAME" --user="$DB_USER" $pass_arg $host_arg 2>/dev/null \
		|| echo "Database $DB_NAME already exists (or could not be created — tests will tell)."
}

install_core
install_test_suite
install_db

echo "Done. Run: WP_TESTS_DIR=$WP_TESTS_DIR composer test"
