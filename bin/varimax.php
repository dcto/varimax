<?php
#!/usr/bin/php

//define _VM_ of constants
defined('_VM_') || define('_VM_',dirname(__DIR__, 1));

//define _DS of constants
defined('_DS_') || define('_DS_', DIRECTORY_SEPARATOR);

//define _DOC_ constants to document root
defined('_DOC_') || define('_DOC_', PHP_SAPI == 'cli' ? getcwd() : dirname($_SERVER['DOCUMENT_ROOT']));

//define _APP_ constants of app name
defined('_APP_') || define('_APP_', ($app = basename($_SERVER['SCRIPT_FILENAME'],'.php')) == 'index' ? 'app' : $app);

//define _DIR_ constants of app root path
defined('_DIR_') || define('_DIR_', _DOC_._DS_._APP_);

//define _WWW_ constants of public path
defined('_WWW_') || define('_WWW_', _DOC_._DS_.'www');

//define _ROOT_ constants of root
defined('_ROOT_') || define('_ROOT_', _DOC_);

//define _RUNTIME_ constants of runtime path
defined('_RUNTIME_') || define('_RUNTIME_', _ROOT_._DS_.'runtime');

/**
 * Autoloader
 */
$loader = require(_DOC_._DS_.'vendor'._DS_.'autoload.php');

//Set App Psr
$loader->setPsr4("App\\", _DIR_);

//Add App Model
$loader->addPsr4("App\\Model\\", _ROOT_._DS_.'model'._DS_);

//Add App Provider
$loader->addPsr4("App\\Provider\\", _ROOT_._DS_.'provider'._DS_);

/**
 * Load Environment
 */
is_file(_DOC_ . _DS_ . '.env') && \Dotenv\Dotenv::createUnsafeImmutable(_DOC_)->load();

/*
 * Load helpers
 */
is_file($helper = _DIR_._DS_.'helper.php') && require($helper); require('library.php');

/**
 * Bootstrap of Application
 */
\VM\Application::boostrap();
