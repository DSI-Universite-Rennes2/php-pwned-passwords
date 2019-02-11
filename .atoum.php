<?php
/*
This file will automatically be included before EACH run.

Use it to configure atoum or anything that needs to be done before EACH run.

More information on documentation:
[en] http://docs.atoum.org/en/chapter3.html#Configuration-files
[fr] http://docs.atoum.org/fr/chapter3.html#Fichier-de-configuration
*/

$report = $script->addDefaultReport();

/*
LOGO
*/
// This will add the atoum logo before each run.
// $report->addField(new atoum\report\fields\runner\atoum\logo());

// This will add a green or red logo after each run depending on its status.
// $report->addField(new atoum\report\fields\runner\result\logo());

/*
CODE COVERAGE SETUP
*/
// Please replace in next line "Project Name" by your project name and "/path/to/destination/directory" by your destination directory path for html files.
$coverage_field = new atoum\report\fields\runner\coverage\html('php-pwned-passwords', __DIR__.'/reports/atoum');

// Please replace in next line http://url/of/web/site by the root url of your code coverage web site.
// $coverage_field->setRootUrl('http://url/of/web/site');

$report->addField($coverage_field);

/*
TEST GENERATOR SETUP
*/
$test_generator = new atoum\test\generator();

// Please replace in next line "/path/to/your/tests/units/classes/directory" by your unit test's directory.
$test_generator->setTestClassesDirectory(__DIR__.'/tests/units/');

// Please replace in next line "your\project\namespace\tests\units" by your unit test's namespace.
$test_generator->setTestClassNamespace('UniversiteRennes2\PwnedPasswords\tests\units');

// Please replace in next line "/path/to/your/classes/directory" by your classes directory.
$test_generator->setTestedClassesDirectory(__DIR__.'/src/PwnedPasswords.php');

// Please replace in next line "your\project\namespace" by your project namespace.
$test_generator->setTestedClassNamespace('UniversiteRennes2\PwnedPasswords');

// Please replace in next line "path/to/your/tests/units/runner.php" by path to your unit test's runner.
// $test_generator->setRunnerPath('path/to/your/tests/units/runner.php');

$script->getRunner()->setTestGenerator($test_generator);

