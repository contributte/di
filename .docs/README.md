# Dependency Injection (DI)

## Content

- [AutoloaderExtension - autoload classes by definition](#autoloaderextension)

## AutoloaderExtension

At first, you have to register extension.

```yaml
extensions:
    autoloader: Contributte\DI\Autoload\DI\AutoloaderExtension
```

### Default configuration

This configuration is enabled by default.

```yaml
autoloader:
    dirs:
        - %appDir%

    annotations:
        - @Service
        
    interfaces:
        - Contributte\DI\Autoload\AutoloadService

    decorator:
        inject: off
```

It means, `autoloader` will be looking for all `*.php` classes in folders (`%appDir%`) which: 
- implements `Contributte\DI\Autoload\AutoloadService` (OR)
- has the annotation `@Service` (OR)

### Custom configuration

You can override all configuration settings you want to.

```yaml
autoloader:
    dirs:
        - %appDir%
        - %libsDir%
        - %fooDir%

    annotations:
        - @Service
        - @MyCustomService
        
    interfaces:
        - Minetro\Autoloader\AutoloadService
        - App\Model\MyAutoloadServiceInterface

    decorator:
        inject: on / off
```

### Performance

Service loading is triggered only once at dependency injection container compile-time. You should be pretty fast, 
almost as [official registering presenter as services](https://api.nette.org/2.4/source-Bridges.ApplicationDI.ApplicationExtension.php.html#121-160).
