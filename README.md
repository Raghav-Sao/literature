[![Build Status](https://travis-ci.org/Raghav-Sao/literature.svg?branch=master)](https://travis-ci.org/Raghav-Sao/literature)

[http://139.59.1.64/](http://139.59.1.64/)

## literature

A Symfony project created on November 12, 2016, 9:25 am.

Play literature(card game) online with friends or bot.

## Setup

Refer [this](https://github.com/Raghav-Sao/literature/wiki/Set-up-literature) wiki page.

## Development

### Api specs

Refer [this](https://github.com/Raghav-Sao/literature/wiki/Api-Specs) wiki page __[DEPRECATED]__

### Unit tests

- Run `./phpunit.phar  --debug`
- To generate code coverage: `./phpunit.phar  --debug --coverage-html=coverage`

  Refer [this](https://gist.github.com/hollodotme/418e9b7c6ebc358e7fda#install-xdebug-extension) for installation of xdebug extension.

### Coding standards

- Run `./vendor/bin/phpcs --standard=Symfony3Custom --extensions=php src/ tests/`

  Refer [this](https://packagist.org/packages/endouble/symfony3-custom-coding-standard) for help with any issues.
