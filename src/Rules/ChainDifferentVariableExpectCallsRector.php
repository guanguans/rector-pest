<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Chains multiple expect() calls on different variables using ->and().
 *
 * This rule ONLY handles different-variable bridging with ->and().
 * For same-variable chaining, see ChainSameVariableExpectCallsRector.
 */
final class ChainDifferentVariableExpectCallsRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Chains multiple expect() calls on different variables using ->and()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10);
expect($b)->toBe(20);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10)->and($b)->toBe(20);
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($value1)->toBe(10);
expect($value2)->toBe(20);
expect($value3)->toBe(30);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value1)->toBe(10)->and($value2)->toBe(20)->and($value3)->toBe(30);
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }

    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! property_exists($node, 'stmts') || $node->stmts === null) {
            return null;
        }

        /** @var array<Node\Stmt> $stmts */
        $stmts = $node->stmts;
        $hasChanged = false;

        do {
            $changedInPass = false;

            foreach ($stmts as $key => $stmt) {
                if (! is_int($key)) {
                    continue;
                }

                if (! $stmt instanceof Expression) {
                    continue;
                }

                if (! $stmt->expr instanceof MethodCall) {
                    continue;
                }

                $methodCall = $stmt->expr;
                if (! $this->isExpectChain($methodCall)) {
                    continue;
                }

                $firstExpectArg = $this->getExpectArgument($methodCall);
                if (! $firstExpectArg instanceof Expr) {
                    continue;
                }

                if (! isset($stmts[$key + 1])) {
                    continue;
                }

                $nextStmt = $stmts[$key + 1];
                if (! $nextStmt instanceof Expression) {
                    continue;
                }

                if (! $nextStmt->expr instanceof MethodCall) {
                    continue;
                }

                $nextMethodCall = $nextStmt->expr;
                if (! $this->isExpectChain($nextMethodCall)) {
                    continue;
                }

                $nextExpectArg = $this->getExpectArgument($nextMethodCall);
                if (! $nextExpectArg instanceof Expr) {
                    continue;
                }

                // don't merge across comments — preserve explicit separation
                $currentComments = (array) $stmt->getAttribute('comments', []);
                $nextComments = (array) $nextStmt->getAttribute('comments', []);
                if ($currentComments !== []) {
                    continue;
                }

                if ($nextComments !== []) {
                    continue;
                }

                // Only handle different variables - same variables should use ChainSameVariableExpectCallsRector
                if ($this->nodeComparator->areNodesEqual($firstExpectArg, $nextExpectArg)) {
                    continue;
                }

                // Try to merge the following expect() chains into a single ->and(...) chain
                if ($this->mergeDifferentVariableChains($stmts, $key)) {
                    $hasChanged = true;
                    $changedInPass = true;

                    break;
                }
            }
        } while ($changedInPass);

        if (! $hasChanged) {
            return null;
        }

        $node->stmts = $stmts;

        return $node;
    }

    /**
     * @param array<Node\Stmt> $stmts
     */
    private function mergeDifferentVariableChains(array &$stmts, int $key): bool
    {
        /** @var Expression $exprStmt */
        $exprStmt = $stmts[$key];
        /** @var Expression $nextExprStmt */
        $nextExprStmt = $stmts[$key + 1];

        $firstMethodCall = $exprStmt->expr;
        $nextMethodCall = $nextExprStmt->expr;

        /** @var MethodCall $firstMethodCall */
        /** @var MethodCall $nextMethodCall */

        $targetExpectArg = $this->getExpectArgument($nextMethodCall);
        if (! $targetExpectArg instanceof Expr) {
            return false;
        }

        $collectIndex = $key + 1;
        $allSecondMethods = [];
        $collectedComments = (array) $exprStmt->getAttribute('comments', []);

        while (isset($stmts[$collectIndex])) {
            $currStmt = $stmts[$collectIndex];

            if (! $currStmt instanceof Expression) {
                break;
            }

            // stop merging further when there are comments on this statement
            $currComments = (array) $currStmt->getAttribute('comments', []);
            if ($currComments !== []) {
                break;
            }

            if (! $currStmt->expr instanceof MethodCall) {
                break;
            }

            $currMethodCall = $currStmt->expr;
            /** @var MethodCall $currMethodCall */
            if (! $this->isExpectChain($currMethodCall)) {
                break;
            }

            $currExpectArg = $this->getExpectArgument($currMethodCall);
            if (! $currExpectArg instanceof Expr) {
                break;
            }

            if (! $this->nodeComparator->areNodesEqual($targetExpectArg, $currExpectArg)) {
                break;
            }

            $methods = $this->collectChainMethods($currMethodCall);
            $allSecondMethods = array_merge($allSecondMethods, $methods);

            // collect comments from statements we are removing
            $collectedComments = array_merge($collectedComments, (array) $currStmt->getAttribute('comments', []));

            unset($stmts[$collectIndex]);
            $collectIndex++;
        }

        if ($allSecondMethods === []) {
            return false;
        }

        $andArg = new Arg($targetExpectArg);
        $andCall = new MethodCall($firstMethodCall, 'and', [$andArg]);

        $result = $this->rebuildMethodChain($andCall, $allSecondMethods);

        $exprStmt->expr = $result;

        // attach collected comments to the merged statement (filter out empty ones)
        if ($collectedComments !== []) {
            $filtered = array_values(array_filter($collectedComments, function ($c): bool {
                if (! is_object($c)) {
                    return false;
                }

                if (method_exists($c, 'getText')) {
                    $text = $c->getText();
                    return is_string($text) && trim($text) !== '';
                }

                return true;
            }));

            if ($filtered !== []) {
                $exprStmt->setAttribute('comments', $filtered);
            }
        }

        $stmts = array_values($stmts);

        return true;
    }
}
