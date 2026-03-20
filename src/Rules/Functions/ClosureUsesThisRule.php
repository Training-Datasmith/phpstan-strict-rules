<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Functions;

use function is_string;
use Php_Parser\Node;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\This_Type;
use function sprintf;
/**
 * @implements Rule<Node\Expr\Closure>
 */
class Closure_Uses_This_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Node\Expr\Closure::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($node->static) {
            return [];
        }
        if ($scope->is_in_closure_bind()) {
            return [];
        }
        $messages = [];
        foreach ($node->uses as $closure_use) {
            $var_type = $scope->get_type($closure_use->var);
            if (!is_string($closure_use->var->name)) {
                continue;
            }
            if (!$var_type instanceof This_Type) {
                continue;
            }
            $messages[] = Rule_Error_Builder::message(sprintf('Anonymous function uses $this assigned to variable $%s. Use $this directly in the function body.', $closure_use->var->name))->line($closure_use->get_start_line())->identifier('closure.useThis')->build();
        }
        return $messages;
    }
}