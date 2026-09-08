# Aura Session

Provides session management functionality, including lazy session starting,
session segments, next-request-only ("flash") values, and CSRF tools.

## Foreword

### Installation

This library requires PHP 8.4 or later. It has been tested on PHP 8.4-8.5. We recommend using the latest available version of PHP as a matter of principle. Its only dependency is [aura/session-interface](https://packagist.org/packages/aura/session-interface), which provides the shared session and segment contracts.

It is installable and autoloadable via Composer as [aura/session](https://packagist.org/packages/aura/session).

### Quality

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/auraphp/Aura.Session/badges/quality-score.png?b=4.x)](https://scrutinizer-ci.com/g/auraphp/Aura.Session/?branch=4.x)
[![codecov](https://codecov.io/gh/auraphp/Aura.Session/branch/4.x/graph/badge.svg)](https://codecov.io/gh/auraphp/Aura.Session)
[![Continuous Integration](https://github.com/auraphp/Aura.Session/actions/workflows/continuous-integration.yml/badge.svg?branch=4.x)](https://github.com/auraphp/Aura.Session/actions/workflows/continuous-integration.yml)

To run the unit tests at the command line, issue `composer install` and then `phpunit` at the package root. This requires [Composer](http://getcomposer.org/) to be available as `composer`, and [PHPUnit](http://phpunit.de/manual/) to be available as `phpunit`.

This library attempts to comply with [PSR-1][], [PSR-12][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-12]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

### Community

To ask questions, provide feedback, or otherwise communicate with the Aura community, please join our [Google Group](http://groups.google.com/group/auraphp) or follow [@auraphp on X](https://x.com/auraphp).


## Documentation

This package is fully documented [here](./docs/getting-started.md).
