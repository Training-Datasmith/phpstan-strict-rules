<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Operators;

use Php_Parser\Node\Expr\Pre_Dec;
/**
 * @phpstan-extends OperandInArithmeticIncrementOrDecrementRule<PreDec>
 */
class Operand_In_Arithmetic_Pre_Decrement_Rule extends Operand_In_Arithmetic_Increment_Or_Decrement_Rule
{
    public function get_node_type(): string
    {
        return Pre_Dec::class;
    }
    protected function describe_operation(): string
    {
        return 'pre-decrement';
    }
    protected function get_identifier(): string
    {
        return 'preDec';
    }
}