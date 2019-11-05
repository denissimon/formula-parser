## v2.7.1

* Fixed PHP message "strstr(): Empty needle" in some cases

## v2.7.0

* Optimized the algorithm
* Fixed parsing variables, e.g. 3 * 6x => Syntax error
* Updated: unit tests

## v2.6.1

* Fixed PHP message "strstr(): Non-string needles will be interpreted as strings in the future." in some cases

## v2.6.0

* Refactored: code to PHP 7.3.5
* Updated: composer.json
* Updated: readme

## v2.5.0

* Improved the algorithm: it gets better with this release!
* Added: ability to override default valid variables, which will allow to set variables not only with the names x, y, z, a, b.
* Added: History.md
* Updated: readme

## v2.4.0

* Improved: now several consecutive operators are available, e.g. 10/--++2
* Improved: a decimal number can be given as 1. or .1 which is equal to 1.0 or 0.1
* Added: constant Inf
* Added: processing of NaN
* Added: unit tests
* Fixed: PHP message "Notice: Undefined offset" in some cases
* Refactored: class properties and names of several methods
* Refactored: short syntax to arrays
* Updated: composer.json
* Updated: PHPDoc
* Updated: readme

## v2.3.0

* Added: variables x, y, z, a, b
* Added: constant e
* Added: functions log, exp
* Added: namespace
* Added: composer.json
* Updated: PHPDoc
* Updated: readme

## v2.2.0

* Added: functions sqrt, abs, sin, cos, tan
* Refactored: class properties
* Fixed: PHPDoc
* Updated: readme

## v2.1.0

* Improved the algorithm: it gets better with this release!
* Improved: now spaces are allowed, like so: 8 + ( 10 * ( 3 + 5 ) ) / 2
* Added: numbers in E notation
* Added: constant pi
* Added: new validation rules
