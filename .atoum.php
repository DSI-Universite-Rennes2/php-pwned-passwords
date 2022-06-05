<?php
/*
This file will automatically be included before EACH run.

Use it to configure atoum or anything that needs to be done before EACH run.

More information on documentation:
[en] http://docs.atoum.org/en/chapter3.html#Configuration-files
[fr] http://docs.atoum.org/fr/chapter3.html#Fichier-de-configuration
*/


use
    atoum\atoum,
    atoum\atoum\reports,
    atoum\atoum\writers\std
;
//use mageekguy\atoum\reports;

$runner
    ->addTestsFromDirectory(__DIR__ . '/tests/units/')
    ->disallowUsageOfUndefinedMethodInMock();

$runner->getScore()->getCoverage();

$CI = getenv('coverage');
if ($CI)
{
    $script->addDefaultReport();

    $coverallsToken = getenv('COVERALLS_REPO_TOKEN') ?: null;
    if ($coverallsToken)
    {
        echo "  COVERALLS Token detected...\n";
        $coverallsReport = new reports\asynchronous\coveralls('classes', $coverallsToken);
        $defaultFinder = $coverallsReport->getBranchFinder();
        $coverallsReport
            ->setBranchFinder(function() use ($defaultFinder) {
                    if (($branch = getenv('GITHUB_BRANCH')) === false)
                    {
                        $branch = $defaultFinder();
                    }
                    return $branch;
                }
            )
            ->setServiceName('github-actions')
            ->setServiceJobId(getenv('GITHUB_RUN_NUMBER') ?: null)
            ->addDefaultWriter()
        ;
        $runner->addReport($coverallsReport);
    } else {
        echo "Missing coveralls token\n";
    }
} else {
    echo "No coverage reports (missing coverage env variable) : $CI\n";
}
