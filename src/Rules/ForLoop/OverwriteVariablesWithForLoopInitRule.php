<?php

declare (strict_types=1);
namespace Php_Stan\Rules\For_Loop;

use function is_string;
use Php_Parser\Node;
use Php_Parser\Node\Expr;
use Php_Parser\Node\Expr\Assign;
use Php_Parser\Node\Stmt\For_;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Identifier_Rule_Error;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use function sprintf;
/**
 * @implements Rule<For_>
 */
class Overwrite_Variables_With_For_Loop_Init_Rule implements Rule
{
    public function get_node_type(): string
    {
        return For_::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($node->init as $expr) {
            if (!$expr instanceof Assign) {
                continue;
            }
            foreach ($this->check_value_var($scope, $expr->var) as $error) {
                $errors[] = $error;
            }
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
            $errors[] = Rule_Error_Builder::message(sprintf('For loop initial assignment overwrites variable $%s.', $expr->name))->identifier('for.variableOverwrite')->build();
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