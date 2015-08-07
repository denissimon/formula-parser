Formula Parser
==============

Parsing and evaluating mathematical formula entered as a string.

Supported:

* Operators: +, -, *, /, ^
* Variables: x, y, z, a, b
* Numbers with decimal point '.'
* Constants: pi, e
* Functions: sqrt, abs, sin, cos, tan, log, exp
* Unlimited nested parentheses
* Build-in validation and multilingual responses

Usage
---------------

After obtaining and including the class into your project, invoke it using the class constructor:

``` php
use FormulaParser\FormulaParser;

$formula = new FormulaParser($input_string, $language, $precision_rounding);
```

`$input_string` The text of the formula

`$language` Setting the language ('en', 'ru' or 'es')

`$precision_rounding` Setting the maximum number of digits after the decimal point in a calculated answer


The initialized object `$formula` has three methods:

`setVariables()` Sets variables.

`getResult()` Returns an array(0=>value1, 1=>value2), where value1 is the operating status 'done' or 'error', and value2 is a calculated answer or error message in the set language.

`getFormula()` Returns the text of the formula passed to the constructor.

### Constants

|name|description|
|----|-----------|
|pi|the ratio of the circumference of a circle to its diameter, approx. = 3.141593
|e|the base of the natural logarithm, approx. = 2.718282|

### Functions

|name|description|
|----|-----------|
|abs(n)|absolute value of _n_
|sqrt(n)|square root of _n_
|sin(n)|sine of _n_ radians
|cos(n)|cosine of _n_ radians
|tan(n)|tangent of _n_ radians
|log(n)|natural logarithm of _n_
|exp(n)|exponential value of _n_|


Examples
--------

**Example #1**. Formula: `(8+(10*(3-5)^2))/4.8`

``` php
$formula = new FormulaParser('(8+(10*(3-5)^2))/4.8', 'en', 4);
$result = $formula->getResult(); // array(0=>'done', 1=>10)
```

**Example #2**. Formula: `sqrt(x^y) / log(exp(pi))`, and x = 4, y = 8.

``` php
$formula = new FormulaParser('sqrt(x^y) / log(exp(pi))', 'en', 4);
$formula->setVariables(array('x'=>4, 'y'=>8));
$result = $formula->getResult(); // array(0=>'done', 1=>81.4873)
```

Outputting the result:

``` php
if ($result[0]=='done') {
  echo "Answer: $result[1]";
} elseif ($result[0]=='error') {
  echo "Error: $result[1]";
}
```

More examples and a live demo can be found on [www.yoursimpleformula.com](http://www.yoursimpleformula.com).

License
-------

This software is licensed under the [MIT license](https://github.com/denissimon/formula-parser/blob/master/LICENSE)
