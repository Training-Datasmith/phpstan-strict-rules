<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Disallowed_Constructs;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Shell_Exec;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
/**
 * @implements Rule<ShellExec>
 */
class Disallowed_Backtick_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Shell_Exec::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        return [Rule_Error_Builder::message('Backtick operator is not allowed. Use shell_exec() instead.')->identifier('backtick.notAllowed')->build()];
    }
}