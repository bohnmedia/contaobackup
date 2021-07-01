<?php

function generateDefaultBackupKey() {

    $output = '';
    $allowedChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $max = strlen($allowedChars) - 1;
    for ($i=0; $i<32; $i++) {
        $output .= substr($allowedChars, rand(0, $max), 1);
    }
    return $output;

}

// Set default backup key
if (!isset($GLOBALS['TL_CONFIG']['backupKey'])) {
    $GLOBALS['TL_CONFIG']['backupKey'] = generateDefaultBackupKey();
}