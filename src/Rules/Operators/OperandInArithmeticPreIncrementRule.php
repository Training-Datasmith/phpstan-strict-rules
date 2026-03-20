<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Operators;

use Php_Parser\Node\Expr\Pre_Inc;
/**
 * @phpstan-extends OperandInArithmeticIncrementOrDecrementRule<PreInc>
 */
class Operand_In_Arithmetic_Pre_Increment_Rule extends Operand_In_Arithmetic_Increment_Or_Decrement_Rule
{
    public function get_node_type(): string
    {
        return Pre_Inc::class;
    }
    protected function describe_operation(): string
    {
        return 'pre-increment';
    }
    protected function get_identifier(): string
    {
        return 'preInc';
    }
}