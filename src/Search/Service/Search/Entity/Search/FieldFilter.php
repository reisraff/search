<?php

namespace Search\Service\Search\Entity\Search;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class FieldFilter
{
    const EQUAL_TYPE = 'EQUAL_TYPE';
    const DIFFERENT_TYPE = 'DIFFERENT_TYPE';
    const LEFT_LIKE_TYPE = 'LEFT_LIKE_TYPE';
    const RIGHT_LIKE_TYPE = 'RIGHT_LIKE_TYPE';
    const BOTH_LIKE_TYPE = 'BOTH_LIKE_TYPE';
    const LIKE_TYPE = 'LIKE_TYPE';
    const IS_NULL_TYPE = 'IS_NULL_TYPE';
    const IS_NOT_NULL_TYPE = 'IS_NOT_NULL_TYPE';
    const IN_TYPE = 'IN_TYPE';

    /**
     * @var string
     */
    private $fieldId;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $type;

    /**
     * @var mixed
     */
    private $query;

    /*
     * @var string
     */
    private $or = null;

    /*
     * @var string
     */
    private $and = null;

    /**
     * Gets the value of fieldId.
     *
     * @return string
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * Sets the value of fieldId.
     *
     * @param string $fieldId the field id
     *
     * @return self
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;

        return $this;
    }

    /**
     * Gets the value of field.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Sets the value of field.
     *
     * @param string $field the field
     *
     * @return self
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Gets the value of type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the value of type.
     *
     * @param string $type the type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the value of query.
     *
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Sets the value of query.
     *
     * @param mixed $query the query
     *
     * @return self
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Gets the value of or.
     *
     * @return mixed
     */
    public function getOr()
    {
        return $this->or;
    }

    /**
     * Sets the value of or.
     *
     * @param mixed $or the or
     *
     * @return self
     */
    public function setOr($or)
    {
        $this->or = $or;

        return $this;
    }

    /**
     * Gets the value of and.
     *
     * @return mixed
     */
    public function getAnd()
    {
        return $this->and;
    }

    /**
     * Sets the value of and.
     *
     * @param mixed $and the and
     *
     * @return self
     */
    public function setAnd($and)
    {
        $this->and = $and;

        return $this;
    }
}
