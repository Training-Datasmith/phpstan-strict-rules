<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Strict_Calls;

use function array_key_exists;
use Php_Parser\Node;
use Php_Parser\Node\Expr\Func_Call;
use Php_Parser\Node\Name;
use Php_Stan\Analyser\Arguments_Normalizer;
use Php_Stan\Analyser\Scope;
use Php_Stan\Reflection\Parameters_Acceptor_Selector;
use Php_Stan\Reflection\Reflection_Provider;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Constant\Constant_Boolean_Type;
use function sprintf;
use function strtolower;
/**
 * @implements Rule<FuncCall>
 */
class Strict_Function_Calls_Rule implements Rule
{
    /** @var int[] */
    private array $function_arguments = ['in_array' => 2, 'array_search' => 2, 'base64_decode' => 1, 'array_keys' => 2];
    private Reflection_Provider $reflection_provider;
    public function __construct(Reflection_Provider $reflection_provider)
    {
        $this->reflection_provider = $reflection_provider;
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
        $function = $this->reflection_provider->get_function($node->name, $scope);
        $parameters_acceptor = Parameters_Acceptor_Selector::select_from_args($scope, $node->get_args(), $function->get_variants());
        $node = Arguments_Normalizer::reorder_func_arguments($parameters_acceptor, $node);
        if ($node === null) {
            return [];
        }
        $function_name = strtolower($function->get_name());
        if (!array_key_exists($function_name, $this->function_arguments)) {
            return [];
        }
        if ($function_name === 'array_keys' && !array_key_exists(1, $node->get_args())) {
            return [];
        }
        $argument_position = $this->function_arguments[$function_name];
        if (!array_key_exists($argument_position, $node->get_args())) {
            return [Rule_Error_Builder::message(sprintf('Call to function %s() requires parameter #%d to be set.', $function_name, $argument_position + 1))->identifier('function.strict')->build()];
        }
        $argument_type = $scope->get_type($node->get_args()[$argument_position]->value);
        $true_type = new Constant_Boolean_Type(true);
        if (!$true_type->is_super_type_of($argument_type)->yes()) {
            return [Rule_Error_Builder::message(sprintf('Call to function %s() requires parameter #%d to be true.', $function_name, $argument_position + 1))->identifier('function.strict')->build()];
        }
        return [];
    }
}