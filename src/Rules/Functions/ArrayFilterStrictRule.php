<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Functions;

use function count;
use Php_Parser\Node;
use Php_Parser\Node\Expr\Func_Call;
use Php_Parser\Node\Name;
use Php_Stan\Analyser\Arguments_Normalizer;
use Php_Stan\Analyser\Scope;
use Php_Stan\Reflection\Parameters_Acceptor_Selector;
use Php_Stan\Reflection\Reflection_Provider;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Type;
use Php_Stan\Type\Union_Type;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<FuncCall>
 */
class Array_Filter_Strict_Rule implements Rule
{
    private Reflection_Provider $reflection_provider;
    private bool $treat_php_doc_types_as_certain;
    private bool $check_nullables;
    private bool $treat_php_doc_types_as_certain_tip;
    public function __construct(Reflection_Provider $reflection_provider, bool $treat_php_doc_types_as_certain, bool $check_nullables, bool $treat_php_doc_types_as_certain_tip)
    {
        $this->reflection_provider = $reflection_provider;
        $this->treat_php_doc_types_as_certain = $treat_php_doc_types_as_certain;
        $this->check_nullables = $check_nullables;
        $this->treat_php_doc_types_as_certain_tip = $treat_php_doc_types_as_certain_tip;
    }
    public function get_node_type(): string
    {
        return Func_Call::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Name) {
            return [];
        }
        if (!$this->reflection_provider->has_function($node->name, $scope)) {
            return [];
        }
        $function_reflection = $this->reflection_provider->get_function($node->name, $scope);
        if ($function_reflection->get_name() !== 'array_filter') {
            return [];
        }
        $parameters_acceptor = Parameters_Acceptor_Selector::select_from_args($scope, $node->get_args(), $function_reflection->get_variants(), $function_reflection->get_named_arguments_variants());
        $normalized_func_call = Arguments_Normalizer::reorder_func_arguments($parameters_acceptor, $node);
        if ($normalized_func_call === null) {
            return [];
        }
        $args = $normalized_func_call->get_args();
        if (count($args) === 0) {
            return [];
        }
        if (count($args) === 1) {
            $array_type = $scope->get_type($args[0]->value);
            $item_type = $array_type->get_iterable_value_type();
            if ($item_type instanceof Union_Type) {
                $has_truthy = false;
                $has_falsey = false;
                foreach ($item_type->get_types() as $inner_type) {
                    $boolean_type = $inner_type->to_boolean();
                    if ($boolean_type->is_true()->yes()) {
                        $has_truthy = true;
                        continue;
                    }
                    if ($boolean_type->is_false()->yes()) {
                        $has_falsey = true;
                        continue;
                    }
                    $has_truthy = false;
                    $has_falsey = false;
                    break;
                }
                if ($has_truthy && $has_falsey) {
                    return [];
                }
            } elseif ($item_type->is_boolean()->yes()) {
                return [];
            } elseif ($item_type->is_array()->yes()) {
                return [];
            }
            return [Rule_Error_Builder::message('Call to function array_filter() requires parameter #2 to be passed to avoid loose comparison semantics.')->identifier('arrayFilter.strict')->build()];
        }
        $native_callback_type = $scope->get_native_type($args[1]->value);
        if ($this->treat_php_doc_types_as_certain) {
            $callback_type = $scope->get_type($args[1]->value);
        } else {
            $callback_type = $native_callback_type;
        }
        if ($this->is_callback_type_null($callback_type)) {
            $message = 'Parameter #2 of array_filter() cannot be null to avoid loose comparison semantics (%s given).';
            $error_builder = Rule_Error_Builder::message(sprintf($message, $callback_type->describe(Verbosity_Level::type_only())))->identifier('arrayFilter.strict');
            if ($this->treat_php_doc_types_as_certain_tip && !$this->is_callback_type_null($native_callback_type) && $this->treat_php_doc_types_as_certain) {
                $error_builder->treat_php_doc_types_as_certain_tip();
            }
            return [$error_builder->build()];
        }
        return [];
    }
    private function is_callback_type_null(Type $callback_type): bool
    {
        if ($callback_type->is_null()->yes()) {
            return true;
        }
        if ($callback_type->is_null()->no()) {
            return false;
        }
        return $this->check_nullables;
    }
}