<?
	// ====================================================================== //

	// -- caching filter

	header('Pragma: private');
	header('Cache-control: private, must-revalidate');

	// ---
	
	// -- logging filter
	
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
	ini_set('display_errors', '1');

	// ---
	
	require_once "_classes/_inf/_core/BaseConfig.php";
	include_once('_lib/symfony/yaml/sfYaml.class.php');
	require_once "config.php";

	$config = new Config('', false, $debug=0);
	
	$config->LoadOrqi();
	$config->LoadProject();
	
	$controller_loader = new Loader($_GET, $_POST, $_FILES);
	if ($controller_loader->VerifyController()) $controller_loader->Execute();
	else $controller_loader->DisplayError();
	
	// ====================================================================== //
?>