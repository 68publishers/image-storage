# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-10-28

### Changed

- Minimal PHP version is 8.3 (BC break).
- Required `68publishers/file-storage` `^2.0` (BC break, see its changelog).
- Added support for `symfony/console` 7 and 8, `nette/application` 3.2 and 3.3, `latte/latte` 3.1 and `nette/schema` 1.4.
- `symfony/console` older than 5.4 is no longer supported (conflict `<5.4`, BC break).
- The command `image-storage:lambda:dump-config` is named by the `#[AsCommand]` attribute instead of the static property `$defaultName`.
- Updated dev dependencies: PHPStan 2, `phpstan/phpstan-nette` 2, `nette/tester` 2.6+, `mockery/mockery` 1.6.12+, PHP-CS-Fixer 3.89+ (the core rule `multiline_promoted_properties` replaces the deprecated custom fixer).
- Tests no longer hide deprecations.
- GitHub Actions run the tests on PHP 8.3, 8.4 and 8.5 with the highest dependencies and on PHP 8.3 with the lowest dependencies, the Docker dev environment provides PHP 8.3, 8.4 and 8.5.

### Fixed

- The command `image-storage:lambda:dump-config` had an empty name with `symfony/console` 7+ (the static property `$defaultName` is no longer read), the console application failed to start.
- `LocalImageServer` stripped the base path also from paths that only start with the same characters (e.g. the base path `images` from `images2/...`). Leading and trailing slashes of the base path are ignored, the same as in `ImageServerRoute`.
- `LocalImageServer` failed with an "Array to string conversion" error (HTTP 500) when the signature query parameter was an array, an invalid signature (HTTP 403) is returned now, also for paths that need no signature.
- `MountManager::getAdapter()` without a prefix used `null` as an array key (deprecated in PHP 8.5), the exception about the missing adapter is thrown directly.

## [1.8.2] - 2026-06-24

### Added

- Added method `ResourceInterface::getEncodedImage()`.

### Changed

- Method `ImagePersister::save()` now uses the `ResourceInterface::getEncodedImage()` during saving.

## [1.8.1] - 2026-03-04

### Changed

- Updated HTTP headers when a resource is created using URL.

## [1.8.0] - 2026-02-23

### Added

- Added config option `disable_signature_on_known_modifiers`. In this mode the signature parameter is omitted when image url contains modifiers that are defined by any preset.
- Added descriptors support for presets.

### Changed

- SrcSets can be now generated without a `DescriptorInterface` argument if a preset with a descriptor is used for the path/file info.

## [1.7.0] - 2026-02-20

### Added

- Added argument `$absolute = true` to `LinkGeneratorInterface` and `FileInfoInterface`.

## [1.6.0] - 2026-01-07

### Added

- Image metadata stripping during image modification (ICC profiles are preserved).

## [1.5.0] - 2025-05-29

### Added

- Added compatibility with PHP 8.4.

## [1.4.0] - 2025-05-13

### Added

- Added method `ResourceFactoryInterface::createResourceFromPsrStream()` - support for PSR-7 streams.
- Added stream context option `follow_location: true` when resource is created from an url.
- Added tests.

### Changed

- Changed required version of `68publishers/file-storage` to `^1.2.0`.

## [1.3.1] - 2025-03-26

### Changed

- Error responses with a status code less than 500 are now not logged in `ImageServerPresenter`.

### Fixed

- [DX] Docker image for PHP 8.3.
- Fixed missing `TestLogger` class in tests.

## [1.3.0] - 2024-07-27

### Added

- [DX] Added Docker image for PHP 8.3.

### Changed

- Improved image persistence - if the source image has not really been modified, the original is used, thus avoiding the increase in filesize.
- Methods `LinkGeneratorInterface::srcSet()` and `ImageStorageInterface::srcSet()` now returns an object of type `SrcSet` instead of a string.

### Fixed

- [DX] Fixed Imagick installations in Dockerfile.

## [1.2.1] - 2024-03-20

### Changed

- An image is now downloaded as a tmp file when the method `ResourceFactory::createResourceFromFile()` is called with a url. Thanks to that a file size can be detected.

## [1.2.0] - 2024-03-12

### Changed

- Changed minimal version of `68publishers/file-storage` to `^1.1.1`.
- The original link is created when path info does not have any modifiers instead of throwing exception.
- Updated PHP-Cs-Fixer configuration.

## [1.1.0] - 2023-08-09

### Added

- Added support for AVIF format.
- Added support for animated WebP.

## [1.0.2] - 2023-01-05

### Fixed

- Fixed error `Cannot set a default value when using InputOption::VALUE_NONE mode.` in `CleanCommandConfigurator`.

## [1.0.1] - 2023-01-05

### Fixed

- Fixed route registration in the `ImageStorageExtension` (the option `route`).
- Fixed test `ImagePersisterTest`.

## [1.0.0] - 2023-01-03

### Added

- Added support for PHP `^8.1` (PHP `7` not supported).
- Added support for Latte `^3.0` (Latte `2` not supported).
- ImageServer presenter and routes are now automatically registered and configured in the application if the option `route: yes` is defined in the configuration for a storage.
- [DX] Added Dockerfile and docker-compose.yml with containers for all supported PHP versions, tests, Makefile, GitHub actions and PHPStan (level 8).

### Changed

- Integrated with `68publishers/file-storage` in the version `^1.0.0`.
- Added strict types for all properties and return types for all methods.
- Improved coding style.
- Updated README.

### Removed

- Removed class `AbstractImageServerPresenter`.

## [0.5.8] - 2022-03-20

### Added

- Added Latte function `no_image()`.

## [0.5.7] - 2021-11-04

### Changed

- Improved processing of animated GIFs.

## [0.5.6] - 2021-11-02

### Fixed

- Fixed presets usage.

## [0.5.5] - 2021-10-27

### Changed

- Method `ImageStorage::createPathInfo()` now returns NoImage if the argument is `null` or empty string.

## [0.5.4] - 2021-10-11

### Changed

- All stack options in the extension `ImageStorageLambdaExtension` can be now dynamic statements.

## [0.5.3] - 2021-10-05

### Changed

- The option `driver` can be dynamic.

## [0.5.2] - 2021-08-16

### Added

- Support for animated GIFs.

## [0.5.1] - 2021-06-20

### Fixed

- Fixed generation of `srcset` attribute for PathInfo instances without default modifiers.

## [0.5] - 2021-01-29

### Changed

- Integration with [68publishers/file-storage](https://github.com/68publishers/file-storage).
- A lot of changes, no backward compatibility.
- Added full README.

## [0.4.10] - 2020-10-31

### Added

- Added config option `cache_max_age`.

### Changed

- Headers `Cache-Control` and `Expires` are defined by the config option.
- Options `VersionParameterName` and `CacheMaxAge` are exported to the AWS config.

## [0.4.9] - 2020-10-26

### Changed

- An image's type is now resolved from a `type` attribute (e.g. `type="image/webp"`) for non pair macros `{src}` and `{srcset}`.

## [0.4.8] - 2020-10-23

### Fixed

- The default extension is used if an ImageInfo doesn't contain an extension in `ImageStorageLatteFacade`.

## [0.4.7] - 2020-10-13

### Fixed

- Macros fixes and improvements.

## [0.4.6] - 2020-05-15

### Fixed

- An exception `FileExistsException` is thrown if a source image is processed in method `DefaultImagePersister::persistResource()`.

## [0.4.5] - 2020-05-15

### Added

- Added macro `n:picture`.
- Updated README (section about Latte macro usages has been added).

### Deprecated

- Macros `{img}` and `n:img` are deprecated, use `{src}` and `n:src` instead.

## [0.4.4] - 2020-04-25

### Added

- Added a modifier `f` - fit (`fill`, `stretch`, `contain`, `crop-*`).

## [0.4.3] - 2020-04-24

### Added

- Added a modifier `ar` - aspect ratio.

## [0.4.2] - 2020-04-20

### Fixed

- Filesystem adapters were registered into the DI container as services but never used.

## [0.4.1] - 2020-04-20

### Changed

- Updated README.

## [0.4] - 2020-04-19

### Added

- Added a method `IImageStorage::getConfig()`.
- Added support for [image-storage-lambda](https://github.com/68publishers/image-storage-lambda).

### Changed

- BC break: the object `Env` has been renamed to `Config`, the option `env` has been renamed to `config` and the `config` options are now lowercase.
- BC break: changed a signature configuration.
- BC break: the option `no_image_rules` has been renamed to `no_image_patterns`.

## [0.3.1] - 2020-03-26

### Changed

- An error response is in JSON format instead of a plain text.
- A 404 response is returned when an image is not found and a no-image is not defined.

### Fixed

- Small fixes in file paths and extensions.

## [0.3] - 2020-03-11

### Added

- WebP and progressive JPEG support.
- Modifiers can be now defined without values like tags (if a modifier allows it). The only modifier that allows it is the `original` for now.

### Changed

- Source and cached images are in separated directories. The original (source) images are now saved without a file extension.
- An image format is parsed from a file extension (defined in URL path).

### Removed

- BC break: removed modifier `pf`.
- BC break: removed method `ImageInfo::isNoImage()`.

## [0.2.3] - 2020-02-26

### Added

- Added option `storages.{name}.signature` - requests on an ImageServer can be verified with signature parameter in URL.
- Added an ENV property `SIGNATURE_PARAMETER_NAME`.

### Changed

- Changed a default value for an ENV property `VERSION_PARAMETER_NAME` to `_v` (NULL before).

## [0.2.2] - 2020-02-04

### Changed

- Improved a coding style.

### Fixed

- Fixed double slashes in image links.

## [0.2.1] - 2019-12-01

### Changed

- Improved quality options.

## [0.2] - 2019-10-29

### Added

- Srcset attributes can be generated with different descriptors `x` or `w` for each image.

### Changed

- Git files `.gitignore` and `.gitkeep` are kept when a storage is cleaned.
- Improved coding style.

### Fixed

- Removed double slashes from image links.

## [0.1.2] - 2019-09-16

### Added

- Added setting `Env::HOST` for link generator.

## [0.1.1] - 2019-09-02

### Fixed

- Fixed command name.

## [0.1] - 2019-06-09

### Added

- First release (Nette `^2.4`).

[Unreleased]: https://github.com/68publishers/image-storage/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/68publishers/image-storage/compare/v1.8.2...v2.0.0
[1.8.2]: https://github.com/68publishers/image-storage/compare/v1.8.1...v1.8.2
[1.8.1]: https://github.com/68publishers/image-storage/compare/v1.8.0...v1.8.1
[1.8.0]: https://github.com/68publishers/image-storage/compare/v1.7.0...v1.8.0
[1.7.0]: https://github.com/68publishers/image-storage/compare/v1.6.0...v1.7.0
[1.6.0]: https://github.com/68publishers/image-storage/compare/v1.5.0...v1.6.0
[1.5.0]: https://github.com/68publishers/image-storage/compare/v1.4.0...v1.5.0
[1.4.0]: https://github.com/68publishers/image-storage/compare/v1.3.1...v1.4.0
[1.3.1]: https://github.com/68publishers/image-storage/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/68publishers/image-storage/compare/v1.2.1...v1.3.0
[1.2.1]: https://github.com/68publishers/image-storage/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/68publishers/image-storage/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/68publishers/image-storage/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/68publishers/image-storage/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/68publishers/image-storage/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/68publishers/image-storage/compare/v0.5.8...v1.0.0
[0.5.8]: https://github.com/68publishers/image-storage/compare/v0.5.7...v0.5.8
[0.5.7]: https://github.com/68publishers/image-storage/compare/v0.5.6...v0.5.7
[0.5.6]: https://github.com/68publishers/image-storage/compare/v0.5.5...v0.5.6
[0.5.5]: https://github.com/68publishers/image-storage/compare/v0.5.4...v0.5.5
[0.5.4]: https://github.com/68publishers/image-storage/compare/v0.5.3...v0.5.4
[0.5.3]: https://github.com/68publishers/image-storage/compare/v0.5.2...v0.5.3
[0.5.2]: https://github.com/68publishers/image-storage/compare/v0.5.1...v0.5.2
[0.5.1]: https://github.com/68publishers/image-storage/compare/v0.5...v0.5.1
[0.5]: https://github.com/68publishers/image-storage/compare/v0.4.10...v0.5
[0.4.10]: https://github.com/68publishers/image-storage/compare/v0.4.9...v0.4.10
[0.4.9]: https://github.com/68publishers/image-storage/compare/v0.4.8...v0.4.9
[0.4.8]: https://github.com/68publishers/image-storage/compare/v0.4.7...v0.4.8
[0.4.7]: https://github.com/68publishers/image-storage/compare/v0.4.6...v0.4.7
[0.4.6]: https://github.com/68publishers/image-storage/compare/v0.4.5...v0.4.6
[0.4.5]: https://github.com/68publishers/image-storage/compare/v0.4.4...v0.4.5
[0.4.4]: https://github.com/68publishers/image-storage/compare/v0.4.3...v0.4.4
[0.4.3]: https://github.com/68publishers/image-storage/compare/v0.4.2...v0.4.3
[0.4.2]: https://github.com/68publishers/image-storage/compare/v0.4.1...v0.4.2
[0.4.1]: https://github.com/68publishers/image-storage/compare/v0.4...v0.4.1
[0.4]: https://github.com/68publishers/image-storage/compare/v0.3.1...v0.4
[0.3.1]: https://github.com/68publishers/image-storage/compare/v0.3...v0.3.1
[0.3]: https://github.com/68publishers/image-storage/compare/v0.2.3...v0.3
[0.2.3]: https://github.com/68publishers/image-storage/compare/v0.2.2...v0.2.3
[0.2.2]: https://github.com/68publishers/image-storage/compare/v0.2.1...v0.2.2
[0.2.1]: https://github.com/68publishers/image-storage/compare/v0.2...v0.2.1
[0.2]: https://github.com/68publishers/image-storage/compare/v0.1.2...v0.2
[0.1.2]: https://github.com/68publishers/image-storage/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/68publishers/image-storage/compare/v0.1...v0.1.1
[0.1]: https://github.com/68publishers/image-storage/releases/tag/v0.1
