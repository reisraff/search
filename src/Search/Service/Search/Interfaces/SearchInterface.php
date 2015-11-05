<?php

namespace Search\Service\Search\Interfaces;

use Search\Service\Search\Entity\Search;
use Pagerfanta\Pagerfanta;

interface SearchInterface
{
    /**
     * @param Search $search
     *
     * @return Pagerfanta
     */
    public function search(Search $search);
}
