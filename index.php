<?php
session_start();

/**
 * Login check excluded from this demo classes usage
 */

/**
 * Paths
 * Change path according to place of app dir you had choosen. 
 * ('/app' is default and should be changed in real life.)
 */
$appPath = '/app';

// 
/**
 * Autoload all from Library (Basic autoloader for dempo - no need composer here)
 * This is just simplest version for local usage. So, no need for composer to make 
 * autoload Alos no need fo namespaces.
 * We 
 */
spl_autoload_register(function ($className) {

	// Put dir on hidden location and change following apth acordingly if you wush.
	$librarryDir = __DIR__ . '/app/library/';
	$file = $librarryDir . str_replace('\\', '/', $className) . '.php';

	// if the file exists, require it
	if (file_exists($file)) {
		require_once $file;
	} else {
		throw new Exception("The file $file does not exist.");
	}
});


// Define few locations
$controllersDir = __DIR__ . $appPath  . '/controllers';
$modelsDir = __DIR__ . $appPath  . '/models';
$coreDir = __DIR__ . $appPath  . '/core';
$viewsDir = $appPath  . '/views';
$layoutsDir = __DIR__ . $appPath  . '/views/layouts';

// Set defaults
$layout = "default";
$lanuage = "en";

// Analyze params (from url) and resolve classes and methods
$appContoller = (string)filter_input(INPUT_GET, 'c');
$appAction = (string)filter_input(INPUT_GET, 'a');
$lang = (string)filter_input(INPUT_GET, 'l');

if (!empty($appContoller)) {
	$appContoller = $appContoller;
	if (!empty($appAction)) {
		$appAction = $appAction;
	} else {
		$appAction = "index";
	}
} else {
	// Default is: homepage.
	$appContoller = 'Home';
	$appAction = 'index';
}

/**
 * In this demo environment we will use no one user.
 * In no-framework repo. we may have more users and complex login system.
 * So, we use only predefined default layout and controller.
 */
//$layout = "notlogged";
//$appContoller = 'Demo';
//$appAction = 'index';

/**
 * In this demo environment we will use only one language.
 * In no-framework repo. we have more languages and complex language system.
 * So, we use only predefined default language.
 */
$allowedLanguages = array('en');

if (!empty($lang)) {
	if (in_array($lanuage, $allowedLanguages)) {
		$lanuage = $lang;
	} else {
		$lanuage = 'en';
	}
}

/**
 * For include by default (no need autoload/composer)
 * Modes must have same name as controller wits added "s" at the end of controller name.
 * Don't change following. It is expected structure.
 */
$controller = $controllersDir . "/" . $appContoller . ".php";
$model = $modelsDir . "/" . $appContoller . "s.php";
$lib = $coreDir . "/lib.php";
$db = $coreDir . "/db.php";
$root = $coreDir . "/root.php";


if (file_exists($controller) && file_exists($model)) {

	// Load necessary things...	
	require $root;
	require $db;
	require $lib;
	require $model;
	require $controller;

	// Prepare things
	$className = $appContoller;
	$class = new $className();

	if (!method_exists($class, $appAction)) {
		die('Bad url!');
	}

	// Set things
	$class->interfaceLanguage = $lanuage;  // User's language
	$class->controller = $appContoller;  // Controller name
	$class->action = $appAction;  // Controller targer public method
	$class->views = $viewsDir;  // Views location
	$class->view = $appAction;  // Action name
	$class->layout = $layout;  // Layout

	// Here we go... 
	$data = call_user_func(array($class, $appAction));
	if ($class->layout) {
		if (file_exists($layoutsDir . "/" . $class->layout . ".php")) {
			require $layoutsDir . "/" . $class->layout . ".php";
		} else {
			die('Layout does not exist.');
		}
	}
} else {
	die('Bad url.');
}
