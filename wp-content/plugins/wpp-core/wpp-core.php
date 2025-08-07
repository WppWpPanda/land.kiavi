<?php
/**
 * Plugin Name: WPP Core
 * Description: Wp Panda Core
 * Version: 1.0.0
 * Author: WebAndAd Team
 * Developer: WP_Panda
 */

/**
 * Recursively includes all PHP files matching the pattern "wpp-al-*.php"
 * from the given directory and its subdirectories.
 *
 * @param string $dir The directory path to search in.
 *
 * @return void
 *
 * @example
 * recursive_require_wpp_al(__DIR__ . '/plugins');
 */
function recursive_require_wpp_al($dir) {
	// Check if the directory exists
	if (!is_dir($dir)) {
		return;
	}

	// Create a recursive iterator for directory traversal
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
	);

	// Loop through each file
	foreach ($iterator as $file) {
		if ($file->isFile() && $file->getExtension() === 'php') {
			$filename = $file->getFilename();

			// Match pattern: starts with "wpp-al-" and ends with ".php"
			if (fnmatch('wpp-al-*.php', $filename)) {
				require_once $file->getPathname();
			}
		}
	}
}

recursive_require_wpp_al(__DIR__ );

function removeEmptySubarrays($array) {
	return array_filter($array, function($item) {
		return !is_array($item) || !empty($item);
	});
}