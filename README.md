Code Community Search
=====================

[![Latest Stable Version](https://poser.pugx.org/code-community/search/v/stable)](https://packagist.org/packages/code-community/search) [![Total Downloads](https://poser.pugx.org/code-community/search/downloads)](https://packagist.org/packages/code-community/search) [![Latest Unstable Version](https://poser.pugx.org/code-community/search/v/unstable)](https://packagist.org/packages/code-community/search) [![License](https://poser.pugx.org/code-community/search/license)](https://packagist.org/packages/code-community/search)[![Build Status](https://travis-ci.org/code-community/search.svg?branch=master)](https://travis-ci.org/code-community/search)

Code Community Search is good component. It aims to
abstract HTTP request using Linio input, and with a json request standard you can mount your search dinamically just following a few steps.

Install
-------

The recommended way to install Linio Input is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "code-community/search": "v1.0.0"
    }
}
```

Tests
-----

To run the test suite, you need install the dependencies via composer, then
run PHPUnit.

    $ composer install
    $ phpunit

Usage
-----

The library is very easy to use: first, you have to configure the [Linio input](https://github.com/code-community/input/blob/master/README.md)

In your input handler:

```php
<?php

namespace AppBundle\Input\Handler;

use Input\Handler\AbstractHandler;
use Search\Service\Search\Behavior\HandlerAware;

class EntitySearchHandler extends AbstractHandler
{
    use HandlerAware;

    /**
     * {@inheritdoc}
     */
    public function define()
    {
        $this->handleIt('EntityBundle:Entity');
    }
}
```

In your controller:

```php
/**
 * @var EntityRepository $entityRepository
 */
private $entityRepository;

public function search(Request $request)
{
    $input = $this->getInputHandler('entity_search');
    $input->setEntityManager($this->em); // it is extremely necessary
    $input->bind($request);

    if (! $input->isValid()) {
        throw new \Exception('Invalid input');
    }

    $search = $input->getData('search');
    
    // Its not necessary
    if (! $this->entityRepository->validateSearch()) {
        throw new \Exception('Invalid Search');
    }

    /** @var array */
    $paginator = $this->entityRepository->paginateSearch($search);

    /** @var \Pagerfanta\Pagerfanta */
    $paginator = $this->entityRepository->paginateSearch($search);
    
    /** @var \Pagerfanta\Pagerfanta */
    $qb = $this->em->createQueryBuilder($params...);
    // do something
    $paginator = $this->entityRepository->paginateQuery($qb->getQuery(), $search);
    
    // Do anything
}
```

In your repository:

```php
<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Search\Service\Search\Behavior\SearchAware;
use Search\Service\Search\Entity\Search;
use Search\Service\Search\Interfaces\SearchInterface;

class EntityRepository extends EntityRepository implements SearchInterface
{
    use SearchAware;

    /**
     * {@inheritdoc}
     */
    public function search(Search $searchService)
    {
        $paginator = $this->paginateSearch($searchService);

        return $paginator;
    }
}
```

The JSON Request will be similar this:

```
{
    "search" : {
        "page" : int, // not required (if not will return all)
        "maxPerPage" : int, // not required (default: 10)

        // not required
        "orderBy" : [
            {
                "field" : string, // object fields
                "direction" : string(ASC|DESC), // not required (defalt: ASC),
            }
        ],
        
        // not required
        "fieldFilters" : [
            {
                "fieldId" : integer, // cannot be repeated
                "field" : string, // object fields
                "type" : string(EQUAL_TYPE|DIFFERENT_TYPE|LEFT_LIKE_TYPE|RIGHT_LIKE_TYPE|BOTH_LIKE_TYPE|LIKE_TYPE|IS_NULL_TYPE|IS_NOT_NULL_TYPE)
                "query" : mixed // not required,
                "or" : string{fieldId:order} // not required (pattern: /^[0-9]+\:[0-9]+$/)
                "and" : string{fieldId:order} // not required (pattern: /^[0-9]+\:[0-9]+$/)
            }
        ]
    }
}
```

A small example is:

```sql
SELECT
    *
FROM
    EntityBundle:Entity
WHERE
    email = 'rafael@gmail.com'
    OR (
        email like 'rafael%'
        AND email like '%el@gmail.com'
    )
ORDER BY
    email ASC
```
​
```JSON
{
    "search" : {
        "orderBy" : [
            {
                "field" : "email"
            }
        ],
        "fieldFilters" : [
            {
                "fieldId": 1,
                "field": "email",
                "type": "IS_EQUAL_TYPE",
                "query" : "rafael@gmail.com",
                "or": "",
                "and": ""
            },
            {
                "fieldId": 2,
                "field": "email",
                "type": "LEFT_LIKE_TYPE",
                "query" : "rafael",
                "or": "1:0",
                "and": ""
            },
            {
                "fieldId": 3,
                "field": "email",
                "type": "RIGHT_LIKE_TYPE",
                "query" : "el@gmail.com",
                "or": "",
                "and": "2:0"
            }
        ]
    }
}
```
