<?php

namespace EntityMarshal\Definition;

use EntityMarshal\Definition\PropertyDefnitionInterface;

/**
 * Class Property
 *
 * @author    Merten van Gerven
 * @copyright (c) 2013, Merten van Gerven
 */
class PropertyDefinition implements PropertyDefnitionInterface
{
    /**
     * @var     string      Property name
     */
    protected $name;

    /**
     * @var     string      Raw property type before processing for generics.
     */
    protected $rawType;

    /**
     * @var     string      Property base type
     */
    protected $type;

    /**
     * @var     string      Type of the children if the property is an array
     */
    protected $genericType;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRawType()
    {
        return $this->rawType;
    }

    /**
     * @inheritdoc
     */
    public function setRawType($rawType)
    {
        $this->rawType = $rawType;

        $generic = $this->extractGeneric($rawType);

        if (!is_null($generic)) {
            $this->setType('array');
            $this->setGenericType($generic);
        }
        else {
            $this->setType($this->getRawType());
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGenericType()
    {
        return $this->genericType;
    }

    /**
     * @inheritdoc
     */
    public function setGenericType($genericType)
    {
        $this->genericType = $genericType;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isGeneric()
    {
        return !empty($this->genericType);
    }

    /**
     * Extract the generic subtype from the specified type if there is one.
     *
     * @param   string $type
     *
     * @return  string|null
     */
    protected function extractGeneric($type)
    {
        if (empty($type)) {
            return null;
        }

        $generic = null;

        if (substr($type, -2) === '[]') {
            $generic = substr($type, 0, -2);
        }
        elseif (strtolower(substr($type, 0, 6)) === 'array<' && substr($type, -1) === '>') {
            $generic = preg_replace('/^array<([^>]+)>$/i', '$1', $type);
        }

        return $generic;
    }
}
