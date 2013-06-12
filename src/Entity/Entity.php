<?php

namespace Entity;

use Entity\Abstraction\AbstractEntity;
use Entity\Abstraction\Accessor\ClassMethodInterface;
use Entity\Abstraction\Accessor\ObjectPropertyInterface;

/**
 * @author      Merten van Gerven
 * @package     Entity
 */
class Entity extends AbstractEntity implements ClassMethodInterface, ObjectPropertyInterface
{
    /**
     * {@inheritdoc}
     */
    public function __call($method, $arguments)
    {
        return $this->call($method, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function &__get($name)
    {
        return $this->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    protected function propertiesAndTypes()
    {
        $properties = array();
        $reflection = new \ReflectionClass($this->calledClassName());
        $publicVars = $reflection->getProperties();

        foreach ($publicVars as $publicVar) {
            /* @var ReflectionProperty $publicVar */

            $doc       = $publicVar->getDocComment();
            $key       = $publicVar->getName();
            $is_static = $publicVar->isStatic();

            if ($is_static) {
                continue;
            }

            $matches          = array();
            $properties[$key] = preg_match('/@var\s+([^\s]+)/i', $doc, $matches) ? $matches[1] : null;
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultValues()
    {
        return get_class_vars($this->calledClassName());
    }

    /**
     * {@inheritdoc}
     */
    public function calledClassName()
    {
        return get_called_class();
    }

    /**
     * {@inheritdoc}
     */
    protected function unsetProperties($keys)
    {
        foreach ($keys as $key) {
            unset($this->$key);
        }
    }
}