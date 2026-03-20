<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Methods;

use Php_Parser\Node;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class Illegal_Constructor_Method_Call_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Node\Expr\Method_Call::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier || $node->name->to_lower_string() !== '__construct') {
            return [];
        }
        return [Rule_Error_Builder::message('Call to __construct() on an existing object is not allowed.')->identifier('constructor.call')->build()];
    }
}