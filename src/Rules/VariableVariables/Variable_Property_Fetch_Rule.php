<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Variable_Variables;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Property_Fetch;
use Php_Stan\Analyser\Scope;
use Php_Stan\Reflection\Class_Reflection;
use Php_Stan\Reflection\Reflection_Provider;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Verbosity_Level;
use Simple_Xml_Element;
use function sprintf;
/**
 * @implements Rule<PropertyFetch>
 */
class Variable_Property_Fetch_Rule implements Rule
{
    private Reflection_Provider $reflection_provider;
    /** @var string[] */
    private array $universal_object_crates_classes;
    /**
     * @param string[] $universalObjectCratesClasses
     */
    public function __construct(Reflection_Provider $reflection_provider, array $universal_object_crates_classes)
    {
        $this->reflection_provider = $reflection_provider;
        $this->universal_object_crates_classes = $universal_object_crates_classes;
    }
    public function get_node_type(): string
    {
        return Property_Fetch::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if ($node->name instanceof Node\Identifier) {
            return [];
        }
        if ($scope->get_type($node->name)->is_literal_string()->yes()) {
            return [];
        }
        $fetched_on_type = $scope->get_type($node->var);
        foreach ($fetched_on_type->get_object_class_names() as $referenced_class) {
            if (!$this->reflection_provider->has_class($referenced_class)) {
                continue;
            }
            $class_reflection = $this->reflection_provider->get_class($referenced_class);
            if ($this->is_universal_object_crate($class_reflection) || $this->is_simple_xml_element($class_reflection)) {
                return [];
            }
        }
        return [Rule_Error_Builder::message(sprintf('Variable property access on %s.', $fetched_on_type->describe(Verbosity_Level::type_only())))->identifier('property.dynamicName')->build()];
    }
    private function is_simple_xml_element(Class_Reflection $class_reflection): bool
    {
        return $class_reflection->is(Simple_Xml_Element::class);
    }
    private function is_universal_object_crate(Class_Reflection $class_reflection): bool
    {
        foreach ($this->universal_object_crates_classes as $class_name) {
            if (!$this->reflection_provider->has_class($class_name)) {
                continue;
            }
            if ($class_reflection->is($class_name)) {
                return true;
            }
        }
        return false;
    }
}