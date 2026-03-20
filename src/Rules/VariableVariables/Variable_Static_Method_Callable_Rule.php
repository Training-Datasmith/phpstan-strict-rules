<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Variable_Variables;

use Php_Parser\Node;
use Php_Stan\Analyser\Scope;
use Php_Stan\Node\Static_Method_Callable_Node;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<StaticMethodCallableNode>
 */
class Variable_Static_Method_Callable_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Static_Method_Callable_Node::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($node->get_name() instanceof Node\Identifier) {
            return [];
        }
        if ($node->get_class() instanceof Node\Name) {
            $method_called_on = $scope->resolve_name($node->get_class());
        } else {
            $method_called_on = $scope->get_type($node->get_class())->describe(Verbosity_Level::type_only());
        }
        return [Rule_Error_Builder::message(sprintf('Variable static method call on %s.', $method_called_on))->identifier('staticMethod.dynamicName')->build()];
    }
}