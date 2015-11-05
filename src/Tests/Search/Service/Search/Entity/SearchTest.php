<?php

namespace Test\Search\Service\Search\Entity;

use Search\Service\Search\Entity\Search;
use Search\Service\Search\Entity\Search\FieldFilter;

class SearchTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGroupedFieldFilters()
    {
        $search = new Search;

        $f1 = (new FieldFilter)
            ->setFieldId(1)
            ->setField('email')
            ->setType(FieldFilter::EQUAL_TYPE)
            ->setQuery('rafael@gmail.com');
        $search->addFieldFilter($f1);

        $f2 = (new FieldFilter)
            ->setFieldId(2)
            ->setField('email')
            ->setType(FieldFilter::LEFT_LIKE_TYPE)
            ->setQuery('rafael')
            ->setOr('1:0');
        $search->addFieldFilter($f2);

        $f3 = (new FieldFilter)
            ->setFieldId(3)
            ->setField('email')
            ->setType(FieldFilter::RIGHT_LIKE_TYPE)
            ->setAnd('2:0');
        $search->addFieldFilter($f3);

        $groupings = $search->getGroupedFieldFilters();

        // Desired Structure [[$f1, [$f2, $f3]]]
        $this->assertEquals($f1, $groupings[0][0]);
        $this->assertEquals($f2, $groupings[0][1][0]);
        $this->assertEquals($f3, $groupings[0][1][1]);
    }
}
