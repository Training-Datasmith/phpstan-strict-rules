<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Booleans_In_Conditions;

use Php_Parser\Node;
use Php_Stan\Analyser\Scope;
use Php_Stan\Node\Boolean_And_Node;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<BooleanAndNode>
 */
class Boolean_In_Boolean_And_Rule implements Rule
{
    private Boolean_Rule_Helper $helper;
    public function __construct(Boolean_Rule_Helper $helper)
    {
        $this->helper = $helper;
    }
    public function get_node_type(): string
    {
        return Boolean_And_Node::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        $original_node = $node->get_original_node();
        $messages = [];
        $node_text = $original_node->get_operator_sigil();
        $identifier_type = $original_node instanceof Node\Expr\Binary_Op\Boolean_And ? 'booleanAnd' : 'logicalAnd';
        if (!$this->helper->passes_as_boolean($scope, $original_node->left)) {
            $left_type = $scope->get_type($original_node->left);
            $messages[] = Rule_Error_Builder::message(sprintf('Only booleans are allowed in %s, %s given on the left side.', $node_text, $left_type->describe(Verbosity_Level::type_only())))->identifier(sprintf('%s.leftNotBoolean', $identifier_type))->build();
        }
        $right_scope = $node->get_right_scope();
        if (!$this->helper->passes_as_boolean($right_scope, $original_node->right)) {
            $right_type = $right_scope->get_type($original_node->right);
            $messages[] = Rule_Error_Builder::message(sprintf('Only booleans are allowed in %s, %s given on the right side.', $node_text, $right_type->describe(Verbosity_Level::type_only())))->identifier(sprintf('%s.rightNotBoolean', $identifier_type))->build();
        }
        return $messages;
    }
}