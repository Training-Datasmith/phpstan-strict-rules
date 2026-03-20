<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Classes;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Static_Call;
use Php_Parser\Node\Name;
use Php_Parser\Node\Stmt\Class_Method;
use Php_Stan\Analyser\Scope;
use Php_Stan\Better_Reflection\Reflection\Adapter\ReflectionClass;
use Php_Stan\Better_Reflection\Reflection\Adapter\Reflection_Enum;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Should_Not_Happen_Exception;
use function property_exists;
use function sprintf;
/**
 * @implements Rule<ClassMethod>
 */
class Require_Parent_Construct_Call_Rule implements Rule
{
    public function get_node_type(): string
    {
        return Class_Method::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if (!$scope->is_in_class()) {
            throw new Should_Not_Happen_Exception();
        }
        if ($scope->is_in_trait()) {
            return [];
        }
        if ($node->name->name !== '__construct') {
            return [];
        }
        if ($node->is_abstract()) {
            return [];
        }
        $class_reflection = $scope->get_class_reflection()->get_native_reflection();
        if ($class_reflection->is_interface() || $class_reflection->is_anonymous()) {
            return [];
        }
        if ($this->calls_parent_construct($node)) {
            return [];
        }
        $parent_class = $this->get_parent_constructor_class($class_reflection);
        if ($parent_class !== false) {
            return [Rule_Error_Builder::message(sprintf('%s::__construct() does not call parent constructor from %s.', $class_reflection->get_name(), $parent_class->get_name()))->identifier('constructor.missingParentCall')->build()];
        }
        return [];
    }
    private function calls_parent_construct(Node $parser_node): bool
    {
        if (!property_exists($parser_node, 'stmts')) {
            return false;
        }
        foreach ($parser_node->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Expression) {
                $statement = $statement->expr;
            }
            $statement = $this->ignore_error_suppression($statement);
            if ($statement instanceof Static_Call) {
                if ($statement->class instanceof Name && (string) $statement->class === 'parent' && $statement->name instanceof Node\Identifier && $statement->name->name === '__construct') {
                    return true;
                }
            } else if ($this->calls_parent_construct($statement)) {
                return true;
            }
        }
        return false;
    }
    /**
     * @param ReflectionClass|ReflectionEnum $classReflection
     * @return ReflectionClass|false
     */
    private function get_parent_constructor_class($class_reflection)
    {
        $parent_class = $class_reflection->get_parent_class();
        while ($parent_class !== false) {
            $constructor = $parent_class->has_method('__construct') ? $parent_class->get_method('__construct') : null;
            $constructor_with_class_name = $parent_class->has_method($parent_class->get_name()) ? $parent_class->get_method($parent_class->get_name()) : null;
            if ($constructor !== null && $constructor->get_declaring_class()->get_name() === $parent_class->get_name() && !$constructor->is_abstract() && !$constructor->is_private() && !$constructor->is_deprecated() || $constructor_with_class_name !== null && $constructor_with_class_name->get_declaring_class()->get_name() === $parent_class->get_name() && !$constructor_with_class_name->is_abstract()) {
                return $parent_class;
            }
            $parent_class = $parent_class->get_parent_class();
        }
        return false;
    }
    private function ignore_error_suppression(Node $statement): Node
    {
        if ($statement instanceof Node\Expr\Error_Suppress) {
            return $statement->expr;
        }
        return $statement;
    }
}