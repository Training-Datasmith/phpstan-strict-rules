<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Operators;

use Php_Parser\Node\Expr\Post_Dec;
/**
 * @phpstan-extends OperandInArithmeticIncrementOrDecrementRule<PostDec>
 */
class Operand_In_Arithmetic_Post_Decrement_Rule extends Operand_In_Arithmetic_Increment_Or_Decrement_Rule
{
    public function get_node_type(): string
    {
        return Post_Dec::class;
    }
    protected function describe_operation(): string
    {
        return 'post-decrement';
    }
    protected function get_identifier(): string
    {
        return 'postDec';
    }
}