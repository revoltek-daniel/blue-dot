<?php

namespace BlueDot\Entity;

use BlueDot\Common\AbstractArgumentBag;
use BlueDot\Exception\EntityException;

class Entity extends AbstractArgumentBag
{
    /**
     * @param array $findBy
     * @return Entity|null
     * @throws EntityException
     */
    public function findBy(array $findBy)
    {
        $keys = array_keys($findBy);

        foreach ($keys as $key) {
            if (!is_string($key)) {
                throw new EntityException(sprintf(
                    'Invalid argument for Entity::findBy(). Argument should be a association array, not numeric array'
                ));
            }
        }

        foreach ($this->arguments as $argument) {
            $result = array_intersect_assoc($findBy, $argument);

            if (!empty($result) and count($findBy) === count($result)) {
                return new Entity(array($argument));
            }
        }

        return null;
    }
    /**
     * @param string $column
     * @param string $value
     * @return mixed
     * @throws EntityException
     */
    public function find(string $column, string $value)
    {
        $result = $this->findBy(array(
            $column => $value,
        ));

        if (count($result) > 1) {
            throw new EntityException(sprintf('Invalid return value. Entity::find() can only return one result. %d results found', count($result)));
        }

        return new Entity($result[0]);
    }
    /**
     * @param string $column
     * @return array|null
     */
    public function extract(string $column)
    {
        $columns = array();
        foreach ($this->arguments as $argument) {
            if (array_key_exists($column, $argument)) {
                $columns[$column][] = $argument[$column];
            }
        }

        if (empty($columns)) {
            return null;
        }

        return $columns;
    }
    /**
     * @param array $arrangeColumns
     * @return $this|Entity
     */
    public function arrangeMultiples(array $arrangeColumns)
    {
        if (empty($arrangeColumns)) {
            return $this;
        }

        $temp = array();
        $arranged = false;
        foreach ($this->arguments as $argument) {
            foreach ($arrangeColumns as $column) {
                if (array_key_exists($column, $argument)) {
                    $temp[$column][] = $argument[$column];
                }
            }

            $argumentKeys = array_keys($argument);

            if (!$arranged) {
                foreach ($argumentKeys as $argumentKey) {
                    if (in_array($argumentKey, $arrangeColumns) === false) {
                        $temp[$argumentKey] = $argument[$argumentKey];
                    }
                }

                $arranged = true;
            }
        }

        return new Entity($temp);
    }
}