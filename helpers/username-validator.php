<?php

/**
 * Validate and sanitize username input
 *
 * @param string $username Raw username input
 * @param string $platform Platform name (instagram, tiktok, etc.)
 * @return array Validation result with 'valid', 'username', and 'message' keys
 */
function validateUsername($username, $platform) {
    // Trim whitespace
    $username = trim($username);

    // Check if empty
    if (empty($username)) {
        return [
            'valid' => false,
            'username' => '',
            'message' => '❌ Username tidak boleh kosong!'
        ];
    }

    // Remove @ if user enters @username
    if (strpos($username, '@') === 0) {
        $username = substr($username, 1);
    }

    // Check if empty after removing @
    if (empty($username)) {
        return [
            'valid' => false,
            'username' => '',
            'message' => '❌ Username tidak valid!'
        ];
    }

    // Check if username already exists for this platform
    $existing_username = db_read('smm_social_accounts', [
        'platform' => $platform,
        'username' => $username
    ]);

    if (!empty($existing_username)) {
        return [
            'valid' => false,
            'username' => $username,
            'message' => "❌ Username @{$username} sudah digunakan oleh pengguna lain. Silakan gunakan username lain."
        ];
    }

    // Username is valid
    return [
        'valid' => true,
        'username' => $username,
        'message' => ''
    ];
}

/**
 * Generate platform URL based on username
 *
 * @param string $platform Platform name
 * @param string $username Username
 * @return string Platform URL
 */
function generatePlatformUrl($platform, $username) {
    $urls = [
        'instagram' => "https://instagram.com/{$username}",
        'tiktok' => "https://tiktok.com/@{$username}",
        'twitter' => "https://twitter.com/{$username}",
        'facebook' => "https://facebook.com/{$username}",
        'youtube' => "https://youtube.com/@{$username}"
    ];

    return $urls[$platform] ?? "#";
}

/**
 * Get platform icon for display
 *
 * @param string $platform Platform name
 * @return string Platform icon emoji
 */
function getPlatformIcon($platform) {
    $icons = [
        'instagram' => '📷',
        'tiktok' => '🎵',
        'twitter' => '🐦',
        'facebook' => '📘',
        'youtube' => '📺',
        'linkedin' => '💼'
    ];

    return $icons[$platform] ?? '🌐';
}

?>
