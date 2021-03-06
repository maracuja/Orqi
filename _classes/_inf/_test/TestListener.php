<?php
	if (class_exists(PHPUnit_Framework_TestCase))
	{
		/**
		 * Looks nowt like the SimpleTestListener in the phpunit examples. no sirree.
		 */
		class TestListener implements PHPUnit_Framework_TestListener
		{
			public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
			{
				printf("Error while running test '%s'.<br>", $test->getName());
			}
		 
			public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
			{
				printf("Test '%s' failed.<br>", $test->getName());
			}
		 
			public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
			{
				// printf("Test '%s' is incomplete.\n", $test->getName());
			}
		 
			public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
			{
				// printf("Test '%s' has been skipped.\n", $test->getName());
			}
		 
			public function startTest(PHPUnit_Framework_Test $test)
			{
				printf("<h3>Test '%s' started.</h3>", $test->getName());
			}
		 
			public function endTest(PHPUnit_Framework_Test $test, $time)
			{
				// printf("Test '%s' ended.\n", $test->getName());
			}
		 
			public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
			{
				printf("<hr><h2>TestSuite '%s' started.</h2>", $suite->getName());
			}
		 
			public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
			{
				// printf("TestSuite '%s' ended.\n", $suite->getName());
			}
		}
	}
