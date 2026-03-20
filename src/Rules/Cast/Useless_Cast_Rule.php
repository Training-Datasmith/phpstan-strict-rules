<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Cast;

use Php_Parser\Node;
use Php_Parser\Node\Expr\Cast;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule;
use Php_Stan\Rules\Rule_Error_Builder;
use Php_Stan\Type\Error_Type;
use Php_Stan\Type\Generalize_Precision;
use Php_Stan\Type\Verbosity_Level;
use function sprintf;
/**
 * @implements Rule<Cast>
 */
class Useless_Cast_Rule implements Rule
{
    private bool $treat_php_doc_types_as_certain;
    private bool $treat_php_doc_types_as_certain_tip;
    public function __construct(bool $treat_php_doc_types_as_certain, bool $treat_php_doc_types_as_certain_tip)
    {
        $this->treat_php_doc_types_as_certain = $treat_php_doc_types_as_certain;
        $this->treat_php_doc_types_as_certain_tip = $treat_php_doc_types_as_certain_tip;
    }
    public function get_node_type(): string
    {
        return Cast::class;
    }
    public function process_node(Node $node, Scope $scope): array
    {
        $cast_type = $scope->get_type($node);
        if ($cast_type instanceof Error_Type) {
            return [];
        }
        $cast_type = $cast_type->generalize(Generalize_Precision::less_specific());
        if ($this->treat_php_doc_types_as_certain) {
            $expression_type = $scope->get_type($node->expr);
        } else {
            $expression_type = $scope->get_native_type($node->expr);
        }
        if ($cast_type->is_super_type_of($expression_type)->yes()) {
            $add_tip = function (Rule_Error_Builder $rule_error_builder) use ($scope, $node, $cast_type): Rule_Error_Builder {
                if (!$this->treat_php_doc_types_as_certain) {
                    return $rule_error_builder;
                }
                if (!$this->treat_php_doc_types_as_certain_tip) {
                    return $rule_error_builder;
                }
                $expression_type_without_php_doc = $scope->get_native_type($node->expr);
                if ($cast_type->is_super_type_of($expression_type_without_php_doc)->yes()) {
                    return $rule_error_builder;
                }
                return $rule_error_builder->treat_php_doc_types_as_certain_tip();
            };
            return [$add_tip(Rule_Error_Builder::message(sprintf('Casting to %s something that\'s already %s.', $cast_type->describe(Verbosity_Level::type_only()), $expression_type->describe(Verbosity_Level::type_only()))))->identifier('cast.useless')->build()];
        }
        return [];
    }
}