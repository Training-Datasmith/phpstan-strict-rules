<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Foreach_Loop;

use function is_string;
use Php_Parser\Node;
use Php_Parser\Node\Expr;
use Php_Parser\Node\Stmt\Foreach_;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Identifier_Rule_Error;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use function sprintf;
/**
 * @implements Rule<Foreach_>
 */
class Overwrite_Variables_With_Foreach_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Foreach_::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        $errors = [];
        if ($node->key_var instanceof Node\Expr\Variable && is_string($node->key_var->name) && $scope->has_variable_type($node->key_var->name)->yes()) {
            $errors[] = Rule_Error_Builder::message(sprintf('Foreach overwrites $%s with its key variable.', $node->key_var->name))->identifier('foreach.keyOverwrite')->build();
        }
        foreach ($this->check_value_var($scope, $node->value_var) as $error) {
            $errors[] = $error;
        }
        return $errors;
    }
    /**
     * @return list<IdentifierRuleError>
     */
    private function check_value_var(Scope $scope, Expr $expr): array
    {
        $errors = [];
        if ($expr instanceof Node\Expr\Variable && is_string($expr->name) && $scope->has_variable_type($expr->name)->yes()) {
            $errors[] = Rule_Error_Builder::message(sprintf('Foreach overwrites $%s with its value variable.', $expr->name))->identifier('foreach.valueOverwrite')->build();
        }
        if ($expr instanceof Node\Expr\List_ || $expr instanceof Node\Expr\Array_) {
            foreach ($expr->items as $item) {
                if ($item === null) {
                    continue;
                }
                foreach ($this->check_value_var($scope, $item->value) as $error) {
                    $errors[] = $error;
                }
            }
        }
        return $errors;
    }
}