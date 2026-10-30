<?php
/**
 * config/app.php
 * General settings for the whole application.
 */

define('APP_NAME', 'UHY WorkDoc Extract');

define('BASE_URL', '/uhy-documentflow');

define('EMAIL_DOMAIN', 'uhy.docs.hub.com');

define('DEFAULT_PASSWORD', 'UHY@123');

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES', 10);

define('SESSION_TIMEOUT', 60 * 60 * 8);

define('ROOT_PATH', dirname(__DIR__));

define('APP_DEBUG', true);

// ---- OCR ---------------------------------------------------------------
// Change this to wherever Tesseract was installed on this machine.
define(
    'TESSERACT_PATH',
    'C:\\Program Files\\Tesseract-OCR\\tesseract.exe'
);

define('OCR_LANGUAGE', 'eng');

date_default_timezone_set('Asia/Kuala_Lumpur');