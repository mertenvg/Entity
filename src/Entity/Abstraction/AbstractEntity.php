<?php

namespace Entity\Abstraction;

use Entity\Converter\Abstraction\ConverterStrategyInterface;
use Entity\Converter\Dump;
use Entity\Converter\PhpArray;
use Entity\Definition\Abstraction\PropertyDefinitionCollectionInterface;
use Entity\Definition\Abstraction\PropertyDefinitionInterface;
use Entity\Definition\PropertyDefinitionCollection;
use Entity\Exception\RuntimeException;
use Entity\Marshal\Abstraction\MarshalInterface;
use Entity\Marshal\Exception\InvalidArgumentException;
use Entity\Marshal\Strict;
use Entity\RuntimeCache\Abstraction\RuntimeCacheInterface;
use Entity\RuntimeCache\RuntimeCache;
use Traversable;

/**
 * This class is intended to be used as a base for pure data object classes
 * that contain typed (using phpdoc) public properties. Control over these
 * properties is deferred to Entity in order to validate inputs and auto-
 * matically cast values to the correct types.
 *
 * @author      Merten van Gerven
 * @category    Entity
 * @package     Entity
 * @abstract
 */
abstract class AbstractEntity implements EntityInterface, SortableInterface
{
    /**
     * @var MarshalInterface Default marshal instance (shared with all entities)
     */
    private static $defaultMarshal;

    /**
     * @var RuntimeCacheInterface Default runtime cache instance (shared with all entities)
     */
    private static $defaultRuntimeCache;

    /**
     * @var PropertyDefinitionCollectionInterface Prototype of PropertyDefinitionCollectionInterface for cloning.
     */
    private static $prototypePropertyDefinitionCollection;

    /**
     * @var MarshalInterface
     */
    private $marshal;

    /**
     * @var RuntimeCacheInterface
     */
    private $runtimeCache;

    /**
     * @var array Values of public properties declared within Entity extendor.
     */
    private $properties = array();

    /**
     * @var PropertyDefinitionCollectionInterface
     */
    private $definitions;

    /**
     * @var integer
     */
    private $position = 0;

    /**
     * @var array
     */
    private $privileged = array();

    /**
     * Default constructor.
     *
     * @param array|Traversable                     $data
     * @param MarshalInterface                      $marshal
     * @param PropertyDefinitionCollectionInterface $propertyDefinitionCollection
     * @param RuntimeCacheInterface                 $runtimeCache
     */
    public function __construct(
        $data = null,
        MarshalInterface $marshal = null,
        PropertyDefinitionCollectionInterface $propertyDefinitionCollection = null,
        RuntimeCacheInterface $runtimeCache = null
    ) {
        if (!($marshal instanceof MarshalInterface)) {
            $marshal = $this->defaultMarshal();
        }

        if (!($runtimeCache instanceof RuntimeCacheInterface)) {
            $runtimeCache = $this->defaultRuntimeCache();
        }

        $this->position     = 0;
        $this->marshal      = $marshal;
        $this->runtimeCache = $runtimeCache;
        $this->definitions  = $this->resolveDefitions($propertyDefinitionCollection);

        $this->unsetProperties($this->definitions->keys());

        $this->privileged = get_object_vars($this);

        $this->resolveDefaults();

        if (!is_null($data)) {
            $this->fromArray($data);
        }
    }

    protected function resolveDefaults()
    {
        $className = $this->calledClassName();

        if ($this->runtimeCache->has($className, __METHOD__)) {
            $this->properties = $this->runtimeCache->get($className, __METHOD__);

            return;
        }

        $this->fromArray($this->defaultValues());

        $this->runtimeCache->set($className, $this->properties, __METHOD__);
    }

    /**
     * Retrieve an instance of the default marshal
     *
     * @return MarshalInterface
     */
    protected function defaultMarshal()
    {
        if (is_null(self::$defaultMarshal)) {
            self::$defaultMarshal = new Strict();
        }

        return self::$defaultMarshal;
    }

    /**
     * Retrieve an instance of the default runtime cache object
     *
     * @return \Entity\RuntimeCache\Abstraction\RuntimeCacheInterface
     */
    protected function defaultRuntimeCache()
    {
        if (is_null(self::$defaultRuntimeCache)) {
            self::$defaultRuntimeCache = new RuntimeCache();
        }

        return self::$defaultRuntimeCache;
    }

    /**
     * Resolve and if necessary initialize the property definition collection.
     *
     * @param PropertyDefinitionCollectionInterface $propertyDefinitionCollection
     *
     * @return PropertyDefinitionCollectionInterface
     */
    protected function resolveDefitions(PropertyDefinitionCollectionInterface $propertyDefinitionCollection = null)
    {
        if ($propertyDefinitionCollection instanceof PropertyDefinitionCollectionInterface) {
            return $propertyDefinitionCollection->import($this->propertiesAndTypes());
        }

        $className = $this->calledClassName();

        if ($this->runtimeCache->has($className, __METHOD__)) {
            return $this->runtimeCache->get($className, __METHOD__);
        }

        $propertyDefinitionCollection = $this->createPropertyDefinitionCollection();

        $propertyDefinitionCollection->import($this->propertiesAndTypes());

        $this->runtimeCache->set($className, $propertyDefinitionCollection, __METHOD__);

        return $propertyDefinitionCollection;
    }

    /**
     * Get the list of accessible properties and their associated types as an
     * associative array.
     * <code>
     * return array(
     *     'propertyName'  => 'propertyType'
     *     'propertyName2' => 'null'
     * );
     * </code>
     *
     * @return  array
     */
    abstract protected function propertiesAndTypes();

    /**
     * Retrieve an instance of the default property definition object
     *
     * @return PropertyDefinitionCollectionInterface
     */
    protected function createPropertyDefinitionCollection()
    {
        if (is_null(self::$prototypePropertyDefinitionCollection)) {
            self::$prototypePropertyDefinitionCollection = new PropertyDefinitionCollection();
        }

        return clone self::$prototypePropertyDefinitionCollection;
    }

    /**
     * Unset the object properties defined by $keys
     *
     * @param array $keys
     */
    abstract protected function unsetProperties($keys);

    /**
     * {@inheritdoc}
     */
    public function fromArray($data)
    {
        if (!is_array($data) && !($data instanceof Traversable)) {
            throw new RuntimeException(sprintf(
                "Unable to import from array in class '%s' failed. Argument must be an array or Traversable",
                $this->calledClassName()
            ));
        }

        foreach ($data as $name => $value) {
            try {
                $this->set($name, $value);
            }
            catch (InvalidArgumentException $e) {
                continue;
            }
        }

        return $this;
    }

    /**
     * Get the default property values.
     *
     * @return array
     */
    abstract protected function defaultValues();

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->properties[$name]);
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        if (isset($this->properties[$name])) {
            unset($this->properties[$name]);
        }
    }

    /**
     * handle clone
     */
    public function __clone()
    {
        $this->position = 0;
    }

    /**
     * Standard __call method handler for subclass use.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    protected function call($method, &$arguments)
    {
        $matches = array();

        if (!preg_match('/^(?:(get|set|is)_?)(\w+)$/i', $method, $matches)) {
            return null;
        }

        $action = $matches[1];
        $name   = $matches[2];

        if ($action === 'is') {
            $name   = "is$name";
            $action = 'get';
        }

        $propertyName = lcfirst($name);

        if ($action === 'set') {
            return $this->set($propertyName, $arguments[0]);
        }
        else {
            return $this->get($propertyName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        if (array_key_exists($name, $this->privileged)) {
            return $this;
        }

        $this->properties[$name] = $this->marshal->ratify(
            $value,
            $this->definitions->get($name)
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function &get($name)
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new RuntimeException(sprintf(
                "Attempt to access property '%s' of class '%s' failed. Property does not exist.",
                $name,
                $this->calledClassName()
            ));
        }

        return $this->properties[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $keys = array_keys($this->properties);

        return $keys[$this->position];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($recursive = true)
    {
        if (!$recursive) {
            $copy = array();

            foreach (array_keys($this->properties) as $key) {
                $copy[$key] = $this->$key;
            }

            return $copy;
        }

        return $this->convert(new PhpArray());
    }

    /**
     * {@inheritdoc}
     */
    public function typeof($name)
    {
        $definition = $this->definitions->get($name);

        if ($definition instanceof PropertyDefinitionInterface) {
            return $definition->getType();
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $this->properties = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $keys = array_keys($this->properties);
        $key  = null;

        if ($this->position < count($keys)) {
            $key = $keys[$this->position];
        }

        return !is_null($key) ? array_key_exists($key, $this->properties) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(ConverterStrategyInterface $strategy)
    {
        return $strategy->convert($this);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $keys = array_keys($this->properties);
        $key  = $keys[$this->position];

        return $this->get($key);
    }

    /**
     * {@inheritdoc}
     */
    final public function dump($html = true)
    {
        echo $this->convert(new Dump($html));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * Sort the object by value
     *
     * @return bool
     */
    public function asort()
    {
        return asort($this->properties);
    }

    /**
     * Sort the object by property/key
     *
     * @return bool
     */
    public function ksort()
    {
        return ksort($this->properties);
    }

    /**
     * Sort the object by value using natural order (case insensitive)
     *
     * @return bool
     */
    public function natcasesort()
    {
        return natcasesort($this->properties);
    }

    /**
     * Sort the object by value using natural order
     *
     * @return bool
     */
    public function natsort()
    {
        return natsort($this->properties);
    }

    /**
     * Sort the object by value with a user defined function
     *
     * @param callable $cmp_function
     *
     * @return bool
     */
    public function uasort(callable $cmp_function)
    {
        return uasort($this->properties, $cmp_function);
    }

    /**
     * Sort the object by property/key with a user defined function
     *
     * @param callable $cmp_function
     *
     * @return bool
     */
    public function uksort(callable $cmp_function)
    {
        return uksort($this->properties, $cmp_function);
    }
}
