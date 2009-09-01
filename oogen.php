<?
	// ====================================================================== //

	require_once "_classes/_inf/_core/BaseConfig.php";
	include_once('_lib/symfony/yaml/sfYaml.class.php');
	require_once "config.php";

	$config = new Config('', false, $debug=0);
	$config->LoadOrqi();
	
	$gc = new Generator();
	$gc->Run();
	
	// ====================================================================== //
?>