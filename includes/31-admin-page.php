<?php
if (!defined('ABSPATH')) exit;
/**
 * Evoke ONE — TL Admin Page
 * Loader — logika podzielona na includes/admin/tl/
 */

$_tl = __DIR__ . '/admin/tl/';
require $_tl . 'bootstrap.php';
require $_tl . 'render.php';
require $_tl . 'ajax.php';
require $_tl . 'js-admin.php';
