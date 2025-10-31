# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## v0.18.0 (2025-10-31)
### Changed
- Changed underlying `openai/client` package version from 0.17.0 to 0.18.0

## v0.17.1 (2025-10-02)
### Fixed
- Removed hard-coded `OpenAI-Beta` header which broke Conversation API. ([#175](https://github.com/openai-php/laravel/pull/175))

## v0.17.0 (2025-10-02)
### Added
- Add facade for `conversations`. ([#173](https://github.com/openai-php/laravel/pull/173))

### Changed
- Changed underlying `openai/client` package version from 0.16.0 to 0.17.0

## v0.16.0 (2025-08-26)
### Changed
- Changed underlying `openai/client` package version from 0.15.0 to 0.16.0
- HTTP 429 now throw a `RateLimitException` instead of a generic `ErrorException`

## v0.15.0 (2025-08-04)
### Added
- Add facade for `containers`. ([#161](https://github.com/openai-php/laravel/pull/161))

### Changed
- Changed underlying `openai/client` package version from 0.14.0 to 0.15.0

## v0.14.0 (2025-06-24)
### Added
- Add facade for `realtime`. ([#156](https://github.com/openai-php/laravel/pull/156))

### Changed
- Changed underlying `openai/client` package version from 0.13.0 to 0.14.0

## v0.13.0 (2025-05-14)
### Added
- Add facade for `responses` ([#147](https://github.com/openai-php/laravel/pull/147))

### Changed
- Changed underlying `openai/client` package version from 0.12.0 to 0.13.0
- Restored Laravel 11 support.

## v0.12.0 (2025-05-04)
### Added
- Add facade for `fineTuning` ([#125](https://github.com/openai-php/laravel/pull/125))
- Make base url configurable ([#144](https://github.com/openai-php/laravel/pull/144))

### Changed
- Changed underlying `openai/client` package version from 0.11.0 to 0.12.0

### Removed
- Removed support for PHP 8.1

## v0.11.0 (2025-02-24)
### Added
- Add support for Laravel 12 ([#137](https://github.com/openai-php/laravel/pull/137))

## v0.10.1 (2024-06-06)
### Changed
- Changed underlying `openai/client` package version from 0.9.1 to v0.10.1

## v0.10.0-beta.2 (2024-05-28)
### Fixed
- Api version header in Service Provider - ([#100](https://github.com/openai-php/laravel/issues/100))

## v0.10.0-beta.1 (2024-05-27)
### Changed
- Changed underlying `openai/client` package version from 0.9.1 to v0.10.0-beta.1

## v0.9.1 (2024-05-25)
### Changed
- Changed underlying `openai/client` package version from 0.9.0 to v0.9.1

## v0.9.0 (2024-05-21)
### Changed
- Changed underlying `openai/client` package version from 0.8.0 to v0.9.0

## v0.8.0 (2023-11-23)
### Changed
- Changed underlying `openai/client` package version from 0.7.8 to v0.8.0

## v0.8.0-beta.1 (2023-11-13)
### Changed
- Changed underlying `openai/client` package version from 0.7.8 to v0.8.0-beta.1

## v0.7.8 (2023-11-07)
### Changed
- Changed underlying `openai/client` package version from 0.7.5 to 0.7.8

## v0.7.5 (2023-11-06)
### Changed
- Changed underlying `openai/client` package version from 0.7.0 to 0.7.5

## v0.7.0 (2023-08-29)
### Changed
- Changed underlying `openai/client` package version from 0.6.3 to 0.7.0
- Update configuration documentation

## v0.6.3 (2023-07-10)
### Changed
- Changed underlying `openai/client` package version from 0.6.0 to 0.6.3

## v0.6.0 (2023-06-14)
### Added
- Make HTTP request timeout configurable ([#45](https://github.com/openai-php/laravel/pull/45))

### Changed
- Changed underlying `openai/client` package version from 0.5.3 to 0.6.0

## v0.5.3 (2023-06-07)
### Changed
- Changed underlying `openai/client` package version from 0.5.1 to 0.5.3

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
