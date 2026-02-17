# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- **BREAKING**: Minimum PHP version requirement raised from 7.x to 8.3
- Upgraded to PHP 8.4 for development and testing
- Upgraded PHPUnit from 8.x/9.x to 10.5
- Migrated from fzaninotto/faker to fakerphp/faker

### Added
- PHP CodeSniffer (PHPCS) for PSR-12 code style enforcement
- Psalm static analysis tool for improved type safety
- `Connection::resetInstance()` method for better test isolation
- PHPUnit 10 configuration format

### Fixed
- Applied PSR-12 code style fixes across entire codebase
- Fixed various type and logic issues identified by Psalm static analysis
- Updated tests for PHPUnit 10 compatibility

### Migration Guide from PHP 7.x to 8.3+

If you're upgrading from an older version of bb-orm that ran on PHP 7.x:

1. **PHP Version**: Ensure your environment runs PHP 8.3 or higher
2. **Dependencies**: Run `composer update` to get compatible dependencies
3. **Code Changes**: Review your code for PHP 7.x specific patterns that may need updating:
   - Check for deprecated features removed in PHP 8.x
   - Update type hints to use union types where applicable
   - Review null handling with the nullsafe operator (`?->`)
4. **Testing**: Run your test suite to ensure compatibility

For more information on PHP 8 migration, see:
- [PHP 8.0 Migration Guide](https://www.php.net/manual/en/migration80.php)
- [PHP 8.1 Migration Guide](https://www.php.net/manual/en/migration81.php)
- [PHP 8.2 Migration Guide](https://www.php.net/manual/en/migration82.php)
- [PHP 8.3 Migration Guide](https://www.php.net/manual/en/migration83.php)
