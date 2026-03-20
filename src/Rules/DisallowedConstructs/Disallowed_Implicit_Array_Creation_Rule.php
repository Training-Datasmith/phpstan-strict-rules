<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Disallowed_Constructs;

use function is_string;
use Php_Parser\Node;
use Php_Parser\Node\Expr\Array_Dim_Fetch;
use Php_Parser\Node\Expr\Assign;
use Php_Parser\Node\Expr\Variable;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use function sprintf;
/**
 * @implements Rule<Assign>
 */
class Disallowed_Implicit_Array_Creation_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Assign::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if (!$node->var instanceof Array_Dim_Fetch) {
            return [];
        }
        $node = $node->var;
        while ($node instanceof Array_Dim_Fetch) {
            $node = $node->var;
        }
        if (!$node instanceof Variable) {
            return [];
        }
        if (!is_string($node->name)) {
            return [];
        }
        $certainty = $scope->has_variable_type($node->name);
        if ($certainty->no()) {
            return [Rule_Error_Builder::message(sprintf('Implicit array creation is not allowed - variable $%s does not exist.', $node->name))->identifier('variable.implicitArray')->build()];
        }
        if ($certainty->maybe()) {
            return [Rule_Error_Builder::message(sprintf('Implicit array creation is not allowed - variable $%s might not exist.', $node->name))->identifier('variable.implicitArray')->build()];
        }
        return [];
    }
}