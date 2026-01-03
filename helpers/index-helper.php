<?php
// Logging helper function
function logMessage($category, $data, $level = 'info') {
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'category' => $category,
        'data' => $data
    ];

    $file = $level === 'debug' ? 'log/debug.log' : 'log/app.log';
    file_put_contents($file, json_encode($entry) . "\n");
}

// Fungsi handle posisi user
function updateUserPosition($chatid, $menu, $submenu = '') {
    $sql = "UPDATE smm_users SET menu = ?, submenu = ? WHERE chatid = ?";
    $params = [$menu, $submenu, $chatid];

    $result = db_execute($sql, $params);

    // Log position update
    logMessage('position', [
        'chatid' => $chatid,
        'menu' => $menu,
        'submenu' => $submenu,
        'result' => $result
    ]);

    if($menu === "main") {
        $result = 1;
    }
    return $result;
}
?>