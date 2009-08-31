<?
	// ====================================================================== //

	require_once 'PHPUnit/Framework.php';
	require_once "_classes/_inf/_core/BaseConfig.php";
	include_once('_lib/symfony/yaml/sfYaml.class.php');
	require_once "config.php";

	$config = new Config('', false, $debug=0);
	
	$config->LoadOrqi();
	$config->LoadProject();
	$config->LoadTestFiles();
	 
	// Create a test suite that contains the tests
	// from the ArrayTest class.	
	$testharness = new TestHarness();
	$suite = $testharness->GetSuite();
	 
	// Create a test result and attach a SimpleTestListener
	// object as an observer to it.
	$result = new PHPUnit_Framework_TestResult();
	$result->addListener(new TestListener());
	 
	// Run the tests.
	$suite->run($result);
		
	// ====================================================================== //
?>