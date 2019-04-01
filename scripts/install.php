<?php
/**
 * src: https://github.com/worksolutions/bitrix-reduce-migrations/blob/master/scripts/install.php
 */


$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../../../..');
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('CHK_EVENT', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
require(__DIR__ . '/../install/index.php');

$module = new yngc0der_tauth();
$module->DoInstall();

echo 'ok';
