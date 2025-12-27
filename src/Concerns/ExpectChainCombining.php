<?php

declare(strict_types=1);

namespace RectorPest\Concerns;

use PhpParser\Comment;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;

/**
 * Shared logic for chaining expect() calls across multiple rectors.
 *
 * Used by:
 * - ChainSameVariableExpectCallsRector
 * - ChainDifferentVariableExpectCallsRector
 */
trait ExpectChainCombining
{
    /**
     * Data object representing a pair of consecutive expect chains that can be merged.
     *
     * @return array{key: int, stmt: Expression, methodCall: MethodCall, expectArg: Expr, nextStmt: Expression, nextMethodCall: MethodCall, nextExpectArg: Expr}|null
     */
    protected function findMergeableExpectPair(array $stmts, int $key): ?array
    {
        if (! isset($stmts[$key]) || ! isset($stmts[$key + 1])) {
            return null;
        }

        $stmt = $stmts[$key];
        if (! $stmt instanceof Expression) {
            return null;
        }

        if (! $stmt->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $stmt->expr;
        if (! $this->isExpectChain($methodCall)) {
            return null;
        }

        $expectArg = $this->getExpectArgument($methodCall);
        if (! $expectArg instanceof Expr) {
            return null;
        }

        $nextStmt = $stmts[$key + 1];
        if (! $nextStmt instanceof Expression) {
            return null;
        }

        if (! $nextStmt->expr instanceof MethodCall) {
            return null;
        }

        $nextMethodCall = $nextStmt->expr;
        if (! $this->isExpectChain($nextMethodCall)) {
            return null;
        }

        $nextExpectArg = $this->getExpectArgument($nextMethodCall);
        if (! $nextExpectArg instanceof Expr) {
            return null;
        }

        // Don't merge across comments — preserve explicit separation
        $currentComments = (array) $stmt->getAttribute('comments', []);
        $nextComments = (array) $nextStmt->getAttribute('comments', []);
        if ($currentComments !== [] || $nextComments !== []) {
            return null;
        }

        return [
            'key' => $key,
            'stmt' => $stmt,
            'methodCall' => $methodCall,
            'expectArg' => $expectArg,
            'nextStmt' => $nextStmt,
            'nextMethodCall' => $nextMethodCall,
            'nextExpectArg' => $nextExpectArg,
        ];
    }

    /**
     * Filter out empty comments from a collected comments array.
     *
     * @param array<Comment|mixed> $comments
     * @return array<Comment>
     */
    protected function filterComments(array $comments): array
    {
        if ($comments === []) {
            return [];
        }

        return array_values(array_filter($comments, function ($c): bool {
            if (! is_object($c)) {
                return false;
            }

            if (method_exists($c, 'getText')) {
                $text = $c->getText();

                return is_string($text) && trim($text) !== '';
            }

            return true;
        }));
    }

    /**
     * Preserve comments on a merged statement.
     *
     * @param array<Comment|mixed> $collectedComments
     */
    protected function preserveComments(Expression $stmt, array $collectedComments): void
    {
        $filtered = $this->filterComments($collectedComments);
        if ($filtered !== []) {
            $stmt->setAttribute('comments', $filtered);
        }
    }

    /**
     * Build a chained call by appending second's methods to first.
     */
    protected function buildChainedCall(MethodCall $first, MethodCall $second): MethodCall
    {
        $secondMethods = $this->collectChainMethods($second);
        $result = $this->rebuildMethodChain($first, $secondMethods);

        /** @var MethodCall $result */
        return $result;
    }
}
