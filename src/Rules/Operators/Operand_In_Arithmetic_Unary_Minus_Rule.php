<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Operators;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Unary_Minus;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @phpstan-implements Rule<UnaryMinus>
 */
class Operand_In_Arithmetic_Unary_Minus_Rule implements Rule
{
    private Operator_Rule_Helper $helper;
    public function __construct(Operator_Rule_Helper $helper)
    {
        $this->helper = $helper;
    }
    public function get_node_type(): string
    {
        return Unary_Minus::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        $messages = [];
        if (!$this->helper->is_valid_for_arithmetic_operation($scope, $node->expr)) {
            $var_type = $scope->get_type($node->expr);
            $messages[] = Rule_Error_Builder::message(sprintf('Only numeric types are allowed in unary -, %s given.', $var_type->describe(Verbosity_Level::type_only())))->identifier('unaryMinus.nonNumeric')->build();
        }
        return $messages;
    }
}