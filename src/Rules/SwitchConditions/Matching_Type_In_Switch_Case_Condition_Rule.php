<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Switch_Conditions;

use Php_Parser\Node;
use Php_Parser\Node\Stmt\Switch_;
use Php_Stan\Analyser\Scope;
use Php_Stan\Node\Printer\Printer;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<Switch_>
 */
class Matching_Type_In_Switch_Case_Condition_Rule implements Rule
{
    private Printer $printer;
    public function __construct(Printer $printer)
    {
        $this->printer = $printer;
    }
    public function get_node_type(): string
    {
        return Switch_::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        $messages = [];
        $condition_type = $scope->get_type($node->cond);
        foreach ($node->cases as $case) {
            if ($case->cond === null) {
                continue;
            }
            $case_type = $scope->get_type($case->cond);
            if (!$condition_type->is_super_type_of($case_type)->no()) {
                continue;
            }
            $messages[] = Rule_Error_Builder::message(sprintf('Switch condition type (%s) does not match case condition %s (%s).', $condition_type->describe(Verbosity_Level::value()), $this->printer->pretty_print_expr($case->cond), $case_type->describe(Verbosity_Level::type_only())))->line($case->get_start_line())->identifier('switch.type')->build();
        }
        return $messages;
    }
}