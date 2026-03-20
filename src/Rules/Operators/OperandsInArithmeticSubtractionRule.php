<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Operators;

use Php_Parser\Node;
use Php_Parser\Node\Expr;
use Php_Parser\Node\Expr\Assign_Op\Minus as AssignOpMinus;
use Php_Parser\Node\Expr\Binary_Op\Minus as BinaryOpMinus;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<Expr>
 */
class Operands_In_Arithmetic_Subtraction_Rule implements Rule
{
    private Operator_Rule_Helper $helper;
    public function __construct(Operator_Rule_Helper $helper)
    {
        $this->helper = $helper;
    }
    public function get_node_type(): string
    {
        return Expr::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($node instanceof Binary_Op_Minus) {
            $left = $node->left;
            $right = $node->right;
        } elseif ($node instanceof Assign_Op_Minus) {
            $left = $node->var;
            $right = $node->expr;
        } else {
            return [];
        }
        $messages = [];
        $left_type = $scope->get_type($left);
        if (!$this->helper->is_valid_for_arithmetic_operation($scope, $left)) {
            $messages[] = Rule_Error_Builder::message(sprintf('Only numeric types are allowed in -, %s given on the left side.', $left_type->describe(Verbosity_Level::type_only())))->identifier('minus.leftNonNumeric')->build();
        }
        $right_type = $scope->get_type($right);
        if (!$this->helper->is_valid_for_arithmetic_operation($scope, $right)) {
            $messages[] = Rule_Error_Builder::message(sprintf('Only numeric types are allowed in -, %s given on the right side.', $right_type->describe(Verbosity_Level::type_only())))->identifier('minus.rightNonNumeric')->build();
        }
        return $messages;
    }
}