<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Operators;

use Php_Parser\Node\Expr\Post_Inc;
/**
 * @phpstan-extends OperandInArithmeticIncrementOrDecrementRule<PostInc>
 */
class Operand_In_Arithmetic_Post_Increment_Rule extends Operand_In_Arithmetic_Increment_Or_Decrement_Rule
{
    public function get_node_type(): string
    {
        return Post_Inc::class;
    }
    protected function describe_operation(): string
    {
        return 'post-increment';
    }
    protected function get_identifier(): string
    {
        return 'postInc';
    }
}