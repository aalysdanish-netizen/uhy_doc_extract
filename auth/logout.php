<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Logging out must be a POST with a CSRF token, so another site cannot log people out.
if (is_post() && csrf_verify()) {
    logout_user();
    session_start();                                   // fresh session just to carry the message
    flash_set('success', 'You have been signed out.');
}

redirect('auth/login.php');