<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPest\AbstractRector;
use RectorPest\Concerns\ExpectChainCombining;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Chains multiple expect() calls on the same variable into a single chained expectation.
 *
 * This rule ONLY handles same-variable chaining. For different-variable bridging
 * with ->and(), see ChainDifferentVariableExpectCallsRector.
 */
final class ChainSameVariableExpectCallsRector extends AbstractRector
{
    use ExpectChainCombining;

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Chains multiple expect() calls on the same variable into a single chained expectation',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10);
expect($a)->toBeInt();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10)->toBeInt();
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($value)->toBe(10);
expect($value)->toBeInt();
expect($value)->toBeGreaterThan(5);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBe(10)->toBeInt()->toBeGreaterThan(5);
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

                // Only handle same variable - different variables should use ChainDifferentVariableExpectCallsRector
                if (! $this->nodeComparator->areNodesEqual($pair['expectArg'], $pair['nextExpectArg'])) {
                    continue;
                }

                $this->mergeSameVariable($stmts, $pair);

                $hasChanged = true;
                $changedInPass = true;

                break;
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
     * @param array{key: int, stmt: Expression, methodCall: \PhpParser\Node\Expr\MethodCall, expectArg: \PhpParser\Node\Expr, nextStmt: Expression, nextMethodCall: \PhpParser\Node\Expr\MethodCall, nextExpectArg: \PhpParser\Node\Expr} $pair
     */
    private function mergeSameVariable(array &$stmts, array $pair): void
    {
        $exprStmt = $pair['stmt'];
        $nextExprStmt = $pair['nextStmt'];
        $key = $pair['key'];

        $exprStmt->expr = $this->buildChainedCall($pair['methodCall'], $pair['nextMethodCall']);

        // preserve comments from the removed statement(s)
        $collectedComments = (array) $exprStmt->getAttribute('comments', []);
        $collectedComments = array_merge($collectedComments, (array) $nextExprStmt->getAttribute('comments', []));

        unset($stmts[$key + 1]);
        $stmts = array_values($stmts);

        $this->preserveComments($exprStmt, $collectedComments);
    }
}
