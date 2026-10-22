<?php
declare(strict_types=1);

define('APP_DIR', __DIR__);
define('ROOT_DIR', dirname(__DIR__));
define('STORAGE_DIR', ROOT_DIR . '/storage');
// 公開フォルダ。public/ 内に app/ を移した構成でも動くように index.php 側で決める。
if (!defined('PUBLIC_DIR')) {
    define('PUBLIC_DIR', ROOT_DIR . '/public');
}

date_default_timezone_set('Asia/Tokyo');
mb_internal_encoding('UTF-8');

$configFile = APP_DIR . '/config.php';
$GLOBALS['config'] = is_file($configFile) ? require $configFile : require APP_DIR . '/config.sample.php';
$GLOBALS['site'] = require APP_DIR . '/site.php';

if (!empty($GLOBALS['config']['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_DIR . '/logs/php-error.log');
}

require APP_DIR . '/lib/helpers.php';
require APP_DIR . '/lib/media.php';
require APP_DIR . '/lib/db.php';
require APP_DIR . '/lib/content.php';
require APP_DIR . '/lib/seo.php';
require APP_DIR . '/lib/legacy.php';
require_once APP_DIR . '/lib/html.php';
require APP_DIR . '/lib/images.php';
require APP_DIR . '/lib/instagram.php';
