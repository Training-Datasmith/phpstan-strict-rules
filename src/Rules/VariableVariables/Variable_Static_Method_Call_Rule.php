<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Variable_Variables;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Static_Call;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<StaticCall>
 */
class Variable_Static_Method_Call_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Static_Call::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($node->name instanceof Node\Identifier) {
            return [];
        }
        if ($node->class instanceof Node\Name) {
            $method_called_on = $scope->resolve_name($node->class);
        } else {
            $method_called_on = $scope->get_type($node->class)->describe(Verbosity_Level::type_only());
        }
        return [Rule_Error_Builder::message(sprintf('Variable static method call on %s.', $method_called_on))->identifier('staticMethod.dynamicName')->build()];
    }
}