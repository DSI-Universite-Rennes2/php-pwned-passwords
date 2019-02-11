<?php

declare(strict_types=1);

/*
 * Copyright (c) 2018-2019 Yann 'Ze' Richard <ze@nbox.org>
 *
 * SPDX-License-Identifier: LGPL-3.0-or-later
 * License-Filename: LICENSE
 */
namespace UniversiteRennes2\PwnedPasswords\tests\units;

require_once realpath(__DIR__ . '/../../src/PwnedPasswords.php');

use atoum;

class PwnedPasswords extends atoum
{
    public function testConstruct() : void
    {
        $this->assert('test curl is not present')
            ->given($this->function->extension_loaded = false)
            ->exception(
                function () : void {
                    $this->newTestedInstance();
                }
            )
        ;

        $this->assert('test mock TLS 1.2 not capable')
            ->given($this->function->defined = false)
            ->and($this->function->extension_loaded = true)
            ->exception(
                function () : void {
                    $this->newTestedInstance();
                }
            )
        ;
    }

    public function testSetApiUrl() : void
    {
        $i =0;

        $i++;
        $this->assert(__METHOD__ . ' : test #' . $i)
            ->if($this->newTestedInstance())
            ->and($this->testedInstance->setApiUrl('http://example.com/range/'))
            ->then
                ->string($this->testedInstance->apiUrl)->isEqualTo('http://example.com/range/')
        ;

        $i++;
        $this->assert(__METHOD__ . ' : test #' . $i)
            ->if($this->newTestedInstance())
            ->and($this->testedInstance->setApiUrl('http://example.com/range'))
            ->then
                ->string($this->testedInstance->apiUrl)->isEqualTo('http://example.com/range/')
        ;
    }

    public function testSetCurlOptions() : void
    {
        $i =0;

        $options = array('blah' => 'bloh');

        $i++;
        $this->assert(__METHOD__ . ' : test #' . $i)
            ->if($this->newTestedInstance())
            ->and($this->testedInstance->setCurlOptions($options))
            ->then
                ->array($this->testedInstance->curlOptions)->isIdenticalTo($options)
        ;
    }

    public function testHowManyPwned() : void
    {
        $i =0;

        $pass = 'jesuisnul';
        $count = 34;

        $i++;
        $this->assert(__METHOD__ . ' : test #' . $i)
            ->if($this->newTestedInstance())
            ->then
                ->integer($this->testedInstance->howManyPwned($pass))->isEqualTo($count)
        ;
    }

    public function testIPwned() : void
    {
        $i =0;

        $i++;
        $pass = '123456';
        $options = array(
            CURLOPT_HEADER => false
        );
        $this->assert(__METHOD__ . ' : test #' . $i)
            ->if($this->newTestedInstance())
            ->and($this->testedInstance->setCurlOptions($options))
            ->then
                ->boolean($this->testedInstance->isPwned($pass))->isTrue
        ;

        $i++;
        $pass = 'UgpIlamNHVpCNufpSuaoYypfOjMmCwxkKgLHXoIdejqzU0KA9f';
        $this->assert(__METHOD__ . ' : test #' . $i)
            ->if($this->newTestedInstance())
            ->then
                ->boolean($this->testedInstance->isPwned($pass))->isFalse
        ;
    }

    public function testIsPwnedWithMockCurlExec() : void
    {
        $i =0;

        $this->assert(__METHOD__ . ' : test curl_exec return false')
            ->given($this->newTestedInstance())
            ->if($this->function->curl_exec = false)
            ->then
            ->exception(function () : void {
                $this->testedInstance->isPwned('dontcareaboutit');
            })
        ;
    }

    public function testIsPwnedWithMockCurlGetInfo() : void
    {
        $i =0;
        $returnCode = array(503, 429, 500);
        foreach ($returnCode as $code) {
            $i++;
            $this->assert(__METHOD__ . ' : test #' . $i . ' curl response code ' . $code)
                ->given($this->newTestedInstance())
                ->if($this->function->curl_getinfo = $code)
                ->then
                    ->exception(function () : void {
                        $this->testedInstance->isPwned('dontcareaboutit');
                    })
            ;
        }
    }
}
