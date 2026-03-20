<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Variable_Variables;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Static_Property_Fetch;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<StaticPropertyFetch>
 */
class Variable_Static_Property_Fetch_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Static_Property_Fetch::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($node->name instanceof Node\Identifier) {
            return [];
        }
        if ($node->class instanceof Node\Name) {
            $property_accessed_on = $scope->resolve_name($node->class);
        } else {
            $property_accessed_on = $scope->get_type($node->class)->describe(Verbosity_Level::type_only());
        }
        return [Rule_Error_Builder::message(sprintf('Variable static property access on %s.', $property_accessed_on))->identifier('staticProperty.dynamicName')->build()];
    }
}