<?php

namespace Geekmusclay\ORM\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Geekmusclay\ORM\Tests\Fake\FakeEntity;

class EntityTest extends TestCase
{
    public function testHydrate()
    {
        $entity = new FakeEntity([
            'id'        => 1,
            'firstname' => 'Hello',
            'lastname'  => 'World',
            'bool'      => true,
            'config'    => [ 'message' => 'Hello World' ]
        ]);

        $this->assertEquals(1, $entity->id);
        $this->assertEquals('Hello', $entity->firstname);
        $this->assertEquals('World', $entity->lastname);
        $this->assertEquals(true, $entity->bool);
        $this->assertEquals([ 'message' => 'Hello World' ], $entity->config);

        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('Hello', $entity->getFirstname());
        $this->assertEquals('World', $entity->getLastname());
        $this->assertEquals(true, $entity->getBool());
        $this->assertEquals([ 'message' => 'Hello World' ], $entity->getConfig());
    }
}