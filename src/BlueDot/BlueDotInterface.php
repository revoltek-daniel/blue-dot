<?php

namespace BlueDot;

use BlueDot\Database\Connection;
use BlueDot\Entity\PromiseInterface;
use BlueDot\StatementBuilder\StatementBuilder;

interface BlueDotInterface
{
    /**
     * @param string $name
     * @param array|mixed $parameters
     * @param bool $cache
     * @return PromiseInterface
     */
    public function execute(string $name, $parameters = array(), bool $cache = true) : PromiseInterface;
    /**
     * @param Connection $connection
     * @return BlueDotInterface
     */
    public function setConnection(Connection $connection) : BlueDotInterface;
    /**
     * @return Connection|null
     */
    public function getConnection();
    /**
     * @param Connection|null $connection
     * @return StatementBuilder
     */
    public function createStatementBuilder(Connection $connection = null) : StatementBuilder;
    /**
     * @param string $configSource
     * @return BlueDotInterface
     */
    public function setConfiguration(string $configSource) : BlueDotInterface;
    /**
     * @return APIInterface
     */
    public function api() : APIInterface;
    /**
     * @param string $apiName
     * @return BlueDotInterface
     */
    public function useApi(string $apiName) : BlueDotInterface;
    /**
     * @param string $name
     * @param array $parameters
     * @param bool $cache
     * @return BlueDotInterface
     */
    public function prepareExecution(string $name, $parameters = array(), bool $cache = true) :  BlueDotInterface;
    /**
     * @return array
     */
    public function executePrepared() : array;
}