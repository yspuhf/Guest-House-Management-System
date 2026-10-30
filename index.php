<?php
/**
 * Dr. Y.S. Parmar UHF Nauni - Central Guest House Portal
 * Production Router & Fallback Handler for Hostinger
 */

// If Laravel framework is installed and configured, route through public/index.php
if (file_exists(__DIR__.'/vendor/laravel/framework') && file_exists(__DIR__.'/public/index.php')) {
    require_once __DIR__.'/public/index.php';
    exit;
}

// Default: Safely serve the interactive Guest House Portal
if (file_exists(__DIR__.'/index.html')) {
    include __DIR__.'/index.html';
    exit;
}
