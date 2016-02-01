<?php

require_once '../FormulaParser.php';

use FormulaParser\FormulaParser;

class FormulaParserTest extends PHPUnit_Framework_TestCase
{
    // 1 -------- Test NAN ---------------------------------------------------------------->
    
    /**
     * @dataProvider testGetResultNANData
     */
    public function testGetResultNAN($input_string) {
        $formula = new FormulaParser($input_string);
        $result = $formula->getResult();
        
        $this->assertEquals((string) $result[1], 'NAN');
    }
    
    public function testGetResultNANData() {
        return [
            ['sqrt(log(0)) + 1'],
            ['sqrt(-1.0)'],
            ['sqrt(-INF)'],
            ['sin(INF)'],
            ['0/0 + 1'],
            ['5^500 - 5^500'],
            ['5^500 / 5^500'],
            ['0 * INF'],
            ['Inf / Inf'],
            ['Inf / -Inf'],
            ['Inf - Inf'],
            ['NaN']
        ];
    }
    
    // 2 -------- Test infinity ----------------------------------------------------------->    

    /**
     * @dataProvider testGetResultINFData
     */
    public function testGetResultINF($input_string, $expected_result) {
        $formula = new FormulaParser($input_string);
        $result = $formula->getResult();
        
        $this->assertEquals($result[1], $expected_result);
    }
    
    public function testGetResultINFData() {
        return [
            ['sqrt(5^500)', INF],
            ['10 + log(0)', -INF],
            ['-(5)^500+5', -INF],
            ['(-5)^500+5', INF],
            ['abs(-5^500)/pi', INF],
            ['-abs(-5^500+1)', -INF],
            ['log(0)', -INF],
            ['-log(0)', INF],
            ['INF + 1', INF],
            ['Inf * Inf', INF],
            ['Inf * -Inf', -INF]
        ];
    }
    
    // 3 -------- Test variables ---------------------------------------------------------->

    /**
     * @dataProvider testGetResultVariablesData
     */
    public function testGetResultVariables($input_string, $expected_result, $variables = [], $precision_rounding = 4) {
        $formula = new FormulaParser($input_string, $precision_rounding);
        $formula->setVariables($variables);
        $result = $formula->getResult();
        
        $this->assertEquals($result[1], $expected_result);
    }
    
    public function testGetResultVariablesData() {
        return [
            ['3*x^2 - 4*y + 3/y', 16.38, ['x' => -4, 'y' => 8], 2],
            ['5/-x', 2.5, ['x' => -2]],
            ['+-z', 10, ['z' => -10]],
            ['sqrt(x^y/pi)', 9.027, ['x' => -2, 'y' => 8]],
            ['abs(a-b^3)', 25, ['a' => 2, 'b' => 3]],
            ['x-tan(-4)^3', 1.4521, ['x' => -.1]],
            ['(y)^x', 16, ['x' => 4, 'y' => -2]]
        ];
    }
    
    // 4 -------- Test numbers in E notation ---------------------------------------------->
    
    /**
     * @dataProvider testGetResultEData
     */
    public function testGetResultE($input_string, $expected_result, $variables = [], $precision_rounding = 5) {
        $formula = new FormulaParser($input_string, $precision_rounding);
        $formula->setVariables($variables);
        $result = $formula->getResult();
                
        $this->assertEquals($result[1], $expected_result);
    }
    
    public function testGetResultEData() {
        return [
            ['1e3+e+5^30', 9.31323E+20],
            ['abs(-5^30)', 9.31323E+20],            
            ['2^(sqrt(15)^3)', 3.07796E+17],
            ['(-1E3+1)^(1E+1)', 9.90045E+29],
            ['-2^3^4', -2.41785E+24],
            ['4^-0.8e+1/x', 3.81469727e-6, ['x' => 4], 8]
        ];
    }
     
    // 5 -------- Test operator combinations ---------------------------------------------->
    
    /**
     * @dataProvider testGetResultOperatorsData
     */
    public function testGetResultOperators($input_string, $expected_result) {
        $formula = new FormulaParser($input_string);
        $result = $formula->getResult();
        
        $this->assertEquals($result[1], $expected_result);
    }
    
    public function testGetResultOperatorsData() {
        return [
            ['10/++--2', 5],
            ['-+8', -8],
            ['5^--2', 25],
            ['5^-+2', 0.04],
            ['--sin(90)', 0.894],
            ['--4*-+-8', 32],
            ['5.-++-.5', 5.5],
            ['5^+++2', 25],
            ['2^-+-5', 32]
        ];
    }
    
    // 6 -------- Test exponential expressions -------------------------------------------->

    /**
     * @dataProvider testGetResultExpData
     */
    public function testGetResultExp($input_string, $expected_result) {
        $formula = new FormulaParser($input_string);
        $result = $formula->getResult();
        
        $this->assertEquals($result[1], $expected_result);
    }
    
    public function testGetResultExpData() {
        return [
            ['10*(3-5)^4/2', 80],
            ['3+4*2/(1-5)^8', 3.0001],
            ['5^-500', 0],
            ['1^(-5^500)', 1],
            ['(-2)^+4', 16],
            ['-2^(-4)', -0.0625],
            ['8e+1^2', 6400],
            ['exp((-3)^2)', 8103.0839],
            ['cos(-4)^8', 0.0333],
            ['sin(10^5)', 0.0357], 
            ['sin(-90)^-5', -1.7511],
            ['pi^sin(e)', 1.6004]
        ];
    }
    
    // 7 --------  Test errors ------------------------------------------------------------>

    /**
     * @dataProvider testGetResultErrorData
     */
    public function testGetResultError($input_string, $expected_result, $variables = []) {
        $formula = new FormulaParser($input_string);
        $formula->setVariables($variables);
        $result = $formula->getResult();
        
        $this->assertEquals($result[1], $expected_result);
    }
    
    public function testGetResultErrorData() {
        return [
            ['5*/7', 'Syntax error'],
            ['^', 'Syntax error'],
            ['.', 'Syntax error'],            
            ['()', 'Syntax error'],
            [') (', 'Syntax error'],
            ['(1+1)5', 'Syntax error'],
            ['pi e', 'Syntax error'],
            ['1*E1', 'Syntax error'],
            ['1.(23)', 'Syntax error'],
            ['1.2.3', 'Syntax error'],
            ['.y', 'Syntax error', ['y' => '4']],
            ['y', 'Syntax error', []],
            ['  ', 'Empty string'],
            ['_', 'Invalid character'],
            ['X', 'Invalid character', ['x' => '4']],
            ['(x))', 'Mismatched parentheses'],
            ['x+y', 'Variable error', ['x' => '1', 'y' => '']]
        ];
    }
    
    // 8 -------- Test other cases -------------------------------------------------------->

    /**
     * @dataProvider testGetResultData
     */
    public function testGetResult($input_string, $expected_result) {
        $formula = new FormulaParser($input_string);
        $result = $formula->getResult();
        
        $this->assertEquals($result[1], $expected_result);
    }
    
    public function testGetResultData() {
        return [
            ['5', 5],
            ['(10+5)', 15],
            ['-(-8)', 8],
            ['(0.1+0.7)*10', 8],
            ['4.4/4/-0.4', -2.75],
            ['8+(10*(3+5))/2', 48],
            ['-35+.5e+5', 49965],
            ['sqrt(9)', 3],
            ['sqrt(exp(pi))', 4.8105],
            ['sin(-5.28e+8)', 0.2939],
            ['5e+1^0.5+e', 9.7893],
            ['5E+1+e', 52.7183]
        ];
    }
}

?>
