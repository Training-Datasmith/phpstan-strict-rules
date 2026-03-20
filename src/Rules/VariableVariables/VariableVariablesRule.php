<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Variable_Variables;

use function is_string;
use Php_Parser\Node;
use Php_Parser\Node\Expr\Variable;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
/**
 * @implements Rule<Variable>
 */
class Variable_Variables_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Variable::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if (is_string($node->name)) {
            return [];
        }
        return [Rule_Error_Builder::message('Variable variables are not allowed.')->identifier('variable.dynamicName')->build()];
    }
}