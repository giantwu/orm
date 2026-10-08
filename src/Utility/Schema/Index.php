<?php

namespace EasySwoole\ORM\Utility\Schema;

use EasySwoole\DDL\Enum\Index as IndexType;
use InvalidArgumentException;

/**
 * 索引结构
 * Class Index
 * @package EasySwoole\ORM\Utility\Schema
 */
class Index extends \EasySwoole\DDL\Blueprint\Create\Index
{
    /**
     * @param string|null $indexName
     * @param string|IndexType $indexType
     * @param string|array $indexColumns
     */
    public function __construct(?string $indexName, $indexType, $indexColumns)
    {
        if (is_string($indexType)) {
            $typeName = strtolower($indexType);
            $indexType = IndexType::tryFrom($typeName);
            if ($indexType === null) {
                throw new InvalidArgumentException("Unsupported index type: {$typeName}");
            }
        }
        parent::__construct($indexName, $indexType, $indexColumns);
    }

    /**
     * IndexName Getter
     * @return mixed
     */
    public function getIndexName()
    {
        return $this->indexName;
    }

    /**
     * IndexType Getter
     * @return string
     */
    public function getIndexType()
    {
        return parent::getIndexType();
    }

    /**
     * IndexColumns Getter
     * @return mixed
     */
    public function getIndexColumns()
    {
        return $this->indexColumns;
    }

    /**
     * IndexComment Getter
     * @return mixed
     */
    public function getIndexComment()
    {
        return $this->indexComment;
    }
}
