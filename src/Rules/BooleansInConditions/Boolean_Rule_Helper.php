<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Booleans_In_Conditions;

use Php_Parser\Node\Expr;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule_Level_Helper;
use Php_Stan\Type\Error_Type;
use Php_Stan\Type\Mixed_Type;
use Php_Stan\Type\Type;
class Boolean_Rule_Helper
{
    private Rule_Level_Helper $rule_level_helper;
    public function __construct(Rule_Level_Helper $rule_level_helper)
    {
        $this->rule_level_helper = $rule_level_helper;
    }
    public function passes_as_boolean(Scope $scope, Expr $expr): bool
    {
        $type = $scope->get_type($expr);
        if ($type instanceof Mixed_Type) {
            return !$type->is_explicit_mixed();
        }
        $type_to_check = $this->rule_level_helper->find_type_to_check($scope, $expr, '', static fn(Type $type): bool => $type->is_boolean()->yes());
        $found_type = $type_to_check->get_type();
        if ($found_type instanceof Error_Type) {
            return true;
        }
        return $found_type->is_boolean()->yes();
    }
}