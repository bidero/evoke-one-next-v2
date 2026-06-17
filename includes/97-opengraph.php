<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke One — Moduł OpenGraph (loader)
 *
 * Generuje obrazy OG na podstawie konfigurowalnych warstw.
 */

require_once __DIR__ . '/opengraph/settings.php';
require_once __DIR__ . '/opengraph/image-generator.php';
require_once __DIR__ . '/opengraph/hooks.php';
