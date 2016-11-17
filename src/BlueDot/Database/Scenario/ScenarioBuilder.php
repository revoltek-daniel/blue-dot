<?php

namespace BlueDot\Database\Scenario;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Database\ParameterCollection;
use BlueDot\Database\ParameterCollectionInterface;
use BlueDot\Exception\QueryException;
use BlueDot\Entity\EntityInterface;

class ScenarioBuilder implements ScenarioInterface
{
    /**
     * @var ArgumentBag $argumentBag
     */
    private $argumentBag;
    /**
     * @param StorageInterface $argumentsBag
     */
    public function __construct(StorageInterface $argumentsBag)
    {
        $this->argumentBag = $argumentsBag;
    }

    public function buildScenario()
    {
        if ($this->argumentBag->get('type') === 'simple') {
            $this->resolveParameters($this->argumentBag);

            return new Scenario($this->argumentBag);
        } else if ($this->argumentBag->get('type') === 'scenario') {
            $scenarious = $this->argumentBag->get('specific_configuration');

            $scenarioCollection = new ScenarioCollection();
            foreach ($scenarious as $scenario) {
                $this->argumentBag->remove('specific_configuration');

                $argumentBag = new ArgumentBag($this->argumentBag);
                $argumentBag->mergeStorage($scenario);
                $argumentBag->add('specific_configuration', $scenario);

                $this->resolveParameters($argumentBag);

                $scenarioCollection->addScenario($scenario->getName(), new Scenario($argumentBag));
            }

            return $scenarioCollection;
        }
    }

    private function resolveParameters(StorageInterface $storage)
    {
        if ($storage->has('parameters')) {
            $parameters = $storage->get('parameters');
            $specificConfiguration = $storage->get('specific_configuration');

            $storage->add(
                'parameters',
                $this->validateParameters($parameters, $specificConfiguration),
                true
            );
        }
    }

    private function validateParameters($parameters, $specificConfiguration)
    {
        if (!$parameters instanceof EntityInterface and !is_array($parameters)) {
            throw new QueryException('Invalid argument. If provided, parameters can be an instance of '.EntityInterface::class.', an instance of '.ParameterCollectionInterface::class.' or an array');
        }

        if ($parameters instanceof EntityInterface) {

        }

        $parameters = new ParameterCollection($parameters);
        $configParameters = $specificConfiguration->getParameters();

        if (!empty(array_diff($configParameters, $parameters->getBindingKeys()))) {
            throw new QueryException('Given parameters and parameters in configuration are not equal for '.$specificConfiguration->getType().'.'.$specificConfiguration->getName());
        }

        return $parameters;
    }
}