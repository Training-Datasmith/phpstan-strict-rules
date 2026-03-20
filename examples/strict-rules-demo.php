<?php

declare(strict_types=1);

/**
 * Example: Code patterns that phpstan-strict-rules flags.
 *
 * Install:
 *   composer require --dev phpstan/phpstan-strict-rules
 *
 * Add to phpstan.neon:
 *   includes:
 *     - vendor/phpstan/phpstan-strict-rules/rules.neon
 *
 * All rules are enabled by default. Disable individually:
 *
 *   parameters:
 *     strictRules:
 *       disallowedLooseComparison: false  # allow == and !=
 *       allRules: true                    # master switch
 */

// --- Loose comparisons (strictRules.disallowedLooseComparison) ---

function checkStatus(string $status): bool
{
    // Flagged: use === instead of ==
    // return $status == 'active';

    return $status === 'active'; // correct
}


// --- empty() usage (strictRules.disallowedEmpty) ---

function hasItems(array $list): bool
{
    // Flagged: empty() has surprising behavior with non-array types
    // return !empty($list);

    return $list !== []; // correct
}


// --- array_filter without callback (strictRules.requireParentConstructorCall equivalent) ---

function filterPositive(array $numbers): array
{
    // Flagged: no callback means implicit boolean cast — surprising with "0", "", false, null
    // return array_filter($numbers);

    return array_filter($numbers, static fn(int $n): bool => $n > 0); // correct
}


// --- in_array without strict mode (strictRules.strictCalls) ---

function isAdmin(string $role, array $roles): bool
{
    // Flagged: in_array without third argument uses loose comparison
    // return in_array($role, $roles);

    return in_array($role, $roles, true); // correct — strict mode
}


// --- Boolean in condition (strictRules.booleansInConditions) ---

function processIfReady(int $count): void
{
    // Flagged: $count is int, not bool — use explicit comparison
    // if ($count) { ... }

    if ($count > 0) { // correct
        echo 'Ready';
    }
}


// --- Variable variables (strictRules.disallowedVariableVariables) ---

function getProperty(object $obj, string $prop): mixed
{
    // Flagged: variable property access is impossible to statically analyze
    // return $obj->$prop;

    // Use a lookup array or match expression instead:
    return match ($prop) {
        'name' => $obj->name ?? null,  // explicit
        default => null,
    };
}
