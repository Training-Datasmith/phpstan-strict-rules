<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Methods;

use Php_Parser\Node;
use Php_Stan\Analyser\Scope;
use Php_Stan\Node\In_Class_Method_Node;
use Php_Stan\Reflection\Class_Reflection;
use Php_Stan\Rules\Identifier_Rule_Error;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use function sprintf;
/**
 * @implements Rule<InClassMethodNode>
 */
class Wrong_Case_Of_Inherited_Method_Rule implements Rule
{
    public function get_node_type(): string
    {
        return In_Class_Method_Node::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        $method_reflection = $node->get_method_reflection();
        $declaring_class = $method_reflection->get_declaring_class();
        $messages = [];
        if ($declaring_class->get_parent_class() !== null) {
            $parent_message = $this->find_method($declaring_class, $declaring_class->get_parent_class(), $method_reflection->get_name());
            if ($parent_message !== null) {
                $messages[] = $parent_message;
            }
        }
        foreach ($declaring_class->get_interfaces() as $interface) {
            $interface_message = $this->find_method($declaring_class, $interface, $method_reflection->get_name());
            if ($interface_message === null) {
                continue;
            }
            $messages[] = $interface_message;
        }
        return $messages;
    }
    private function find_method(Class_Reflection $declaring_class, Class_Reflection $class_reflection, string $method_name): ?Identifier_Rule_Error
    {
        if (!$class_reflection->has_native_method($method_name)) {
            return null;
        }
        $parent_method = $class_reflection->get_native_method($method_name);
        if ($parent_method->get_name() === $method_name) {
            return null;
        }
        return Rule_Error_Builder::message(sprintf('Method %s::%s() does not match %s method name: %s::%s().', $declaring_class->get_display_name(), $method_name, $class_reflection->is_interface() ? 'interface' : 'parent', $class_reflection->get_display_name(), $parent_method->get_name()))->identifier('method.nameCase')->build();
    }
}