<?php

namespace Geekmusclay\ORM\Tests;

use PHPUnit\Framework\TestCase;
use Geekmusclay\ORM\Tests\Fake\FakeModel;

class ModelTest extends TestCase
{
    public function testHydrate()
    {
        $entity = new FakeModel([
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

    public function testSerilization()
    {
        $entity = new FakeModel([
            'id'        => 1,
            'firstname' => 'Hello',
            'lastname'  => 'World',
            'bool'      => true,
            'config'    => [ 'message' => 'Hello World' ]
        ]);
        $json = $entity->serialize();
        $this->assertEquals('{"id":1,"firstname":"Hello","lastname":"World","bool":true,"config":{"message":"Hello World"}}', $json);

        $json = $entity->serialize(['config']);
        $this->assertEquals('{"id":1,"firstname":"Hello","lastname":"World","bool":true}', $json);
    }

    public function testTableName()
    {
        $entity = new FakeModel();
        $this->assertEquals('fake_models', $entity->getTable());
    }
}