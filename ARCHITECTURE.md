# Architecture: phpstan-strict-rules

## Purpose

A PHPStan extension that adds opinionated, strict-typing rules beyond what PHPStan checks
by default. Targets developers who want no implicit type coercions, no loose comparisons,
and no PHP footguns like `empty()`, backtick operators, or variable variables.

## Directory Structure

```
src/Rules/
  BooleansInConditions/    # Require boolean expressions in if/while/do-while/ternary/&&/||/!
    Boolean_Rule_Helper.php  # Shared: checks if an expression evaluates to a boolean
    Boolean_In_*_Rule.php    # One rule per operator/statement type
  Cast/
    Useless_Cast_Rule.php    # Reports casts that have no effect (e.g., (int) on an int)
  Classes/
    Require_Parent_Construct_Call_Rule.php  # Ensures parent::__construct() is called
  DisallowedConstructs/
    Disallowed_Backtick_Rule.php             # Bans backtick execution operator
    Disallowed_Empty_Rule.php                # Bans empty() — use === [] or count() === 0
    Disallowed_Implicit_Array_Creation_Rule.php  # Bans $arr[] = x on undefined variable
    Disallowed_Loose_Comparison_Rule.php     # Bans == and != in favor of === and !==
    Disallowed_Short_Ternary_Rule.php        # Bans ?: (Elvis operator)
  ForLoop/
    Overwrite_Variables_With_For_Loop_Init_Rule.php  # Warns when for-init overwrites outer var
  ForeachLoop/
    Overwrite_Variables_With_Foreach_Rule.php  # Warns when foreach key/value shadows outer var
  Functions/
    Array_Filter_Strict_Rule.php    # Requires callback in array_filter() — no implicit boolean cast
    Closure_Uses_This_Rule.php      # Disallows use($this) in closures — use arrow functions
  Methods/
    Illegal_Constructor_Method_Call_Rule.php   # Bans calling __construct() directly (not via new)
    Illegal_Constructor_Static_Call_Rule.php   # Bans Foo::__construct() static calls
    Wrong_Case_Of_Inherited_Method_Rule.php    # Enforces consistent method name casing
  Operators/
    Operand_In_Arithmetic_*_Rule.php  # One rule per arithmetic operator — enforces numeric operands
    Operator_Rule_Helper.php          # Shared: checks if a type is numeric
  StrictCalls/
    Dynamic_Call_On_Static_Methods_Rule.php  # Bans calling static methods via instance
    Strict_Function_Calls_Rule.php           # Requires strict-mode flag in in_array(), array_search()
  SwitchConditions/
    Matching_Type_In_Switch_Case_Condition_Rule.php  # Requires case values to match switch type
  VariableVariables/
    Variable_*_Rule.php  # Bans $$var, $obj->$method(), Foo::$$prop, etc.
tests/            # Rule tests mirroring src/ structure
rules.neon        # Extension config: PHPStan defaults + toggleable rules via conditionalTags
```

## Key Design Decisions

### One Rule Per Operator

Boolean-in-condition rules and arithmetic-operand rules each have a distinct class per
operator or statement. This allows users to disable individual rules (e.g., allow `??:`)
without affecting others. The granularity is intentional even though it produces boilerplate.

### Configuration-Driven Toggles

`rules.neon` uses PHPStan's `conditionalTags` mechanism: each rule is associated with a
`strictRules.*` parameter. Setting a parameter to `false` removes the rule's service tag,
effectively disabling it. The `allRules: true` default enables everything, but individual
parameters take precedence.

### Shared Helpers via DI

`Boolean_Rule_Helper` and `Operator_Rule_Helper` encapsulate the type-checking logic and
are injected into all rules that need them. This keeps individual rule classes focused on
which AST node to inspect, not how to evaluate types.

## Extension Points

All rules are opt-in or opt-out via `strictRules.*` parameters in `phpstan.neon`. No public
extension interfaces are provided — this extension is intentionally self-contained.

## Dependency Flow

```
rules.neon
  ├─ Boolean_Rule_Helper → Rule_Level_Helper (PHPStan core)
  │    └─ Boolean_In_*_Rule (8 rules) → Boolean_Rule_Helper
  ├─ Operator_Rule_Helper → Rule_Level_Helper
  │    └─ Operand_In_Arithmetic_*_Rule (7 rules) → Operator_Rule_Helper
  └─ Strict_Function_Calls_Rule (standalone)
```
