<?php

namespace Search\Service\Search\Behavior;

use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Search\Service\Search\Exception\RepeatedFieldId;
use Search\Service\Search\Entity\Search;
use Search\Service\Search\Entity\Search\FieldFilter as Filter;

trait SearchAware
{
    private function buildFieldFilterExpression(
        QueryBuilder $qb,
        Collection $fieldFilterGroups,
        array $fieldNameMap
    ) {
        $lastExpressionOrComparison = null;

        foreach ($fieldFilterGroups as $item) {
            $expression = null;

            if (! $item instanceof Collection) {
                $fieldFilter = $item;
                $query = $fieldFilter->getQuery();
                $name = $fieldNameMap[$fieldFilter->getField()];
                $pieces = explode('.', $fieldFilter->getField());
                $isOr = !! $fieldFilter->getOr();

                $placeholder = ':s' . md5(microtime());

                switch ($fieldFilter->getType()) {
                    case Filter::EQUAL_TYPE:
                        $comparison = $qb->expr()->eq($name, $placeholder);
                        $qb->setParameter($placeholder, $query);
                        break;

                    case Filter::DIFFERENT_TYPE:
                        $comparison = $qb->expr()->neq($name, $placeholder);
                        $qb->setParameter($placeholder, $query);
                        break;

                    case Filter::LEFT_LIKE_TYPE:
                        $comparison = $qb->expr()->like($name, $placeholder);
                        $qb->setParameter($placeholder, $query . '%');
                        break;

                    case Filter::RIGHT_LIKE_TYPE:
                        $comparison = $qb->expr()->like($name, $placeholder);
                        $qb->setParameter($placeholder, '%' . $query);
                        break;

                    case Filter::BOTH_LIKE_TYPE:
                        $comparison = $qb->expr()->like($name, $placeholder);
                        $qb->setParameter($placeholder, '%' . $query . '%');
                        break;

                    case Filter::LIKE_TYPE:
                        $comparison = $qb->expr()->like($name, $placeholder);
                        $qb->setParameter($placeholder, $query);
                        break;

                    case Filter::IS_NULL_TYPE:
                        $comparison = $qb->expr()->isNull($name);
                        break;

                    case Filter::IS_NOT_NULL_TYPE:
                        $comparison = $qb->expr()->isNotNull($name);
                        break;

                    case Filter::IN_TYPE:
                        if (count($query) == 0) {
                            // Automatic evaluate to false
                            $comparison = $qb->expr()->eq(0, 1);
                        } else {
                            // Query is automatic quoted by Expr::literal
                            $comparison = $qb->expr()->in($name, $query);
                        }
                        break;
                }

                if ($lastExpressionOrComparison) {
                    $expression = $isOr ?
                        $qb->expr()->orX($lastExpressionOrComparison, $comparison) :
                        $qb->expr()->andX($lastExpressionOrComparison, $comparison);
                } else {
                    $expression = null;
                }

                $lastExpressionOrComparison = $expression ? $expression : $comparison;
            } else {
                $isOr = !! $item[0]->getOr();

                $innerExpression = $this->buildFieldFilterExpression(
                    $qb,
                    $item,
                    $fieldNameMap
                );

                if ($lastExpressionOrComparison) {
                    $expression = $isOr ?
                        $qb->expr()->orX($lastExpressionOrComparison, $innerExpression) :
                        $qb->expr()->andX($lastExpressionOrComparison, $innerExpression);
                }

                $lastExpressionOrComparison = $expression ? $expression : $innerExpression;
            }
        }

        return $lastExpressionOrComparison;
    }

    private function buildFieldNameMap(QueryBuilder $qb, Search $search)
    {
        $fieldNameMap = [];

        $entityFieldNames = $qb
            ->getEntityManager()
            ->getClassMetadata($qb->getRootEntities()[0])
            ->getFieldNames();

        $currentJoins = [];
        $fieldNames = [];

        foreach ($search->getFieldFilters() as $fieldFilter) {
            $fieldNames[] = $fieldFilter->getField();
        }

        foreach ($search->getOrderBy() as $orderBy) {
            $fieldNames[] = $orderBy->getField();
        }

        $fieldNames = array_unique($fieldNames);

        foreach ($fieldNames as $fieldName) {
            $pieces = explode('.', $fieldName);

            if (in_array($fieldName, $entityFieldNames) || count($pieces) == 1) {
                $fieldNameMap[$fieldName] = $qb->getRootAlias() . '.' . $fieldName;
            } else {
                $lastAlias = $qb->getRootAlias();

                foreach ($pieces as $i => $piece) {
                    if ($i == count($pieces) - 1) {
                        $fieldNameMap[$fieldName] = $pieces[$i - 1] . '.' . $piece;
                    } else {
                        $currentJoin = $lastAlias . '.' . $piece;

                        if (! in_array($currentJoin, $currentJoins)) {
                            $qb->leftJoin($currentJoin, $piece);
                            $currentJoins[] = $currentJoin;
                        }

                        $lastAlias = $piece;
                    }
                }
            }
        }

        return $fieldNameMap;
    }

    public function validateSearch(Search $search)
    {
        $fieldIdMap = [];

        foreach ($search->getFieldFilters() as $fieldFilter) {
            if (array_key_exists($fieldFilter->getFieldId(), $fieldIdMap)) {
                throw new RepeatedFieldId('Repeated field id "' . $fieldFilter->getFieldId() . '"');
            }

            $fieldIdMap[$fieldFilter->getFieldId()] = $fieldFilter;
        }
    }

    private function processSearchQueryBuilder(QueryBuilder $qb, Search $search)
    {
        $this->validateSearch($search);

        $fieldNameMap = $this->buildFieldNameMap($qb, $search);
        $groupings = $search->getGroupedFieldFilters();
        $expression = $this->buildFieldFilterExpression($qb, $groupings, $fieldNameMap);

        if ($expression) {
            $qb->andWhere($expression);
        }

        foreach ($search->getOrderBy() as $orderBy) {
            $qb->addOrderBy($fieldNameMap[$orderBy->getField()], $orderBy->getDirection());
        }

        return $qb;
    }

    private function processSearchQuery(Search $search)
    {
        // Alias Entity (e)
        $qb = $this->createQueryBuilder('e');

        return $this->processSearchQueryBuilder($qb, $search)->getQuery();
    }

    public function search(Search $search)
    {
        $return = $this->processSearchQuery($search)->getResult();

        return $return;
    }

    public function paginateSearch(Search $search)
    {
        $query = $this->processSearchQuery($search);

        return $this->paginateQuery($query, $search);
    }

    public function paginateQuery(Query $query, Search $search)
    {
        $results = $query->getResult();

        $limit = $search->getListAll() ?
            count($results) ?
                count($results) :
                1 :
            $search->getMaxPerPage();

        $adapter = new DoctrineORMAdapter($query);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($limit);
        $paginator->setCurrentPage($search->getPage());
        $paginator->getCurrentPageResults(); // Just to cache the consult
        $paginator->getNbResults(); // Just to cache the results

        return $paginator;
    }
}
