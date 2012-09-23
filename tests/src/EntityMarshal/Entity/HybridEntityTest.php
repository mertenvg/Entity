<?php

namespace EntityMarshal\Entity;

require_once dirname(__FILE__) . '/../../../../src/EntityMarshal/Entity/HybridEntity.php';

/**
 * Test class for HybridEntity.
 * Generated by PHPUnit on 2012-09-23 at 21:47:06.
 */
class HybridEntityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var HybridEntity
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        /* @var $stub PHPUnit_Framework_MockObject_MockObject */

        $stub = $this->getMockForAbstractClass(
            '\EntityMarshal\Entity\ClassMethodEntity'
        );

        $this->object = $stub;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @covers EntityMarshal\Entity\HybridEntity::calledClassName
     * @todo Implement testCalledClassName().
     */
    public function testCalledClassName()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers EntityMarshal\Entity\HybridEntity::__call
     * @todo Implement test__call().
     */
    public function test__call()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers EntityMarshal\Entity\HybridEntity::__get
     * @todo Implement test__get().
     */
    public function test__get()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers EntityMarshal\Entity\HybridEntity::__set
     * @todo Implement test__set().
     */
    public function test__set()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}


