<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Methods;

use function array_key_exists;
use function array_map;
use function in_array;
use Php_Parser\Node;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use function sprintf;
use function strtolower;
/**
 * @implements Rule<Node\Expr\StaticCall>
 */
final class Illegal_Constructor_Static_Call_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Node\Expr\Static_Call::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier || $node->name->to_lower_string() !== '__construct') {
            return [];
        }
        if ($this->is_collect_calling_constructor($node, $scope)) {
            return [];
        }
        return [Rule_Error_Builder::message('Static call to __construct() is only allowed on a parent class in the constructor.')->identifier('constructor.call')->build()];
    }
    private function is_collect_calling_constructor(Node\Expr\Static_Call $node, Scope $scope): bool
    {
        // __construct should be called from inside constructor
        if ($scope->get_function() === null) {
            return false;
        }
        if ($scope->get_function()->get_name() !== '__construct') {
            if (!$this->is_in_renamed_trait_constructor($scope)) {
                return false;
            }
        }
        if (!$scope->is_in_class()) {
            return false;
        }
        if (!$node->class instanceof Node\Name) {
            return false;
        }
        $parent_classes = array_map(static fn(string $name): string => strtolower($name), $scope->get_class_reflection()->get_parent_classes_names());
        return in_array(strtolower($scope->resolve_name($node->class)), $parent_classes, true);
    }
    private function is_in_renamed_trait_constructor(Scope $scope): bool
    {
        if (!$scope->is_in_class()) {
            return false;
        }
        if (!$scope->is_in_trait()) {
            return false;
        }
        if ($scope->get_function() === null) {
            return false;
        }
        $trait_aliases = $scope->get_class_reflection()->get_native_reflection()->get_trait_aliases();
        $function_name = $scope->get_function()->get_name();
        if (!array_key_exists($function_name, $trait_aliases)) {
            return false;
        }
        return $trait_aliases[$function_name] === sprintf('%s::%s', $scope->get_trait_reflection()->get_name(), '__construct');
    }
}