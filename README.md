Code Community Search
=====================

Code Community Search is good component. It aims to
abstract HTTP request using Linio input, and with a json request standard you can mount your search dinamically just following a few steps.

Install
-------

The recommended way to install Linio Input is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "code-community/search": "dev-master"
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
