<?php


namespace EasySwoole\ORM\Db;


use EasySwoole\Mysqli\Config as MysqlConfig;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\Pool\AbstractPool;
use EasySwoole\Pool\ObjectInterface;

class MysqlPool extends AbstractPool
{
    protected function createObject(): ObjectInterface
    {
        /** @var Config $config */
        $config = $this->getConfig();
        $mysqlConfig = new MysqlConfig([
            'host' => $config->getHost(),
            'user' => $config->getUser(),
            'password' => $config->getPassword(),
            'database' => $config->getDatabase(),
            'port' => $config->getPort(),
            'timeout' => $config->getTimeout(),
            'charset' => $config->getCharset(),
            'strict_type' => $config->isStrictType(),
            'fetch_mode' => $config->isFetchMode(),
        ]);
        $client = new MysqliClient($mysqlConfig);
        $client->setAutoPing($config->getAutoPing());
        if ($client->connect()) {
            $client->__lastPingTime = 0;
            return $client;
        }
        $mysqlClient = $client->mysqlClient();
        throw new Exception($mysqlClient ? $mysqlClient->connect_error : 'mysql connect fail');
    }
}
