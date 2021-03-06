<?php
/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */

//define _VM_ of constants
defined('_VM_') || define('_VM_', __DIR__);

//define _DS of constants
defined('_DS_') || define('_DS_', DIRECTORY_SEPARATOR);

//define _DOC_ constants to document root
defined('_DOC_') || define('_DOC_', PHP_SAPI == 'cli' ? dirname(realpath($_SERVER['argv'][0])) : dirname($_SERVER['DOCUMENT_ROOT']));

//define _APP_ constants of app name
defined('_APP_') || define('_APP_', ($app = basename($_SERVER['SCRIPT_FILENAME'],'.php')) == 'index' ? isset( $_GET['app']) ?  $_GET['app']  : 'app' : $app);

//define _DIR_ constants of app root path
defined('_DIR_') || define('_DIR_', _DOC_._DS_._APP_);

//define _WWW_ constants of public path
defined('_WWW_') || define('_WWW_', _DOC_._DS_.'www');

//define _ROOT_ constants of root
defined('_ROOT_') || define('_ROOT_', _DOC_);

//define _TIME_ constants to macro time
defined('_TIME_') || define('_TIME_', microtime(true));

//Access Denied!
PHP_SAPI == 'cli' || is_dir(_DIR_) || die('Access Denied.');

/**
 * Load DotEnv Environment
 */
is_file(_DOC_ . _DS_ . '.env') && \Dotenv\Dotenv::create(_DOC_)->load();

/**
 * load helper from app
 */
is_file($helper = _DIR_._DS_.'helper.php') && require($helper);

/**
 * load common from docker
 */
is_file($common = _DOC_._DS_.'library'._DS_.'common.php') && require($common);

/**
 * load helper from varimax
 */
is_file($function = _VM_._DS_.'Support'._DS_.'Function.php') && require($function);

/**
 * @var $loader \Composer\Autoload\ClassLoader
 */
$loader = require(_ROOT_._DS_.'vendor'._DS_.'autoload.php');

//Set App Psr4
$loader->setPsr4("App\\", _DIR_);

//add App Model
$loader->addPsr4("App\\Model\\", _ROOT_._DS_.'model'._DS_);

//add App Provider
$loader->addPsr4("App\\Provider\\", _ROOT_._DS_.'provider'._DS_);

/**
 * Bootstrap Of Application
 */
(new \VM\Application())->boot();