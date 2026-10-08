<?php


namespace EasySwoole\ORM\Db;


use EasySwoole\Mysqli\QueryBuilder;

interface ClientInterface
{
    /**
     * @param QueryBuilder $builder
     * @param bool $rawQuery
     * @return Result
     */
    public function query(QueryBuilder $builder, $rawQuery = false): Result;
    public function connectionName(?string $name = null):?string;
}