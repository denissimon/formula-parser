Formula Parser
==============

[![Latest Stable Version](https://poser.pugx.org/denissimon/formula-parser/v/stable.svg)](https://packagist.org/packages/denissimon/formula-parser)
[![Total Downloads](https://poser.pugx.org/denissimon/formula-parser/downloads)](https://packagist.org/packages/denissimon/formula-parser)
[![License](https://poser.pugx.org/denissimon/formula-parser/license.svg)](https://github.com/denissimon/formula-parser/blob/master/LICENSE)

Formula Parser is a library for parsing and evaluating mathematical formulas given as strings.

Supports:

* Operators: +, -, *, /, ^
* Variables: x, y, z, a, b
* Numbers with decimal point '.'
* Numbers in E notation
* Constants: pi, e, Inf
* Functions: sqrt, abs, sin, cos, tan, log, exp
* Unlimited nested parentheses
* NaN (Not a Number)

Installation
------------

Requires [PHP 5.4 or higher](http://php.net).

To install with [Composer](https://getcomposer.org):

``` sh
composer require denissimon/formula-parser
```

Usage
-----

``` php
require_once __DIR__ . '/vendor/autoload.php';

use FormulaParser\FormulaParser;

$formula = '3*x^2 - 4*y + 3/y';
$precision = 2; // Number of digits after the decimal point

try {
    $parser = new FormulaParser($formula, $precision);
    $parser->setVariables(['x' => -4, 'y' => 8]);
    $result = $parser->getResult(); // [0 => 'done', 1 => 16.38]
} catch (\Exception $e) {
    echo $e->getMessage(), "\n";
}
```

The `$precision` parameter has a default of 4, and it's not required to specify:

``` php
$parser = new FormulaParser('3+4*2/(1-5)^8');
$result = $parser->getResult(); // [0 => 'done', 1 => 3.0001]
```

The initialized object `$parser` has the following methods:

`setValidVariables($array)` Overwrites default valid variables.

`setVariables($array)` Sets variables.

`getResult()` Returns an array [0 => v1, 1 => v2], where v1 is 'done' or 'error', and v2 is a computed result or validation error message, respectively.

`getFormula()` Returns the text of the formula passed to the constructor.

More usage examples can be found in `tests/FormulaParserTest.php`

License
-------

Licensed under the [MIT license](https://github.com/denissimon/formula-parser/blob/master/LICENSE)
