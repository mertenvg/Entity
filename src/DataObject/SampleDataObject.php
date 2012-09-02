<?php

namespace DataObject;

/**
 * A sample DataObject using all known property types.
 *
 * @author     Merten van Gerven
 * @package    DataObject
 */
class SampleDataObject extends ObjectPropertyDataObject implements PermitDynamicPropertiesInterface
{

    /**
     * @var bool
     */
    public $testBool = false;

    /**
     * @var boolean Alias of bool
     */
    public $testBoolean = true;

    /**
     * @var integer Alias of int
     */
    public $testInteger = 12345;

    /**
     * @var int
     */
    public $testInt = 67890;

    /**
     * @var float
     */
    public $testFloat = 12.123;

    /**
     * @var double Alias of float
     */
    public $testDouble = 34.345;

    /**
     * @var numeric
     */
    public $testNumeric = 34567;

    /**
     * @var long Alias of int
     */
    public $testLong = 23456;

    /**
     * @var real Aliss of float
     */
    public $testReal = 56.567;

    /**
     * @var resource
     */
    public $testResource;

    /**
     * @var scalar Basix data type including int, float, string, bool
     */
    public $testScalar;

    /**
     * @var string
     */
    public $testString = "test string";

    /**
     * @var mixed
     */
    public $testMixed ;

    /**
     * @var array
     */
    public $testArray = array('1', '2', '3');

    /**
     * @var stdClass
     */
    public $testStdClass;

    /**
     * @var DataObject\SampleDataObject
     */
    public $testDataObject;

    /**
     * @var object
     */
    public $testObject;

    /**
     * @var null
     */
    public $testNull;

    /**
     * @var callable
     */
    public $testCallable;

    /**
     * @var DataObject\SampleDataObject[]
     */
    public $testTypedArray1;

    /**
     * @var array<DataObject\SampleDataObject>
     */
    public $testTypedArray2;

    /**
     * @var array[]
     */
    public $testTypedArray3;

    /**
     * @var integer[]
     */
    public $testTypedArray4;


}
