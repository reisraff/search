<?php

namespace Search\Service\Search\Behavior;

use Input\Constraint\Enum;
use Input\Constraint\Pattern;
use Search\Service\Search\Entity\Search;
use Search\Service\Search\Entity\Search\FieldFilter;
use Search\Service\Behavior\EntityManagerAware;

trait HandlerAware
{
    use EntityManagerAware;

    private $allowedFields = [];

    protected function addAllowedField($field)
    {
        $this->allowedFields[] = $field;
    }

    /**
     * @param string $entity
     */
    protected function handleIt($entity)
    {
        $search = $this->add('search', 'Search\Service\Search\Entity\Search');

        $search->add('page', 'integer', ['required' => false]);
        $search->add('maxPerPage', 'integer', ['required' => false]);

        $orderBy = $search->add('orderBy', 'Search\Service\Search\Entity\Search\OrderBy[]', ['required' => false]);
        $orderBy->add('field', 'string', [
            'constraints' => [
                new Enum(
                    array_merge(
                        $this->em->getClassMetadata($entity)->getFieldNames(),
                        $this->allowedFields
                    )
                ),
            ]
        ]);
        $orderBy->add('direction', 'string', [
            'required' => false,
            'constraints' => [
                new Enum([
                    Search::ASC,
                    Search::DESC
                ]),
            ]
        ]);

        $fieldFilters = $search->add(
            'fieldFilters',
            'Search\Service\Search\Entity\Search\FieldFilter[]',
            [
                'required' => false,
            ]
        );

        $fieldFilters->add('fieldId', 'integer');
        $fieldFilters->add('field', 'string', [
            'constraints' => [
                new Enum(
                    array_merge(
                        $this->em->getClassMetadata($entity)->getFieldNames(),
                        $this->allowedFields
                    )
                ),
            ]
        ]);
        $fieldFilters->add('type', 'string', [
            'constraints' => [
                new Enum([
                    FieldFilter::EQUAL_TYPE,
                    FieldFilter::DIFFERENT_TYPE,
                    FieldFilter::LEFT_LIKE_TYPE,
                    FieldFilter::RIGHT_LIKE_TYPE,
                    FieldFilter::BOTH_LIKE_TYPE,
                    FieldFilter::LIKE_TYPE,
                    FieldFilter::IS_NULL_TYPE,
                    FieldFilter::IS_NOT_NULL_TYPE,
                    FieldFilter::IN_TYPE
                ]),
            ]
        ]);
        $fieldFilters->add('query', 'mixed', ['required' => false]);
        $fieldFilters->add('or', 'string', [
            'required' => false,
            'constraints' => [
                new Pattern('/^[0-9]+\:[0-9]+$/')
            ]
        ]);
        $fieldFilters->add('and', 'string', [
            'required' => false,
            'constraints' => [
                new Pattern('/^[0-9]+\:[0-9]+$/')
            ]
        ]);
    }
}
