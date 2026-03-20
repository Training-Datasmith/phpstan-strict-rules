<?php

declare (strict_types=1);
namespace Php_Stan\Rules\Operators;

use Php_Parser\Node\Expr;
use Php_Stan\Analyser\Scope;
use Php_Stan\Rules\Rule_Level_Helper;
use Php_Stan\Type\Accessory\Accessory_Numeric_String_Type;
use Php_Stan\Type\Benevolent_Union_Type;
use Php_Stan\Type\Error_Type;
use Php_Stan\Type\Float_Type;
use Php_Stan\Type\Integer_Type;
use Php_Stan\Type\Intersection_Type;
use Php_Stan\Type\Mixed_Type;
use Php_Stan\Type\String_Type;
use Php_Stan\Type\Type;
use Php_Stan\Type\Union_Type;
class Operator_Rule_Helper
{
    private Rule_Level_Helper $rule_level_helper;
    public function __construct(Rule_Level_Helper $rule_level_helper)
    {
        $this->rule_level_helper = $rule_level_helper;
    }
    public function is_valid_for_arithmetic_operation(Scope $scope, Expr $expr): bool
    {
        $type = $scope->get_type($expr);
        if ($type instanceof Mixed_Type) {
            return true;
        }
        // already reported by PHPStan core
        if ($type->to_number() instanceof Error_Type) {
            return true;
        }
        return $this->is_subtype_of_number($scope, $expr);
    }
    public function is_valid_for_increment(Scope $scope, Expr $expr): bool
    {
        $type = $scope->get_type($expr);
        if ($type instanceof Mixed_Type) {
            return true;
        }
        if ($type->is_string()->yes()) {
            // Because `$a = 'a'; $a++;` is valid
            return true;
        }
        return $this->is_subtype_of_number($scope, $expr);
    }
    public function is_valid_for_decrement(Scope $scope, Expr $expr): bool
    {
        $type = $scope->get_type($expr);
        if ($type instanceof Mixed_Type) {
            return true;
        }
        return $this->is_subtype_of_number($scope, $expr);
    }
    private function is_subtype_of_number(Scope $scope, Expr $expr): bool
    {
        $accepted_type = new Union_Type([new Integer_Type(), new Float_Type(), new Intersection_Type([new String_Type(), new Accessory_Numeric_String_Type()])]);
        $type = $this->rule_level_helper->find_type_to_check($scope, $expr, '', static fn(Type $type): bool => $accepted_type->is_super_type_of($type)->yes())->get_type();
        if ($type instanceof Error_Type) {
            return true;
        }
        $is_super_type = $accepted_type->is_super_type_of($type);
        if ($type instanceof Benevolent_Union_Type) {
            return !$is_super_type->no();
        }
        return $is_super_type->yes();
    }
}