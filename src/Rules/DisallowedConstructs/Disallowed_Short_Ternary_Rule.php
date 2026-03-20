<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Disallowed_Constructs;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Ternary;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
/**
 * @implements Rule<Ternary>
 */
class Disallowed_Short_Ternary_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Ternary::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($node->if !== null) {
            return [];
        }
        return [Rule_Error_Builder::message('Short ternary operator is not allowed. Use null coalesce operator if applicable or consider using long ternary.')->identifier('ternary.shortNotAllowed')->build()];
    }
}