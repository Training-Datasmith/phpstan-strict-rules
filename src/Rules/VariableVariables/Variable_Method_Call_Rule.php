<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Variable_Variables;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Method_Call;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<MethodCall>
 */
class Variable_Method_Call_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Method_Call::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($node->name instanceof Node\Identifier) {
            return [];
        }
        return [Rule_Error_Builder::message(sprintf('Variable method call on %s.', $scope->get_type($node->var)->describe(Verbosity_Level::type_only())))->identifier('method.dynamicName')->build()];
    }
}