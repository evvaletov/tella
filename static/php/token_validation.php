<?php
function validateToken($token) {
    if (empty($token)) {
        error_log('Empty token received');
        return false;
    }

    // Validate token format (64 characters of hex)
    if (!preg_match('/^[0-9a-f]{64}$/', $token)) {
        error_log('Invalid token format');
        return false;
    }

    // Store used tokens with timestamp
    $tokens_file = sys_get_temp_dir() . '/form_tokens.json';
    $used_tokens = [];
    
    if (file_exists($tokens_file)) {
        $used_tokens = json_decode(file_get_contents($tokens_file), true) ?: [];
        
        // Clean up old tokens (older than 24 hours)
        $used_tokens = array_filter($used_tokens, function($timestamp) {
            return $timestamp > (time() - 86400);
        });
    }

    // Check if token has been used
    if (isset($used_tokens[$token])) {
        error_log('Token already used');
        return false;
    }

    // Store the new token with current timestamp
    $used_tokens[$token] = time();
    file_put_contents($tokens_file, json_encode($used_tokens));
    
    return true;
}
