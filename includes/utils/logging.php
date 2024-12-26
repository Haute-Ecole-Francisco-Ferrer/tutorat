<?php
/**
 * Centralized logging utility
 */

function debug_log($context, $message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] DEBUG [$context]: $message";
    error_log($log_message);
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            error_log("DATA: " . print_r($data, true));
        } else {
            error_log("DATA: " . $data);
        }
    }
}