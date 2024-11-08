<?php

/**
 * Create a distribution archive based on a project's .distignore file.
 */
class Increment_Version {

	/**
	 * Increment a plugin's version
	 *
	 * Parses the version number from the main plugin file, increments the major/minor/patch as specified, replaces
	 * instances of the old version number with the new version number in specified files.
	 *
	 * ## OPTIONS
	 *
	 * [<paths>]
	 * : List of files where the version should be updated. The first file must contain a valid plugin header.
	 *
	 * [--version=<version>]
	 * : The semver point to update
	 * ---
	 * default: patch
	 * options:
	 *   - major
	 *   - minor
	 *   - patch
	 * ---
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {

		$absolute_paths = array();

		foreach ( $args as $path ) {
			if ( 0 === strpos( $path, '/' ) ) {
				$absolute_path = $path;
			} else {
				$absolute_path = getcwd() . '/' . $path;
			}
			if ( ! file_exists( $absolute_path ) ) {
				WP_CLI::error( 'File not found: ' . $absolute_path );
			}
			$absolute_paths[] = $absolute_path;
		}

		$version_point_to_increment = $assoc_args['version'];

		$a = preg_match_all( '/(\s*\*\s*Version:\s*)(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)/', file_get_contents( $absolute_paths[0] ) );

		if ( count( $absolute_paths ) === 0
		|| 0 === preg_match_all( '/(\s*\*\s*Version:\s*)(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)/', file_get_contents( $absolute_paths[0] ) )
		) {
			$plugin_file_path = getcwd() . '/' . basename( getcwd() ) . '.php';
			$absolute_paths[] = $plugin_file_path;
		} else {
			$plugin_file_path = $absolute_path[0];
		}

		$file_contents = file_get_contents( $plugin_file_path );

		if ( false === preg_match_all( '/(\s*\*\s*Version:\s*)(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)/', $file_contents, $matches ) ) {
			WP_CLI::error( 'Failed to get plugin version from file: ' . $plugin_file_path );
		}

		$version_major = $matches['major'][0];
		$version_minor = $matches['minor'][0];
		$version_patch = $matches['patch'][0];

		$old_version = "$version_major.$version_minor.$version_patch";

		switch ( $version_point_to_increment ) {
			case 'major':
				++$version_major;
				$version_minor = 0;
				$version_patch = 0;
				break;
			case 'minor':
				++$version_minor;
				$version_patch = 0;
				break;
			default:
				++$version_patch;
		}

		$new_version = "$version_major.$version_minor.$version_patch";

		WP_CLI::line( "Incrementing version from $old_version to $new_version." );

		$file_content_updated_header = str_replace( $matches[0][0], $matches[1][0] . $new_version, $file_contents );

		file_put_contents( $plugin_file_path, $file_content_updated_header );

		WP_CLI::line( "Version incremented in plugin header: $plugin_file_path" );

		foreach ( $absolute_paths as $file_path ) {

			$file_contents         = file_get_contents( $file_path );
			$file_contents_updated = preg_replace( '/(["\'])' . preg_quote( $old_version, '/' ) . '(["\'])/', "'$new_version'", $file_contents );
			if ( $file_contents_updated !== $file_contents ) {
				file_put_contents( $file_path, $file_contents_updated );
				WP_CLI::line( "Version incremented in $file_path" );
			} elseif ( $plugin_file_path !== $file_path ) {
				WP_CLI::warning( "Failed to find old version $old_version in $file_path" );
			}
		}
	}
}
