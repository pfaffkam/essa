<?php

namespace PfaffKIT\Essa\Query;

use PfaffKIT\Essa\EventSourcing\Exception\EssaException;

class QueryNotRegisteredException extends \RuntimeException implements EssaException
{
    public function __construct(Query $query)
    {
        parent::__construct(sprintf("The query '%s' has no handler.", $query::class));
    }
}
