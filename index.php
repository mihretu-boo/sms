<?php
/**
 * Shalaka Jatan Ali Secondary School Management System
 * Front Controller
 */

define('ROOT', __DIR__);

// Start session
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_name('SJASSMS_SESSION');
session_start();

// Load configuration
require_once ROOT . '/config/database.php';
require_once ROOT . '/config/app.php';

// Load core classes
require_once ROOT . '/app/Core/Router.php';
require_once ROOT . '/app/Core/Controller.php';
require_once ROOT . '/app/Core/Auth.php';
require_once ROOT . '/app/Core/Flash.php';
require_once ROOT . '/app/Core/Helpers.php';
require_once ROOT . '/app/Core/Mailer.php';
require_once ROOT . '/app/Core/RateLimiter.php';

// Check session timeout
Auth::checkSession();

// Generate CSRF token if not set
Auth::generateCsrf();

// Dispatch the request
$router = new Router();
$router->dispatch();
