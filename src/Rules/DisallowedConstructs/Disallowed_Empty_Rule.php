<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Disallowed_Constructs;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Empty_;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
/**
 * @implements Rule<Empty_>
 */
class Disallowed_Empty_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Empty_::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        return [Rule_Error_Builder::message('Construct empty() is not allowed. Use more strict comparison.')->identifier('empty.notAllowed')->build()];
    }
}