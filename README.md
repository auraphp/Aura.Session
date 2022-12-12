# Aura Session

Provides session management functionality, including lazy session starting,
session segments, next-request-only ("flash") values, and CSRF tools.

## Foreword

### Installation

This library requires PHP 7.2 or later. It has been tested on PHP 7.2-8.2. we recommend using the latest available version of PHP as a matter of principle. It has no userland dependencies.

It is installable and autoloadable via Composer as [aura/session](https://packagist.org/packages/aura/session).

Alternatively, [download a release](https://github.com/auraphp/Aura.Session/releases) or clone this repository, then require or include its _autoload.php_ file.

### Quality

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/auraphp/Aura.Session/badges/quality-score.png?b=4.x)](https://scrutinizer-ci.com/g/auraphp/Aura.Session/?branch=4.x)
[![codecov](https://codecov.io/gh/auraphp/Aura.Session/branch/4.x/graph/badge.svg)](https://codecov.io/gh/auraphp/Aura.Session)
[![Continuous Integration](https://github.com/auraphp/Aura.Session/actions/workflows/continuous-integration.yml/badge.svg?branch=4.x)](https://github.com/auraphp/Aura.Session/actions/workflows/continuous-integration.yml)

To run the unit tests at the command line, issue `composer install` and then `phpunit` at the package root. This requires [Composer](http://getcomposer.org/) to be available as `composer`, and [PHPUnit](http://phpunit.de/manual/) to be available as `phpunit`.

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

### Community

To ask questions, provide feedback, or otherwise communicate with the Aura community, please join our [Google Group](http://groups.google.com/group/auraphp), follow [@auraphp on Twitter](http://twitter.com/auraphp), or chat with us on #auraphp on Freenode.


## Documentation

This package is fully documented [here](./docs/getting-started.md).
