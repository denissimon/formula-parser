<?php

namespace FormulaParser;

interface IFormulaParser
{
    public function setValidVariables(array $vars);

    public function setVariables(array $vars);

    public function getResult();

    public function getFormula();
}
