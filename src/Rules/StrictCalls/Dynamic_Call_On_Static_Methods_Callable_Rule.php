<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Strict_Calls;

use Php_Parser\Node;
use Php_Stan\Analyser\Scope;
use Php_Stan\Node\Method_Callable_Node;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Rules\Rule_Level_Helper;
use Php_Stan\Type\Error_Type;
use Php_Stan\Type\Type;
use function sprintf;
/**
 * @implements Rule<MethodCallableNode>
 */
class Dynamic_Call_On_Static_Methods_Callable_Rule implements Rule
{
    private Rule_Level_Helper $rule_level_helper;
    public function __construct(Rule_Level_Helper $rule_level_helper)
    {
        $this->rule_level_helper = $rule_level_helper;
    }
    public function get_node_type(): string
    {
        return Method_Callable_Node::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if (!$node->get_name() instanceof Node\Identifier) {
            return [];
        }
        $name = $node->get_name()->name;
        $type = $this->rule_level_helper->find_type_to_check($scope, $node->get_var(), '', static fn(Type $type): bool => $type->can_call_methods()->yes() && $type->has_method($name)->yes())->get_type();
        if ($type instanceof Error_Type || !$type->can_call_methods()->yes() || !$type->has_method($name)->yes()) {
            return [];
        }
        $method_reflection = $type->get_method($name, $scope);
        if ($method_reflection->is_static()) {
            return [Rule_Error_Builder::message(sprintf('Dynamic call to static method %s::%s().', $method_reflection->get_declaring_class()->get_display_name(), $method_reflection->get_name()))->identifier('staticMethod.dynamicCall')->build()];
        }
        return [];
    }
}