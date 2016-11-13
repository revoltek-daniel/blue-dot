<?php

namespace BlueDot\Configuration;

use BlueDot\Configuration\Simple\SimpleSelect;
use BlueDot\Exception\ConfigurationException;

class Configuration
{
    /**
     * @var array $connectionType
     */
    private $dsn = array();
    /**
     * @var array $simples
     */
    private $simples = array();
    /**
     * @constructor
     * @param array $configuration
     * @throws ConfigurationException
     */
    public function __construct(array $configuration)
    {
        if (!array_key_exists('configuration', $configuration)) {
            throw new ConfigurationException('Invalid configuration file. Top element should be \'configuration\'');
        }

        $configuration = $configuration['configuration'];

        if (array_key_exists('connection', $configuration)) {
            $connection = $configuration['connection'];

            $validKeys = array('host', 'database_name', 'user', 'password');

            foreach ($validKeys as $key) {
                if (!array_key_exists($key, $connection)) {
                    throw new ConfigurationException('Invalid connection configuration. Missing '.$key.' configutaion value');
                }
            }

            $this->dsn = $connection;
        }

        if (array_key_exists('simple', $configuration)) {
            if (array_key_exists('select', $configuration['simple'])) {
                $this->simples[] = new SimpleSelect($configuration['simple']['select']);
            }
        }
    }
    /**
     * @return string
     */
    public function getDsn() : array
    {
        return $this->dsn;
    }
}