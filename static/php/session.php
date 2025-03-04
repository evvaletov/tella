<?php
function validateToken($token) {
    if (empty($token)) {
        return false;
    }

    // Check token format (48 hex characters)
    if (!preg_match('/^[0-9a-f]{48}$/', $token)) {
        return false;
    }

    // Optional: Store used tokens in a file/database to prevent reuse
    $used_tokens_file = sys_get_temp_dir() . '/used_tokens.txt';
    if (file_exists($used_tokens_file)) {
        $used_tokens = file($used_tokens_file, FILE_IGNORE_NEW_LINES);
        if (in_array($token, $used_tokens)) {
            return false;
        }
    }

    // Store the used token
    file_put_contents($used_tokens_file, $token . PHP_EOL, FILE_APPEND);
    
    return true;
}
