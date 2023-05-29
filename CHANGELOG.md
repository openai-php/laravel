# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## v0.5.2 (2023-05-29)
### Fixed
- Removed unnecessary dependency for `http-interop/http-factory-guzzle` from composer.json which leads to an error with Laravel 10 - ([#64](https://github.com/openai-php/laravel/issues/46))

## v0.5.1 (2023-05-24)
### Changed
- Changed underlying `openai/client` package version from 0.5.0 to 0.5.1

## v0.5.0 (2023-05-24)
### Changed
- Changed underlying `openai/client` package version from 0.4.2 to 0.5.0

## v0.4.3 (2023-04-13)
### Fixed
- Undefined methods on facade due missing provides ([8d6d3cb](https://github.com/openai-php/laravel/commit/8d6d3cbd8ad1aea121dd294dddfe81569c2c1dd9))

## v0.4.2 (2023-04-12)
### Added
- Facade `fake()` / Testing support ([#27](https://github.com/openai-php/laravel/pull/27))

### Changed
- ServiceProvider: run `publishes` only when running in console ([#29](https://github.com/openai-php/laravel/pull/29))
- Make ServiceProvider deferred ([#30](https://github.com/openai-php/laravel/pull/30))
- Changed underlying `openai/client` package version from 0.4.1 to 0.4.2

## v0.4.1 (2023-03-24)
### Changed
- Changed underlying `openai/client` package version from 0.4.0 to 0.4.1

## v0.4.0 (2023-03-17)
### Changed
- Changed underlying `openai/client` package version from 0.3.4 to 0.4.0

## v0.3.4 (2023-03-03)
### Changed
- Changed underlying `openai/client` package version from 0.3.3 to 0.3.4

## v0.3.3 (2023-03-02)
### Changed
- Changed underlying `openai/client` package version from 0.3.2 to 0.3.3

## v0.3.2 (2023-02-28)
### Changed
- Changed underlying `openai/client` package version from 0.3.0 to 0.3.2

## v0.3.1 (2023-01-27)
### Added
- Support for Laravel 10 ([#8](https://github.com/openai-php/laravel/pull/8))

## v0.3.0 (2023-01-03)
### Changed
- Changed underlying `openai/client` package version from 0.2.x to 0.3.x ([4f785ef](https://github.com/openai-php/laravel/commit/4f785ef21c6c8f68d3380b3d7178689c938c1235))

## v0.2.1 (2022-12-27)
### Fixed
- Typo on configuration file ([#3](https://github.com/openai-php/laravel/pull/3))

## v0.2.0 (2022-12-26)
### Added
- First version
