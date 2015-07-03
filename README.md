Formula Parser
==============

Parsing and evaluating mathematical formula entered as a string.

Supported:
* Operators +, -, *, /, ^
* Numbers with decimal point '.'
* Constants: pi
* Unlimited nested parentheses
* Build-in validation and multilingual responses

Formula Parser uses Float for calculation and result.

Setup and Usage
---------------

Simply include this class into your project like so:

`include_once('/libraries/FormulaParser.php');`

Then invoke the class in your project using the class constructor:

`$formula = new FormulaParser($input_string, $language, $precision_rounding);`

`$input_string` The formula entered as a string

`$language` Setting the language ('en', 'ru' or 'es')

`$precision_rounding` Setting the maximum number of digits after the decimal point in a calculated answer


The initialized object `$formula` has two public methods:

`getResult()` Returns an array(0=>value1, 1=>value2), where value1 is the operating status 'done' or 'error', and value2 is a calculated answer or error message in the set language.

`getFormula()`  Returns the text of the formula passed to the constructor

Example
-------

The following example shows how easy this class is to use. For instance, user's formula is: ((8+(10*(3+5)))/2.1)-5^2

``` php
$formula = new FormulaParser('((8+(10*(3+5)))/2.1)-5^2', 'en', 4);
$result = $formula->getResult(); // will return array(0=>'done', 1=>16.9048)
if ($result[0]=='done') {
  echo "Answer: $result[1]";
} elseif ($result[0]=='error') {
  echo "Error: $result[1]";
}
```

More examples and a live demo can be found on [www.yoursimpleformula.com](http://www.yoursimpleformula.com) - the web application made using Formula Parser.

###License

This software is licensed under the [MIT license](https://github.com/denissimon/formula-parser/blob/master/LICENSE)
