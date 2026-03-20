<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Disallowed_Constructs;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Binary_Op;
use Php_Parser\Node\Expr\Binary_Op\Equal;
use Php_Parser\Node\Expr\Binary_Op\Not_Equal;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<BinaryOp>
 */
class Disallowed_Loose_Comparison_Rule implements Rule
{
    private bool $include_operand_types_in_error_message;
    public function __construct(bool $include_operand_types_in_error_message)
    {
        $this->include_operand_types_in_error_message = $include_operand_types_in_error_message;
    }
    public function get_node_type(): string
    {
        return Binary_Op::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if (!$node instanceof Equal && !$node instanceof Not_Equal) {
            return [];
        }
        $left = $scope->get_type($node->left)->describe(Verbosity_Level::type_only());
        $right = $scope->get_type($node->right)->describe(Verbosity_Level::type_only());
        if ($node instanceof Equal) {
            return [Rule_Error_Builder::message($this->include_operand_types_in_error_message ? sprintf('Loose comparison via "==" between %s and %s is not allowed.', $left, $right) : 'Loose comparison via "==" is not allowed.')->tip('Use strict comparison via "===" instead.')->identifier('equal.notAllowed')->build()];
        }
        return [Rule_Error_Builder::message($this->include_operand_types_in_error_message ? sprintf('Loose comparison via "!=" between %s and %s is not allowed.', $left, $right) : 'Loose comparison via "!=" is not allowed.')->tip('Use strict comparison via "!==" instead.')->identifier('notEqual.notAllowed')->build()];
    }
}