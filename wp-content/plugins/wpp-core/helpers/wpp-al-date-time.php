<?php
defined( 'ABSPATH' ) || exit;

/**
 * Returns a human-readable time difference string like "X seconds ago", "Y days ago", etc.
 *
 * @param string $datetime Date and time in 'Y-m-d H:i:s' format.
 *
 * @return string A formatted time-ago string in English, e.g., "5 minutes ago", "2 days ago".
 *
 * @example
 * echo time_ago('2025-07-14 21:55:57');
 * // Output might be: "3 hours ago"
 */
function wpp_time_ago($datetime) {

	$timestamp = strtotime($datetime);
	$current_time = time();
	$diff = $current_time - $timestamp;

	if ($diff < 60) {
		return $diff . ' second' . ($diff !== 1 ? 's' : '') . ' ago';
	} elseif ($diff < 3600) {
		$minutes = floor($diff / 60);
		return $minutes . ' minute' . ($minutes !== 1 ? 's' : '') . ' ago';
	} elseif ($diff < 86400) {
		$hours = floor($diff / 3600);
		return $hours . ' hour' . ($hours !== 1 ? 's' : '') . ' ago';
	} else {
		$days = floor($diff / 86400);
		return $days . ' day' . ($days !== 1 ? 's' : '') . ' ago';
	}
}