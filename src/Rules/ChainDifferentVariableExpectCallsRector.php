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
use RectorPest\Concerns\ExpectChainCombining;
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
    use ExpectChainCombining;

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

                $pair = $this->findMergeableExpectPair($stmts, $key);
                if ($pair === null) {
                    continue;
                }

                // Only handle different variables - same variables should use ChainSameVariableExpectCallsRector
                if ($this->nodeComparator->areNodesEqual($pair['expectArg'], $pair['nextExpectArg'])) {
                    continue;
                }

                // Try to merge the following expect() chains into a single ->and(...) chain
                if ($this->mergeDifferentVariableChains($stmts, $pair)) {
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
     * @param array{key: int, stmt: Expression, methodCall: MethodCall, expectArg: Expr, nextStmt: Expression, nextMethodCall: MethodCall, nextExpectArg: Expr} $pair
     */
    private function mergeDifferentVariableChains(array &$stmts, array $pair): bool
    {
        $exprStmt = $pair['stmt'];
        $firstMethodCall = $pair['methodCall'];
        $targetExpectArg = $pair['nextExpectArg'];
        $key = $pair['key'];

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

        $this->preserveComments($exprStmt, $collectedComments);

        $stmts = array_values($stmts);

        return true;
    }
}
