<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Operators;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Post_Dec;
use Php_Parser\Node\Expr\Post_Inc;
use Php_Parser\Node\Expr\Pre_Dec;
use Php_Parser\Node\Expr\Pre_Inc;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @phpstan-template TNodeType of PreInc|PreDec|PostInc|PostDec
 * @phpstan-implements Rule<TNodeType>
 */
abstract class Operand_In_Arithmetic_Increment_Or_Decrement_Rule implements Rule
{
    private Operator_Rule_Helper $helper;
    public function __construct(Operator_Rule_Helper $helper)
    {
        $this->helper = $helper;
    }
    /**
     * @param TNodeType $node
     */
    public function process_node(Node $node, Scope $scope): array
    {
        $messages = [];
        $var_type = $scope->get_type($node->var);
        if (($node instanceof Pre_Inc || $node instanceof Post_Inc) && !$this->helper->is_valid_for_increment($scope, $node->var) || ($node instanceof Pre_Dec || $node instanceof Post_Dec) && !$this->helper->is_valid_for_decrement($scope, $node->var)) {
            $messages[] = Rule_Error_Builder::message(sprintf('Only numeric types are allowed in %s, %s given.', $this->describe_operation(), $var_type->describe(Verbosity_Level::type_only())))->identifier(sprintf('%s.nonNumeric', $this->get_identifier()))->build();
        }
        return $messages;
    }
    abstract protected function describe_operation(): string;
    /**
     * @return 'preInc'|'postInc'|'preDec'|'postDec'
     */
    abstract protected function get_identifier(): string;
}