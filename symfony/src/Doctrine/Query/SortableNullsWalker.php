<?php
/**
 * Custom Doctrine query walker for PostgreSQL NULL FIRST or NULL LAST order.
 *
 * How to use:
 * ATTENTION! To be functional the walker must be defined at the end on generated query!
 *
 * use App\Doctrine\Query\SortableNullsWalker;
 *
 * // add hint to query
 * $query = $qbl->getQuery();
 * $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class);
 * // add NULLS FIRST/LAST hint for necessary columns
 * $query->setHint(SortableNullsWalker::HINT, [
 *     't.column1' => SortableNullsWalker::NULLS_FIRST,
 *     't.column2' => SortableNullsWalker::NULLS_LAST,
 *     etc...
 * ]);
 *
 * return $query->execute();
 */

namespace App\Doctrine\Query;

use Doctrine\ORM\Query;

class SortableNullsWalker extends Query\SqlWalker
{

    const HINT = 'sortableNulls.fields';

    const NULLS_FIRST = 'NULLS FIRST';
    const NULLS_LAST = 'NULLS LAST';


    /**
     * @param Query\AST\OrderByItem $orderByItem
     *
     * @return mixed|string
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function walkOrderByItem($orderByItem)
    {
        $sql  = parent::walkOrderByItem($orderByItem);
        $hint = $this->getQuery()->getHint(self::HINT);
        $expr = $orderByItem->expression;
        $type = strtoupper($orderByItem->type);

        if (is_array($hint) && count($hint)) {
            // check for a state field
            if (
                $expr instanceof Query\AST\PathExpression &&
                $expr->type == Query\AST\PathExpression::TYPE_STATE_FIELD
            ) {
                $fieldName = $expr->field;
                $dqlAlias = $expr->identificationVariable;

                $search = $this->walkPathExpression($expr) . ' ' . $type;
                $index  = $dqlAlias . '.' . $fieldName;
                if (array_key_exists($index, $hint)) {
                    $sql = str_replace($search, $search . ' ' . $hint[$index], $sql);
                }
            }
        }

        return $sql;
    }

}
