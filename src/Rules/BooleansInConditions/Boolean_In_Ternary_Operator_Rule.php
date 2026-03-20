<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Booleans_In_Conditions;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Ternary;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<Ternary>
 */
class Boolean_In_Ternary_Operator_Rule implements Rule
{
    private Boolean_Rule_Helper $helper;
    public function __construct(Boolean_Rule_Helper $helper)
    {
        $this->helper = $helper;
    }
    public function get_node_type(): string
    {
        return Ternary::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($node->if === null) {
            return [];
            // elvis ?:
        }
        if ($this->helper->passes_as_boolean($scope, $node->cond)) {
            return [];
        }
        $condition_expression_type = $scope->get_type($node->cond);
        return [Rule_Error_Builder::message(sprintf('Only booleans are allowed in a ternary operator condition, %s given.', $condition_expression_type->describe(Verbosity_Level::type_only())))->identifier('ternary.condNotBoolean')->build()];
    }
}