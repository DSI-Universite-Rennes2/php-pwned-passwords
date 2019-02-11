<?php
/*
This file will automatically be included before EACH run.

Use it to configure atoum or anything that needs to be done before EACH run.

More information on documentation:
[en] http://docs.atoum.org/en/chapter3.html#Configuration-files
[fr] http://docs.atoum.org/fr/chapter3.html#Fichier-de-configuration
*/

use mageekguy\atoum\reports;

$runner
    ->addTestsFromDirectory(__DIR__ . '/tests/units/')
    ->disallowUsageOfUndefinedMethodInMock();

$runner->getScore()->getCoverage();

$travis = getenv('TRAVIS');
if ($travis)
{
    $script->addDefaultReport();
    $coverallsToken = getenv('COVERALLS_REPO_TOKEN');
    if ($coverallsToken)
    {
        $coverallsReport = new reports\asynchronous\coveralls('classes', $coverallsToken);
        $defaultFinder = $coverallsReport->getBranchFinder();
        $coverallsReport
            ->setBranchFinder(function() use ($defaultFinder) {
                    if (($branch = getenv('TRAVIS_BRANCH')) === false)
                    {
                        $branch = $defaultFinder();
                    }
                    return $branch;
                }
            )
            ->setServiceName(getenv('TRAVIS') ? 'travis-ci' : null)
            ->setServiceJobId(getenv('TRAVIS_JOB_ID') ?: null)
            ->addDefaultWriter()
        ;
        $runner->addReport($coverallsReport);
    }
}
