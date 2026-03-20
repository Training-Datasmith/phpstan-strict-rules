<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Booleans_In_Conditions;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Boolean_Not;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<BooleanNot>
 */
class Boolean_In_Boolean_Not_Rule implements Rule
{
    private Boolean_Rule_Helper $helper;
    public function __construct(Boolean_Rule_Helper $helper)
    {
        $this->helper = $helper;
    }
    public function get_node_type(): string
    {
        return Boolean_Not::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($this->helper->passes_as_boolean($scope, $node->expr)) {
            return [];
        }
        $expression_type = $scope->get_type($node->expr);
        return [Rule_Error_Builder::message(sprintf('Only booleans are allowed in a negated boolean, %s given.', $expression_type->describe(Verbosity_Level::type_only())))->identifier('booleanNot.exprNotBoolean')->build()];
    }
}