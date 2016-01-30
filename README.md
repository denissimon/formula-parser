Formula Parser
==============

Formula Parser is a library for parsing and evaluating mathematical formulas given as strings.

Supports:

* Operators: +, -, *, /, ^
* Variables: x, y, z, a, b
* Numbers with decimal point '.'
* Numbers in E notation
* Constants: pi, e
* Functions: sqrt, abs, sin, cos, tan, log, exp
* Unlimited nested parentheses

[See it in action](http://www.yoursimpleformula.com).

Usage
---------------

After obtaining and including the class into your project, invoke it using the class constructor:

``` php
use FormulaParser\FormulaParser;

$formula = new FormulaParser($input_string, $language, $precision_rounding);
```

`$input_string` The text of the formula

`$language` Setting the language ('en', 'ru' or 'es')

`$precision_rounding` Setting the maximum number of digits after the decimal point

The initialized object `$formula` has the following methods:

`setVariables()` Sets variables.

`getResult()` Returns an array [0=>value1, 1=>value2], where value1 is 'done' or 'error', and value2 is a computed result or error message in the set language.

The error message is issued if a validation error is detected.

`getFormula()` Returns the text of the formula passed to the constructor.

Examples
--------

**Example #1**. Formula: `(8+(10*(3-5)^2))/4.8`

``` php
$formula = new FormulaParser('(8+(10*(3-5)^2))/4.8', 'en', 4);
$result = $formula->getResult(); // [0=>'done', 1=>10]
```

**Example #2**. Formula: `sqrt(x^y) / log(exp(pi))`, and x = 4, y = 8.

``` php
$formula = new FormulaParser('sqrt(x^y) / log(exp(pi))', 'en', 4);
$formula->setVariables(['x'=>4, 'y'=>8]);
$result = $formula->getResult(); // [0=>'done', 1=>81.4873]
```

License
-------

Licensed under the [MIT license](https://github.com/denissimon/formula-parser/blob/master/LICENSE)
