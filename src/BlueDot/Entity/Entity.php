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
            if (is_object($argument)) {
                $resolvedArgument = array();

                foreach ($findBy as $property => $value) {
                    $method = 'get'.ucfirst($property);

                    if (!method_exists($argument, $method)) {
                        throw new EntityException(
                            sprintf('Possible invalid model method. If you choose to return a specific model from a query, that model has to gave a \'get\' method for the property(s) which you specified in the findBy() method. For example, if you which to find by an id, the the model should have a getId() method')
                        );
                    }

                    $resolvedArgument[$property] = $argument->{$method}();
                }

                $result = array_intersect_assoc($findBy, $resolvedArgument);

                if (!empty($result) and count($findBy) === count($result)) {
                    return $argument;
                }
            }

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


        if (count($result) === 1) {
            return (new Entity($result))->normalizeIfOneExists();
        }

            if (count($result) > 1 or empty($result)) {
            throw new EntityException(sprintf('Invalid return value. Entity::find() can only return one result. %d results found', count($result)));
        }

        return new Entity($result[0]);
    }
    /**
     * @param string $column
     * @param \Closure|null $evaluation
     * @param string|null $alias
     * @return Entity|null
     * @throws EntityException
     */
    public function extract(string $column, \Closure $evaluation = null, string $alias = null)
    {
        $columns = array();
        foreach ($this->arguments as $argument) {
            if (is_object($argument)) {
                $method = 'get'.ucfirst($column);

                if (!method_exists($argument, $method)) {
                    throw new EntityException(
                        sprintf('Possible invalid model method. If you choose to extract values from a specific model from a query, that model has to gave a \'get\' method for the property(s) which you specified in the findBy() method. For example, if you which to find by an id, the the model should have a getId() method')
                    );
                }

                $columns[$column][] = $argument->{$method}();

                continue;
            }

            if (array_key_exists($column, $argument)) {
                if ($evaluation instanceof \Closure) {
                    if ($evaluation->__invoke($argument) === true) {
                        $value = $argument[$column];

                        if (is_object($argument)) {
                            $method = 'get'.ucfirst($column);

                            if (!method_exists($argument, $method)) {
                                throw new EntityException(
                                    sprintf('Possible invalid model method. If you choose to extract values from a specific model from a query, that model has to gave a \'get\' method for the property(s) which you specified in the findBy() method. For example, if you which to find by an id, the the model should have a getId() method')
                                );
                            }

                            $value = $argument->{$method}();
                        }

                        if (is_string($alias)) {
                            $columns[$alias][] = $value;
                        } else {
                            $columns[$column][] = $value;
                        }
                    }

                    continue;
                }

                $columns[$column][] = $argument[$column];
            }
        }

        if (empty($columns)) {
            return null;
        }

        return new Entity($columns);
    }
    /**
     * @param array $arrangeColumns
     * @param \Closure|null $evaluation
     * @param string|null $alias
     * @return $this|Entity
     * @throws EntityException
     */
    public function arrangeMultiples(array $arrangeColumns, \Closure $evaluation = null, string $alias = null)
    {
        if (empty($arrangeColumns)) {
            return $this;
        }

        $temp = array();
        $arranged = false;
        foreach ($this->arguments as $argument) {
            $argumentKeys = array_keys($argument);

            if (!$arranged) {
                foreach ($argumentKeys as $argumentKey) {
                    if (in_array($argumentKey, $arrangeColumns) === false) {
                        $temp[$argumentKey] = $argument[$argumentKey];
                    }
                }

                $arranged = true;
            }

            foreach ($arrangeColumns as $column) {
                if (array_key_exists($column, $argument)) {
                    if ($evaluation instanceof \Closure) {
                        if ($evaluation->invoke($argument) === true) {
                            if (is_string($alias)) {
                                $temp[$alias][] = $argument[$column];
                            } else {
                                $temp[$column][] = $argument[$column];
                            }
                        }

                        continue;
                    }

                    $temp[$column][] = $argument[$column];
                }
            }
        }

        if (empty($temp)) {
            return null;
        }

        return new Entity($temp);
    }
    /**
     * @return Entity
     */
    public function normalizeIfOneExists() : Entity
    {
        if (count($this->arguments) === 1) {
            $firstKey = array_keys($this->arguments)[0];

            if (is_int($firstKey)) {
                $this->arguments = $this->arguments[0];
            }
        }

        return $this;
    }
}