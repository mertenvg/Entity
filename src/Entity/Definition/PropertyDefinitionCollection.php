<?php

namespace Entity\Definition;

use Entity\Definition\Abstraction\PropertyDefinitionCollectionInterface;
use Entity\Definition\Abstraction\PropertyDefinitionInterface;
use Entity\Definition\PropertyDefinition;
use Entity\Exception\InvalidArgumentException;
use Iterator;

/**
 * Class PropertyCollection
 *
 * @author    Merten van Gerven
 * @copyright (c) 2013, Merten van Gerven
 */
class PropertyDefinitionCollection implements PropertyDefinitionCollectionInterface
{
    /**
     * @var PropertyDefinitionInterface
     */
    private static $propertyPrototype;

    /**
     * @var array Associative collection of properties by $name => PropertyInterface
     */
    protected $collection = array();

    /**
     * Constructor override
     *
     * @param PropertyDefinitionInterface $propertyPrototype
     */
    public function __construct(PropertyDefinitionInterface $propertyPrototype = null)
    {
        if (!($propertyPrototype instanceof PropertyDefinitionInterface)) {
            $propertyPrototype = new PropertyDefinition();
        }

        self::$propertyPrototype = $propertyPrototype;
    }

    /**
     * Check whether collection contains a specified key
     *
     * @param   string $name
     *
     * @return  boolean
     */
    public function has($name)
    {
        return array_key_exists($name, $this->collection);
    }

    /**
     * Retrieve the property definition by name.
     *
     * @param   string $name
     *
     * @return  PropertyDefnitionInterface
     */
    public function get($name)
    {
        return isset($this->collection[$name]) ? $this->collection[$name] : null;
    }

    /**
     * Return a list of keys for the collection
     *
     * @return  array
     */
    public function keys()
    {
        return array_keys($this->collection);
    }

    /**
     * Add a list of properties and types
     *
     * @param   array $list   key=>type pair list of properties and types.
     *
     * @return  PropertyDefinitionCollection
     * @throws  InvalidArgumentException
     */
    public function import($list)
    {
        if (!is_array($list) && !($list instanceof Iterator)) {
            throw new InvalidArgumentException("Expected a list of properties and types");
        }

        foreach ($list as $name => $type) {
            $this->add($name, $type);
        }

        return $this;
    }

    /**
     * Add a single property and type
     *
     * @param string $name
     * @param string $type
     *
     * @return PropertyDefinitionCollection
     */
    public function add($name, $type)
    {
        $property = clone self::$propertyPrototype;

        $this->collection[$name] = $property->setName($name)->setRawType($type);

        return $this;
    }

    /**
     * Export a list of properties and types
     *
     * @return  array
     */
    public function export()
    {
        $result = array();

        foreach ($this->collection as $name => $property) {
            /* @var $property PropertyDefnitionInterface */
            $result[$name] = $property->getRawType();
        }

        return $result;
    }
}
