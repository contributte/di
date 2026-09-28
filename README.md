![](https://heatbadger.now.sh/github/readme/contributte/di/)

<p align=center>
  <a href="https://github.com/contributte/di/actions"><img src="https://badgen.net/github/checks/contributte/di/master?cache=300"></a>
  <a href="https://codecov.io/gh/contributte/di"><img src="https://badgen.net/codecov/c/github/contributte/di"></a>
  <a href="https://packagist.org/packages/contributte/di"><img src="https://badgen.net/packagist/dm/contributte/di"></a>
  <a href="https://packagist.org/packages/contributte/di"><img src="https://badgen.net/packagist/v/contributte/di"></a>
</p>
<p align=center>
  <a href="https://packagist.org/packages/contributte/di"><img src="https://badgen.net/packagist/php/contributte/di"></a>
  <a href="https://github.com/contributte/di"><img src="https://badgen.net/github/license/contributte/di"></a>
  <a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
  <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
  <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

Contributte DI is a set of DI extensions and helpers for Nette Framework that `nette/di` doesn't ship. It
registers every class of a namespace as a service, injects the container into `IContainerAware` services, splits
big extensions into compiler passes and decorates services from code.

## Usage

To install the latest version of `contributte/di`, use [Composer](https://getcomposer.org):

```bash
composer require contributte/di
```

Requires PHP 8.2 or later and Nette 3.2 or later. `ResourceExtension` also needs `nette/robot-loader`.

Register `ResourceExtension` in your `config.neon` and point it to a namespace. Every non-abstract class found in
the folder becomes a service, unless the container already has a service of that type:

```neon
extensions:
	autoload: Contributte\DI\Extension\ResourceExtension

autoload:
	resources:
		App\Model\Services\:
			paths: [%appDir%/model/services]
```

## Documentation

For details on how to use this package, check out the [documentation](.docs).

## Versions

| State       | Version | Branch   | Nette | PHP     |
|-------------|---------|----------|-------|---------|
| dev         | `^0.7`  | `master` | 3.2+  | `>=8.2` |
| stable      | `^0.6`  | `master` | 3.2+  | `>=8.2` |

## Development

See [how to contribute](https://contributte.org/contributing.html) to this package.

This package is currently maintained by these authors.

<a href="https://github.com/f3l1x">
  <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=80">
</a>

-----

Consider [supporting](https://contributte.org/partners.html) the **contributte** development team.
Thank you for using this package.
