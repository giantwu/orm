<?php


namespace EasySwoole\ORM\Db;


use EasySwoole\Mysqli\Client;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\Pool\ObjectInterface;

class MysqliClient extends Client implements ClientInterface, ObjectInterface
{

    private $name;

    /** @var int */
    private $autoPing = 5;

    /** @var int */
    public $__lastPingTime = 0;

    public function connectionName(?string $name = null): ?string
    {
        if ($name !== null) {
            $this->name = $name;
        }
        return $this->name;
    }

    public function setAutoPing(int $autoPing): MysqliClient
    {
        $this->autoPing = $autoPing;
        return $this;
    }

    /**
     * ORM 查询入口。第二参数为 bool 时表示 rawQuery；
     * 与 mysqli Client::query(QueryBuilder, float $timeout) 并存时用弱类型避免签名冲突。
     *
     * @param QueryBuilder $builder
     * @param bool|float|null $rawQuery
     * @return Result
     * @throws Exception
     * @throws \Throwable
     */
    public function query(QueryBuilder $builder, $rawQuery = false): Result
    {
        // 兼容误传 timeout 的调用：非 bool 时按非 raw 处理
        $isRaw = $rawQuery === true;

        $result = new Result();
        $ret = null;
        $errno = 0;
        $error = '';
        $stmt = null;
        try {
            if ($isRaw) {
                $ret = $this->rawQuery($builder->getLastQuery(), $this->config->getTimeout());
            } else {
                $stmt = $this->mysqlClient()->prepare($builder->getLastPrepareQuery(), $this->config->getTimeout());
                if ($stmt) {
                    $ret = $stmt->execute($builder->getLastBindParams(), $this->config->getTimeout());
                } else {
                    $ret = false;
                }
            }

            $errno = $this->mysqlClient()->errno;
            $error = $this->mysqlClient()->error;
            $insert_id = $this->mysqlClient()->insert_id;
            $affected_rows = $this->mysqlClient()->affected_rows;
            /*
             * 重置mysqli客户端成员属性，避免下次使用
             */
            $this->mysqlClient()->errno = 0;
            $this->mysqlClient()->error = '';
            $this->mysqlClient()->insert_id = 0;
            $this->mysqlClient()->affected_rows = 0;
            //结果设置
            if (!$isRaw && $ret && $this->config->isFetchMode()) {
                $result->setResult(new Cursor($stmt));
            } else {
                $result->setResult($ret);
            }
            $result->setLastError($error);
            $result->setLastErrorNo($errno);
            $result->setLastInsertId($insert_id);
            $result->setAffectedRows($affected_rows);
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
            if ($errno) {

                /**
                 * 断线收回链接
                 */
                if (in_array($errno, [2006, 2013])) {
                    $this->close();
                }

                $exception = new Exception($error . " [{$builder->getLastQuery()}]");
                $exception->setLastQueryResult($result);
                throw $exception;
            }
        }
        return $result;
    }

    function gc()
    {
        $this->close();
    }

    function objectRestore()
    {
        $this->reset();
    }

    function beforeUse(): bool
    {
        return $this->connect();
    }

    function intervalCheck(): bool
    {
        if ($this->autoPing > 0 && (time() - $this->__lastPingTime > $this->autoPing)) {
            try {
                //执行一个sql触发活跃信息
                $this->rawQuery('select 1');
                $this->__lastPingTime = time();
                return true;
            } catch (\Throwable $throwable) {
                return false;
            }
        }
        return true;
    }
}
