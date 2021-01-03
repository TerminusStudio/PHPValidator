# Slim PHP Respect Validation

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-build]][link-build]
[![Scrutinizer][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]

A SlimPHP validator using respect validation package.

## Install

Via Composer

``` bash
$ composer require terminusstudio/phpvalidator
```

## Usage

### Initializing 

**Configuration**
``` php
$config = [
            'useSession' => false 
          ];
```
- `useSession` - If set to true, validation results are saved in a session variable that can be accessed in the next request (using the ValidatorMiddleware). Defaults to false.

**Using a container**
``` php
Container::set('validator', function() {
    return new \TS\PHPValidator\Validator($config);
});
```

**Without container**
``` php
$validator = new \TS\PHPValidator\Validator($config);
```

### Example Usage
``` php
public function login($request, $response) {
    if ($request->getMethod() == 'POST')
    {
        $v = Container::get('validator')->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::notEmpty()->length(8)->alnum('_'),

        ]);;

        if($v->isValid())
        {
            //Do Processing
        }
     }
    return View::render($response, 'login.twig');
}
```

#### Middleware
The ValidatorMiddleware can be added to slim if you set the useSession to true in $config. 

``` php
  public function postLogin($request, $response) {
        $v = Container::get('validator')->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::notEmpty()->length(8)->alnum('_'),
    
        ]);;
    
        if($v->isValid())
        {
            //Do Processing
        }
        
        return $response->withHeader('Location',  Router::getRouteParser()->urlFor('login.get'))->withStatus(302);;
    }
```

If there were any errors, the next request will have access to the errors and values. To enable the middleware just add `ValidatorMiddleware` class to the Slim app and pass the validator instance.

``` php
$app->add(new \TS\PHPValidator\ValidatorMiddleware(Container::get('validator')));
```

#### Twig Extension
This plugin also supports Twig functions. To enable just add `ValidatorTwig` when initializing twig. This requires `slim/twig-view` package.

``` php
Container::set('view', function() {
    $view = Twig::create(....);

    // Add the validator extension and pass the validator instance to it
    $view->addExtension(
        new \TS\PHPValidator\ValidatorTwig(Container::get('validator'))
    );

    return $view;
});
```

There are currently 5 functions supported by the extension,
            
- `has_errors()` - Returns true if there are any errors
- `has_error($key)` - Returns true if `$key` is invalid
- `get_errors()` - Returns an array containing all errors
- `get_error($key, $toString = true)` - Returns an array containing all errors for a specific `$key`. If `$toString` is set to true, then it returns a string.
- `get_value($key, $default = null)` - Returns the value for a specific `$key`.

*`$key` is the field name set in the request.*

``` php
{% if has_error('email') %}
    {{  get_error('email') }}
{% endif %}
<input type="email" name="email" value="{{ get_value('email') }}">
``` 

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/TerminusStudio/PHPValidator.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-build]: https://github.com/TerminusStudio/PHPValidator/workflows/CI/badge.svg
[ico-scrutinizer]: https://img.shields.io/scrutinizer/quality/g/TerminusStudio/PHPValidator
[ico-downloads]: https://img.shields.io/packagist/dt/TerminusStudio/PHPValidator.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/TerminusStudio/PHPValidator
[link-downloads]: https://packagist.org/packages/TerminusStudio/PHPValidator
[link-build]: https://github.com/TerminusStudio/PHPValidator/actions?query=workflow%3ACI
[link-scrutinizer]: https://scrutinizer-ci.com/g/TerminusStudio/PHPValidator/?branch=main
[link-author]: https://github.com/TerminusStudio
