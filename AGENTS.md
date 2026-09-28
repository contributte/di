# Contributte DI

Instructions for AI coding agents working in this repository.

## Overview

`contributte/di` adds extra DI extensions and helpers on top of `nette/di`: service autoloading by namespace
(`ResourceExtension`), container injection (`ContainerAwareExtension`), `@value` property injection
(`InjectValueExtension`), compiler passes (`PassCompilerExtension`), a callback extension for tests
(`MutableExtension`) and a programmatic `Decorator`. It is a toolbox for other extensions and applications, not a
single extension with its own configuration section.

- **PHP**: 8.2 to 8.5 (`>=8.2` in `composer.json`)
- **Package**: `contributte/di`, namespace `Contributte\DI\`
- **Extensions**: `Contributte\DI\Extension\ResourceExtension`, `ContainerAwareExtension`, `InjectValueExtension`,
  `MutableExtension`; base classes `CompilerExtension` and `PassCompilerExtension`
- **Integrates**: `nette/di` 3.1+, `nette/utils` 3.2.8+ or 4.x; `nette/robot-loader` only in `require-dev`

## Documentation

- `.docs/README.md` is the user documentation and the page on contributte.org. Update it in the same pull request
  when configuration or behaviour changes.
- Organization rules for code, tests and tooling are in
  [contributte/contributte specs](https://github.com/contributte/contributte/tree/master/specs).

## Commands

```bash
# Install dependencies
make install

# Run all checks (PHPStan level 9 + code style), does not run tests
make qa

# Fix code style
make csf

# Run all tests, or one file
make tests
vendor/bin/tester -s -p php --colors 1 -C tests/Cases/Extension/ResourceExtension.phpt

# Generate code coverage (coverage.html)
make coverage
```

CI runs the tests on PHP 8.2 to 8.5 and once on PHP 8.2 with `--prefer-lowest`.

## Conventions

- Tests are Nette Tester `.phpt` files in `tests/Cases`, split into `Decorator/` and `Extension/`, with
  `Toolkit::test()`. Containers are compiled with `Nette\DI\ContainerLoader` into `Environment::getTestDir()`.
- Services scanned by `ResourceExtension` tests live in `tests/Fixtures/{Foo,Bar,Baz,Scalar}`. Adding a class
  there changes what the existing autoload tests find.
- `ResourceExtension`, `ContainerAwareExtension`, `InjectValueExtension` and `ExtensionDefinitionsHelper` are not
  `final`. They are public API that users may extend, so keep them open until a major release.

## Traps

- **Each `ContainerLoader::load()` key in a test file must be unique.** The loader caches the compiled class by
  key, so reusing `1` in the same file returns the first container. See the numbered keys in
  `ResourceExtension.phpt`.
- **`ResourceExtension` needs `nette/robot-loader`, which is not in `require`.** Without it `createRobotLoader()`
  throws `Nette\InvalidStateException`; keep that check instead of adding the package to `require`.
- **Resource keys must end with a backslash.** The check throws PHP's own `RuntimeException` with the message
  `must end with /`, and a test asserts that exact text.
- **`ResourceExtension` skips classes already registered by type.** Services are added in `beforeCompile()`, so
  the order of extensions matters; the docs tell users to register it first.
- **`Decorator::of()` takes a `ContainerBuilder` and an `ExtensionDefinitionsHelper`.** `ExtensionDefinitionsHelper`
  turns factory and locator definitions into their `ServiceDefinition`s; every extension here relies on it.
- **`InjectValueExtension` reads the `@value(...)` docblock annotation, not an attribute.** It only touches
  services tagged `inject.value` unless `all: true`, and it has no test in `tests/Cases`.
- **`TContainerAware` is used by no class in `src/`.** `phpstan.neon` ignores that error on purpose; don't remove
  the trait or the ignore rule.
- Usage, configuration and examples for users live in `.docs/README.md`, not here.
