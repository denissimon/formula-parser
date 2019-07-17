<?php
/**
 * Formula Parser - A library for parsing and evaluating mathematical formulas given as strings.
 *
 * @author   Denis Simon <denis.v.simon@gmail.com>
 *
 * @license  MIT (https://github.com/denissimon/formula-parser/blob/master/LICENSE)
 *
 * @version  2.6.1-2019.07.16
 */

namespace FormulaParser;

interface IFormulaParser
{
    public function setValidVariables(array $vars);
    
    public function setVariables(array $vars);

    public function getResult();

    public function getFormula();
}

class FormulaParser implements IFormulaParser
{    
    protected $formula, $original_formula = "";
    protected $precision = "";
    protected $expression = "";
    protected $correct = 1;
    protected $error_type = 0;
    protected $variables = [];
    protected $valid_variables = ['x', 'y', 'z', 'a', 'b'];
    protected $valid_functions = ['abs', 'sin', 'cos', 'tan', 'log', 'exp', 'sqrt'];

    /**
     * Constructor
     *
     * @param string  $formula   The formula given as a string
     * @param integer $precision The rounding precision (number of digits after the decimal point)
     */
    public function __construct($formula = "", $precision = 4)
    {
        if (!empty($formula))
            $this->formula = $this->original_formula = trim($formula);
        
        if (isset($precision))
            $this->precision = $precision;
    }

    /**
     * Magic overloading method
     *
     * @param string $name
     * @param array  $arguments
     *
     * @throws \Exception when the method doesn't exist
     */
    public function __call($name, $arguments)
    {
        throw new \Exception("No such method exists: $name (".implode(', ', $arguments).")");
    }

    /**
     * Overwrites default valid variables
     *
     * @param array $vars
     */
    public function setValidVariables(array $vars)
    {
        $this->valid_variables = $vars;
    }

    /**
     * Sets variables
     *
     * @param array $vars
     */
    public function setVariables(array $vars)
    {
        $this->variables = $vars;
    }

    /**
     * Returns the text of the formula passed to the constructor
     *
     * @return string
     */
    public function getFormula()
    {
        return $this->original_formula;
    }
    
    /**
     * @return boolean
     */
    private function validate() 
    { 
        $validate_str = count_chars(implode("", $this->valid_variables).
                implode("", $this->valid_functions), 3);
        if (preg_match('/[^0-9\*\+\-\/\^\.'.$validate_str.'EINF\s\(\)]/', $this->expression)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $v1
     * @param string $v2
     */
    private function checkInf(&$v1, &$v2 = '') {
        $v1 = ($v1==='INF') ? INF : (($v1==='-INF') ? -INF : $v1);
        $v2 = ($v2==='INF') ? INF : (($v2==='-INF') ? -INF : $v2);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function return_($value) {
        if (abs($value) === INF) {
            return $value;
        } else if ($value === 'INF') {
            return INF;
        } else if ($value === '-INF') {
            return -INF;
        } else {
            return (float) $value;
        }
    }

    /**
     * Calculates an operation ^
     *
     * @param array $array The subexpression of the formula
     *
     * @return array
     */
    private function calculate1(array $array)
    {
        for ($i=count($array)-1; $i>=0; $i--) {
            if (isset($array[$i]) && isset($array[$i-1]) && isset($array[$i+1])
            && $array[$i] === '^') {
                $otp = 1;
                $this->checkInf($array[$i-1], $array[$i+1]);
                if (is_numeric($array[$i-1]) && is_numeric($array[$i+1])) {
                    if ($array[$i-1] < 0) {
                        $a = pow($array[$i-1]*-1, $array[$i+1]);
                        $otp = 2;
                    } else {
                        $a = pow($array[$i-1], $array[$i+1]);
                    }
                } else {
                    $this->correct = 0;
                    break;
                }
                unset($array[$i-1], $array[$i+1]);
                $array[$i] = ($otp == 1) ? $a : $a*-1;
                $array = array_values($array);
                $i = count($array)-1;
            }
        }
        return $array;
    }

    /**
     * Calculates operations *, /
     *
     * @param array $array The subexpression of the formula
     *
     * @return array
     */
    private function calculate2(array $array)
    {
        for ($i=0; $i<count($array); $i++) {
            if (isset($array[$i]) && isset($array[$i-1]) && isset($array[$i+1])
            && ($array[$i] === '*' || $array[$i] === '/')) {
                $this->checkInf($array[$i-1], $array[$i+1]);
                if (!is_numeric($array[$i-1]) || !is_numeric($array[$i+1])) {
                    $this->correct = 0;
                    break;
                }
                if ($array[$i] === '*') {
                    $a = $array[$i-1] * $array[$i+1];
                } elseif ($array[$i] === '/') {
                    if ($array[$i+1] != 0) {
                        $a = $array[$i-1] / $array[$i+1];
                    } elseif ($array[$i-1] != 0 && $array[$i+1] == 0) {
                        $a = ($array[$i-1] > 0) ? INF : -INF;
                    } else {
                        $a = NAN;
                    }
                }
                unset($array[$i-1], $array[$i+1]);
                $array[$i] = $a;
                $array = array_values($array);
                $i = 0;
            }
        }
        return $array;
    }

    /**
     * Calculates operations +, -
     *
     * @param array $array The subexpression of the formula
     *
     * @return array
     */
    private function calculate3(array $array)
    {
        for ($i=0; $i<count($array); $i++) {
            if (isset($array[$i]) && isset($array[$i-1]) && isset($array[$i+1])
            && ($array[$i] === '+' || $array[$i] === '-')) {
                $this->checkInf($array[$i-1], $array[$i+1]);
                if (!is_numeric($array[$i-1]) || !is_numeric($array[$i+1])) {
                    $this->correct = 0;
                    break;
                }
                if ($array[$i] === '+') {
                    $a = $array[$i-1] + $array[$i+1];
                } elseif ($array[$i] === '-') {
                    $a = $array[$i-1] - $array[$i+1];
                }
                unset($array[$i-1], $array[$i+1]);
                $array[$i] = $a;
                $array = array_values($array);
                $i = 0;
            }
        }
        return $array;
    }

    /**
     * Calculates functions
     *
     * @param string  $function
     * @param string  $str
     * @param integer $strlen
     * @param integer $i
     */
    private function calculateFunction($function, &$str, &$strlen, $i)
    {
        $j = ($function == 'sqrt') ? $i+4 : $i+3;
        $arg = null;

        while (true) {
            if (isset($str[$j])) {
                if ((strstr('+-', $str[$j]) && $arg === null)
                || preg_match('/([\d\.eE\+\-])|([\d\.INF])/', $str[$j])) {
                    $arg .= $str[$j];
                } elseif (strstr(' ', $str[$j]) && !strpbrk($arg, '0123456789')) {
                } else {
                    $arg = trim($arg);
                    break;
                }
                $j++;
            } else {
                break;
            }
        }

        $this->checkInf($arg);
        if (!is_numeric($arg)) {
            $this->correct = 0;
        } else {
            if ($function == 'exp') {
                $result = pow(M_E, $arg);
            } else {
                $result = $function($arg);
            }
        }

        if (is_numeric($result) && $this->correct) {
            $str1 = substr($str, 0, $i);
            $str2 = substr($str, $j);
            $str = $str1.' '.$result.$str2;
            $strlen = strlen($str);
        }
    }

    /**
     * Combines operators and numbers
     *
     * @param array $array The subexpression of the formula
     *
     * @return array
     */
    private function combine($array) {
        $new_array = [];
        $i = $j = $key = 0;

        foreach ($array as $item) {

            $array_ip1 = (isset($array[$i+1])) ? $array[$i+1] : null;

            end($new_array);
            $cur = current($new_array);

            if ((@strstr('+-', (string) $item)) && (@strstr('+-', (string) $cur)) 
            && (@strstr('+-', (string) $array_ip1))) {
                if ($item === '-')
                    $new_array[key($new_array)] = ($cur == '+') ? '-' : '+';
            } elseif ((@strstr('+-', (string) $item)) && (@strstr('*/^', (string) $cur)) 
            && (@strstr('+-', (string) $array_ip1))) {
                $new_array[] = $item;
            } else {
                if ((@strstr('+-', (string) $item)) && ($key+1 != $i) 
                && (@strstr('*/^', (string) $array[$key]))
                && (!is_numeric($array[$key+1])) && (isset($array[$key+1]))) {
                    if ($item === '-')
                        $new_array[key($new_array)] = ($cur == '+') ? '-' : '+';
                } else {
                    $new_array[] = $item;
                    $key = $i;
                }
            }
            $i++;
        }

        $array = $new_array;
        $new_array = [];

        foreach ($array as $item) {

            $array_i = $array[$j];
            $array_ip1 = (isset($array[$j+1])) ? $array[$j+1] : null;
            $array_im1 = (isset($array[$j-1])) ? $array[$j-1] : null;
            $array_im2 = (isset($array[$j-2])) ? $array[$j-2] : null;
            $array_im3 = (isset($array[$j-3])) ? $array[$j-3] : null;

            if ($item === '+' || $item === '-') {
                if (($j == 0 && is_numeric($array_ip1))
                || ($j > 0 && is_numeric($array_ip1)
                && @stristr('+-*/^e', (string) $array_im1))) {
                    if ($item === '+') {
                        $new_array[] = $array_ip1;
                    } else {
                        if ($array_im1 === '-' && $array_im2 !== '-') {
                            $new_array[] = $array_ip1;
                        } elseif ($array_im1 === '-' && $array_im3 === '-') {
                            $this->correct = 0;
                            break;
                        } else {
                            $new_array[] = $item.$array_ip1;
                        }
                    }
                } else {
                    if ($item === '-' && $array_ip1 === '-') {
                        if (count($new_array))
                            $new_array[] = '+';
                    } elseif ($item === '-' && $array_ip1 === '+') {
                        if (count($new_array))
                            $new_array[] = '+';
                        $new_array[] = '0';
                        $new_array[] = '-';
                    } else {
                        if (count($new_array) || $item != '+')
                            $new_array[] = $item;
                    }
                }
            } elseif (($j == 1 && is_numeric($item) && @strstr('+-', (string) $array_im1))
            || ($j > 1 && is_numeric($item) && @strstr('+-', (string) $array_im1)
            && (@stristr('+-*/^e', (string) $array_im2)))) {
            } else {
                $new_array[] = $item;
            }
            $j++;
        }
        return $new_array;
    }

    /**
     * Parses and evaluates the subexpression of the formula.
     *
     * @param string $str The subexpression inside parentheses, or entire formula
     *                    if it doesn't contain parentheses.
     *
     * @return float
     */
    private function parse($str)
    {
        $str = trim($str);
        $this->expression = $str;
        $strlen = strlen($str);
        $main_array = [];
        $count = 0;

        for ($i=0; $i<$strlen; $i++) {

            $str_i = $str[$i];
            $str_ip1 = (isset($str[$i+1])) ? $str[$i+1] : null;
            $str_ip2 = (isset($str[$i+2])) ? $str[$i+2] : null;
            $str_ip3 = (isset($str[$i+3])) ? $str[$i+3] : null;
            $str_ip4 = (isset($str[$i+4])) ? $str[$i+4] : null;
            $str_im1 = (isset($str[$i-1])) ? $str[$i-1] : null;
            $str_im2 = (isset($str[$i-2])) ? $str[$i-2] : null;

            // NaN
            if (stristr($str, 'NaN'))
                return NAN;
            // Spaces will be skipped
            if ($str_i == ' ') {
                $count++;
            // Number
            } elseif (is_numeric($str_i)) {
                $main_array[$count] = (isset($main_array[$count])) ?
                        $main_array[$count].$str_i : $str_i;
            // Constant pi
            } elseif ($str_im1.$str_i == 'pi') {
            } elseif (($str_i.$str_ip1 == 'pi')
            && (!$str_ip2 || strstr('+-*/^ ', $str_ip2))) {
                $count++;
                $main_array[$count] = M_PI;
            // Constant e
            } elseif (($str_i == 'e') && ($str_ip1 != 'x') && (!is_numeric($str_im1))
            && (!$str_ip1 || strstr('+-*/^ ', $str_ip1))) {
                $count++;
                $main_array[$count] = M_E;
            // Number in E notation
            } elseif ((strtolower($str_i) == 'e') && ($str_ip1 != 'x')
            && (is_numeric($str_im1))) {
                $main_array[$count] = $main_array[$count].'E';
            } elseif ((strstr('+-', $str_i)) && (strtolower($str_im1) == 'e')
            && (is_numeric($str_ip1)) && (is_numeric($str_im2))) {
                $main_array[$count] = $main_array[$count].$str_i;
            // Decimal point
            } elseif (($str_i == '.') && ((isset($str_im1) && is_numeric($str_im1)) 
            || (isset($str_ip1)) && is_numeric($str_ip1))) {
                $main_array[$count] = (isset($main_array[$count])) ? 
                        $main_array[$count].'.' : '.';
            // Function
            } elseif ((in_array($str_i.$str_ip1.$str_ip2, $this->valid_functions))
            || ($str_i.$str_ip1.$str_ip2.$str_ip3 == 'sqrt')) {
                if ($str_i.$str_ip1.$str_ip2.$str_ip3 == 'sqrt') {
                    if ($str_ip4 == ' ')
                        $this->calculateFunction('sqrt', $str, $strlen, $i);
                } else {
                    if ($str_ip3 == ' ')
                        $this->calculateFunction($str_i.$str_ip1.$str_ip2, $str, $strlen, $i);
                }
            // Variable
            } elseif (in_array($str_i, $this->valid_variables) && count($this->variables)) {
                if (array_key_exists($str_i, $this->variables)
                && is_numeric($this->variables[$str_i])) {
                    $main_array[$count] = (float) $this->variables[$str_i];
                    $count++;
                } else {
                    $this->correct = 0;
                    $this->error_type = 4;
                    break;
                }
            } else {
                // Operator
                $count++;
                if (strstr('+-*/^', $str_i)) {
                    if (!count($main_array) && $str_i == '+')
                        continue;
                    $main_array[$count] = $str_i;
                    $count++;
                } else {
                    // Constant Inf
                    if ($str_im2.$str_im1.$str_i == 'INF') {
                    } elseif ($str_im1.$str_i.$str_ip1 == 'INF') {
                    } elseif ($str_i.$str_ip1.$str_ip2 == 'INF') {
                        if ($str_im1 == '-' && !count($main_array)) {
                            $main_array[$count] = '-';
                            $count++;
                        }
                        $main_array[$count] = INF;
                        $count++;
                    } else {
                        // Nothing matches
                        $this->correct = 0;
                        if (!$this->validate())
                            $this->error_type = 1;
                        break;
                    }
                }
            }
        }

        if (!$this->correct)
            return 0;

        $main_array = array_values($main_array);

        if (isset($main_array[1])) {

            $main_array = $this->combine($main_array);
            $main_array = $this->calculate1($main_array);
            $main_array = $this->calculate2($main_array);
            $main_array = $this->calculate3($main_array);

            if (count($main_array) != 1)
                $this->correct = 0;
        }

        if (!isset($main_array[0]) || strstr('+-*/^', (string) $main_array[0])
        || substr_count((string) $main_array[0], '.') > 1) {
            $this->correct = 0;
            return 0;
        }

        return $this->return_($main_array[0]);
    }

    /**
     * @param string  $expression
     * @param integer $length
     * @param integer $cursor
     * @param float   $base
     *
     * @return \stdClass
     */
    private function checkExp($expression, $length, $cursor, $base)
    {
        $response = new \stdClass();

        if ($base < 0) {
            $test_func = preg_replace('/\s+/', '', $expression);
            $test_func = substr($test_func , 0, -strlen(preg_replace('/\s+/', '',
                    $base.substr($expression, $length-$cursor))));
            $test_func_ok = true;
            if (preg_match('/([nstpg]\()|(in|os|an|bs|rt|xp|og)/', substr($test_func, -2)))
                $test_func_ok = false;

            if ($test_func_ok) {
                $expression = substr($expression, $length-$cursor+1);
                $test_exp = ltrim($expression);
                if (isset($test_exp[0]) && $test_exp[0] === '^') {
                    $exp = '';
                    for ($q=0; $q<=$cursor-1; $q++) {
                        if (isset($expression[$q]) && $expression[$q] === '^') {
                            $exp = ' ';
                        } elseif ($exp != '' && !strstr(' ', $expression[$q])) {
                            if (strstr('+-', $expression[$q]) && $exp == ' ') {
                                $exp .= $expression[$q];
                            } elseif (strstr('0123456789.(', $expression[$q])) {
                                if ($expression[$q] != '(')
                                    $exp .= $expression[$q];
                            } else {
                                $exp = trim($exp);
                                $cursor = $cursor - $q;
                                if (isset($exp[0]) && $exp[0] == '+')
                                    $exp = substr($exp, 1);
                                break;
                            }
                        }
                    }
                    $response->cursor = $cursor;
                    if (substr($exp, -1) == '.')
                        $exp = substr($exp, 0, -1);
                    if (!is_numeric($exp) || strstr($exp, '.')) {
                        $this->correct = $response->result = 0;
                    } else {
                        $response->result = pow(abs($base),$exp) * pow(-1,$exp);
                    }
                }
            }
        }
        return $response;
    }

    /**
     * Returns an error message
     *
     * @return string
     */
    private function errorMsg()
    {
        switch ($this->error_type) {
        case 1:
            return 'Invalid character';
        case 2:
            return 'Empty string';
        case 3:
            return 'Mismatched parentheses';
        case 4:
            return 'Variable error';
        default:
            return 'Syntax error';
        }
    }

    /**
     * @param string $formula
     */
    private function prepare(&$formula) {
        $formula = '( '.$formula.' )';    
        $formula = str_replace('Inf', 'INF', $formula);
        // Spaces and tab characters will be transformed
        $formula = preg_replace('/[\+\-\*\/\^\(\)]/', ' ${0} ', preg_replace('/\s+/', '', $formula));
    }

    /**
     * @param array $matches
     *
     * @return string
     */
    private function match($matches) {
        return ' ( '.preg_replace('/\s+/', '', $matches[0]).' ) ';
    }

    /**
     * @param array $matches
     *
     * @return string
     */
    private function match1($matches) {
        return $matches[0][0] . '( ( '.substr($matches[0],1,1).' ) )' . $matches[0][2];
    }

    /**
     * @return string
     */
    private function match2($matches) {
        return ' ( ( '.$matches[0].' ) ) ';
    }

    /**
     * Groups the parts of the formula that must be evaluated first into parentheses
     *
     * @param string $formula
     * @param string $opt
     */
    private function group(&$formula, $opt = 'init') {
        if ($opt == 'init') {
            // Numbers in E notation
            $formula = preg_replace_callback('/[\d\.]+( )?[eE]( )?[\+\-]?( )?[\d\.]+ /',
                    [$this, 'match'], $formula);
            // Variables
            if (count($this->variables) > 0) {
            	$valid_variables = implode("|", $this->valid_variables);
                $formula = preg_replace_callback('/[\+\-\*\/\^ ]('.$valid_variables.')[ \+\-\*\/\^]/',
                        [$this, 'match1'], $formula);
            }
        } else {
            // Functions
            $valid_functions = implode("|", $this->valid_functions);
            $formula = preg_replace_callback('/('.$valid_functions.')( )?[\+\-]?( )?[\d\.eEINF]+ /',
                    [$this, 'match2'], $formula);
        }
    }

    /**
     * @param float   $result
     * @param integer $precision
     *
     * @return float
     */
    private function round_($result, $precision) {
        if (abs($result) === INF) {
            return $result;
        }
        $str = strtolower((string) $result);
        if (strpos($str, 'e') !== false) {
            $r = explode('e', $str);
            $left = round((float) $r[0], (int) $precision);
            $right = $r[1];
            return (float) ($left.'e'.$right);
        }
        return round($result, (int) $precision);
    }

    /**
     * Parses and evaluates a given formula.
     *
     * @return array Returns an array [0 => v1, 1 => v2], where
     *               v1 is 'done' or 'error', and
     *               v2 is a computed result or error message, respectively.
     */
    public function getResult()
    {
        // Check that the formula is not empty
        if (!isset($this->formula[0])) {
            $this->correct = 0;
            $this->error_type = 2;
        }

        // Check set variables
        for ($i=0; $i<=count($this->valid_variables)-1; $i++) { 
            if (strlen($this->valid_variables[$i]) != 1  
            || !preg_match('/^([a-z])$/', $this->valid_variables[$i]) === 1
            || $this->valid_variables[$i] == "e") {
                $this->correct = 0;
                $this->error_type = 4;
            }
        }

        $this->prepare($this->formula);
        $this->group($this->formula);

        $open_parentheses_count = substr_count($this->formula, '(');
        $close_parentheses_count = substr_count($this->formula, ')');

        // Check for an equality of opening and closing parentheses
        if ($open_parentheses_count != $close_parentheses_count) {
            $this->correct = 0;
            $this->error_type = 3;
        }

        // Check the syntax is correct when using parentheses
        if (preg_match('/(\)( )?[^\)\+\-\*\/\^ ])|(\(( )*?\))|([^nstgp\(\+\-\*\/\^ ]( )?\()/',
        $this->formula)) {
            $this->correct = 0;
        }

        $processing_formula = $this->formula;

        // Begin a general parse
        while ((strstr($processing_formula, '(') || strstr($processing_formula, ')'))
        && $this->correct) {
            $start_cursor_pos = 0; $end_cursor_pos = 0;
            $this->group($processing_formula, '');
            $temp = $processing_formula;

            while (strstr($temp, '(')) {
                $strlen_temp = strlen($temp);
                for ($i=0; $i<=$strlen_temp-1; $i++) {
                    if (isset($temp[$i]) && $temp[$i] == '(') {
                        $temp = substr($temp, $i+1);
                        $start_cursor_pos = $start_cursor_pos + $i+1;
                    }
                }
            }

            $strlen_temp = strlen($temp);
            for ($i=0; $i<=$strlen_temp-1; $i++) {
                if (isset($temp[$i]) && $temp[$i] == ')') {
                    $end_cursor_pos = $strlen_temp - $i;
                    $temp = substr($temp, 0, $i);
                    break;
                }
            }

            $length = strlen($processing_formula);

            if (!empty($temp)) {
                $temp = $this->parse($temp);
                $checkExp = $this->checkExp($processing_formula, $length, $end_cursor_pos, $temp);
                if (isset($checkExp->result)) {
                    $temp = $checkExp->result;
                    $end_cursor_pos = $checkExp->cursor;
                }
            }

            $processing_formula = substr($processing_formula, 0, $start_cursor_pos-1)
                    .$temp.substr($processing_formula, $length - $end_cursor_pos+1);
        }

        $this->formula = $processing_formula;

        $result = $this->parse($this->formula);

        if ($this->correct) {
            return ['done', $this->round_($result, $this->precision)];
        } else {
            return ['error', $this->errorMsg()];
        }
    }
}
