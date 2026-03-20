<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Booleans_In_Conditions;

use Php_Parser\Node;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<Node\Stmt\While_>
 */
class Boolean_In_While_Condition_Rule implements Rule
{
    private Boolean_Rule_Helper $helper;
    public function __construct(Boolean_Rule_Helper $helper)
    {
        $this->helper = $helper;
    }
    public function get_node_type(): string
    {
        return Node\Stmt\While_::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($this->helper->passes_as_boolean($scope, $node->cond)) {
            return [];
        }
        $condition_expression_type = $scope->get_type($node->cond);
        return [Rule_Error_Builder::message(sprintf('Only booleans are allowed in a while condition, %s given.', $condition_expression_type->describe(Verbosity_Level::type_only())))->identifier('while.condNotBoolean')->build()];
    }
}