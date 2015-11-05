<?php

namespace Search\Service\Search\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Search\Service\Search\Entity\Search\FieldFilter;
use Search\Service\Search\Entity\Search\OrderBy;

class Search
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    /**
     * @var integer
     */
    private $page = 1;
    /**
     * @var integer
     */
    private $maxPerPage = 10;

    /**
     * @var Collection|OrderBy[]
     */
    private $orderBy;

    /**
     * @var Collection|FieldFilter[]
     */
    private $fieldFilters;

    /**
     * @var boolean
     */
    private $listAll = true;

    public function __construct()
    {
        $this->fieldFilters = new ArrayCollection;
        $this->orderBy = new ArrayCollection;
    }

    /**
     * Gets the value of page.
     *
     * @return integer
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the value of page.
     *
     * @param integer $page the page
     *
     * @return self
     */
    public function setPage($page)
    {
        $this->listAll = false;
        $this->page = $page;

        return $this;
    }

    /**
     * Gets the FieldFilter.
     *
     * @return Collection
     */
    public function getFieldFilters()
    {
        return $this->fieldFilters;
    }

    /**
     * Get the max field filter id
     */
    public function getMaxFieldFilterId()
    {
        $maxFieldFilterId = 0;

        foreach ($this->fieldFilters as $fieldFilter) {
            if ($fieldFilter->getFieldId() > $maxFieldFilterId) {
                $maxFieldFilterId = $fieldFilter->getFieldId();
            }
        }

        return $maxFieldFilterId;
    }

    /**
     * Gets the OrderBy.
     *
     * @return Collection
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Sets the OrderBy.
     *
     * @param Collection $orderBy the field filters
     *
     * @return self
     */
    public function setOrderBy(Collection $orderBy)
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function addOrderBy(OrderBy $orderBy)
    {
        $this->orderBy->add($orderBy);
    }

    public function removeOrderBy(OrderBy $orderBy)
    {
        $this->orderBy->removeElement($orderBy);
    }

    /**
     * Sets the FieldFilter.
     *
     * @param Collection $fieldFilters the field filters
     *
     * @return self
     */
    public function setFieldFilters(Collection $fieldFilters)
    {
        $this->fieldFilters = $fieldFilters;

        return $this;
    }

    public function addFieldFilter(FieldFilter $fieldFilter)
    {
        $this->fieldFilters->add($fieldFilter);
    }

    public function removeFieldFilter(FieldFilter $fieldFilter)
    {
        $this->fieldFilters->removeElement($fieldFilter);
    }

    /**
     * Gets the value of maxPerPage.
     *
     * @return integer
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * Sets the value of maxPerPage.
     *
     * @param integer $maxPerPage the max per page
     *
     * @return self
     */
    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;

        return $this;
    }

    /**
     * Gets the value of listAll.
     *
     * @return boolean
     */
    public function getListAll()
    {
        return $this->listAll;
    }

    /**
     * Add the value of orderByDirection.
     *
     * @return string
     */
    public function getOrderByDirection()
    {
        return $this->orderByDirection;
    }

    /**
     * Sets the value of orderByDirection.
     *
     * @param string $orderByDirection the order by direction
     *
     * @return self
     */
    public function setOrderByDirection($orderByDirection)
    {
        $this->orderByDirection = $orderByDirection;

        return $this;
    }

    /**
     * Sets the value of orderByDirection.
     *
     * @param string $orderByDirection the order by direction
     *
     * @return self
     */
    public function addOrderByDirection($orderByDirection)
    {
        $this->orderByDirection[] = $orderByDirection;

        return $this;
    }

    /**
     * Get field filters grouped in a way that each array can be
     * represented as a parentheses
     *
     * @return array
     */
    public function getGroupedFieldFilters()
    {
        $groupings = new ArrayCollection();
        $groupingMap = [];
        $fieldMap = [];

        foreach ($this->getFieldFilters() as $i => $fieldFilter) {
            $fieldMap[$fieldFilter->getFieldId()] = $fieldFilter;
        }

        foreach ($fieldMap as $fieldId => $fieldFilter) {
            $orPieces = explode(':', $fieldFilter->getOr());
            $orFieldId = isset($orPieces[0]) ? $orPieces[0] : null;
            $orPosition = isset($orPieces[1]) ? intval($orPieces[1]) : null;

            $andPieces = explode(':', $fieldFilter->getAnd());
            $andFieldId = isset($andPieces[0]) ? $andPieces[0] : null;
            $andPosition = isset($andPieces[1]) ? intval($andPieces[1]) : null;

            $otherFieldId = $orFieldId ?: $andFieldId;
            $position = is_numeric($orPosition) ? $orPosition : $andPosition;

            // Should create a new grouping
            if ($otherFieldId && $position === 0) {
                $otherFieldGrouping = $groupingMap[$otherFieldId];
                $otherFieldIndex = $otherFieldGrouping->indexOf($fieldMap[$otherFieldId]);
                $groupingMap[$otherFieldId] = new ArrayCollection([$fieldMap[$otherFieldId]]);
                $otherFieldGrouping[$otherFieldIndex] = $groupingMap[$otherFieldId];
            }

            if ($otherFieldId) {
                $grouping = $groupingMap[$otherFieldId];
            } else {
                $grouping = $groupings;
            }

            if (is_numeric($position)) {
                $grouping[$position + 1] = $fieldFilter;
            } else {
                $grouping->add($fieldFilter);
            }

            $groupingMap[$fieldFilter->getFieldId()] = $grouping;
        }

        return $groupings;
    }
}
