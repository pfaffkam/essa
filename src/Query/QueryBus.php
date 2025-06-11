<?php

namespace PfaffKIT\Essa\Query;

interface QueryBus
{
    public function ask(Query $query): mixed;
}
