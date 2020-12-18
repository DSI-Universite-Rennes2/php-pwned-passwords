# PHP Pwned Passwords

[![reuse compliant](https://reuse.software/badge/reuse-compliant.svg)](https://reuse.software/) ![PHP](https://github.com/DSI-Universite-Rennes2/php-pwned-passwords/workflows/PHP%20Composer/badge.svg) [![Coverage Status](https://coveralls.io/repos/github/DSI-Universite-Rennes2/php-pwned-passwords/badge.svg?branch=master)](https://coveralls.io/github/DSI-Universite-Rennes2/php-pwned-passwords?branch=master)

A PHP library for the [Pwned Passwords's](https://haveibeenpwned.com/Passwords) API from Troy Hunt's [Have I Been Pwned](https://haveibeenpwned.com/) project.

The main feature compare to others is that you can configure your [own API endpoint](https://github.com/tylerchr/pwnedpass) if don't want to use HIBP's API.


## Table of Contents

- [What about security of the Pwned Password API ?](#what-about-security-of-the-pwned-password-api-)
- [Install](#install)
- [Usage](#usage)
- [Contribute](#contribute)
- [License](#license)


## What about security of the Pwned Password API ?

Testing real passwords on a remote API ? What about security and privacy ?

You don't send the password to the API, only the first 5 characters of the SHA1 password's hash are sent to the endpoint API.
It's the implementation of a mathematical property called [k-anonymity](https://en.wikipedia.org/wiki/K-anonymity).

Not enough for you ? You can build your own API by using [this Golang project](https://github.com/tylerchr/pwnedpass) :
- to build an optimized binary file from [the official database files](https://haveibeenpwned.com/Passwords)
- to run an httpd handler who reproduce the HIBP Pwned Password API.

This PHP Pwned Passwords lib permit you to change the API endpoint.

Read more about :
- [Troy Hunt API's internals](https://www.troyhunt.com/ive-just-launched-pwned-passwords-version-2/)
- [validating leaked password with k-anonymity](https://blog.cloudflare.com/validating-leaked-passwords-with-k-anonymity/)

## Install

```
composer require univ-rennes2/pwned-passwords
```

## Usage

```
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use UniversiteRennes2\PwnedPasswords\PwnedPasswords;

$PPApi = new PwnedPasswords();

// Set new URI for API if have local API server (like this API server in Golang : <https://github.com/tylerchr/pwnedpass>)
// default is set to Troy Hunt's API.
// $PPApi->setApiUrl('http://127.0.0.1:3000/range');

$password = '123456';

// Just test of pwned or not
if ($PPApi->isPwned($password)) {
    // Pwned password
    printf("%s have been pwned\n", $password);
} else {
    // Not Pwned password
    printf("%s have NOT been pwned\n", $password);
}

// To knows how many times it have been pwned
$pwned = $PPApi->howManyPwned($password);
if ($pwned > 0) {
    // Pwned
    printf("%s have been pwned %s times\n", $password, $pwned);
} else {
    // Not pwned
    printf("%s have NOT been pwned\n");
}
```

## Contribute

See [CONTRIBUTING.md](CONTRIBUTING.md)

## License

This program is free software: you can redistribute it and/or modify
it under the terms of the [GNU Lesser General Public License v3.0 or later](LICENCE)
as published by the Free Software Foundation.

The program in this repository meet the requirements to be REUSE compliant,
meaning its license and copyright is expressed in such as way so that it
can be read by both humans and computers alike.

For more information, see https://reuse.software/
