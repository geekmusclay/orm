<?php

namespace Geekmusclay\ORM\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Geekmusclay\ORM\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    public function testSelect()
    {
        $query = (new QueryBuilder())->select('*')->from('test_table')->getQuery();
        $this->assertEquals('SELECT * FROM test_table', $query);

        $query = (new QueryBuilder())->select(['name', 'email'])->from('test_table')->getQuery();
        $this->assertEquals('SELECT name, email FROM test_table', $query);
    }

    public function testCumulateSelect()
    {
        $query = (new QueryBuilder())->select('name')->select('email')->from('test_table')->getQuery();
        $this->assertEquals('SELECT name, email FROM test_table', $query);

        $query = (new QueryBuilder())->select('id, name')->select('email')->from('test_table')->getQuery();
        $this->assertEquals('SELECT id, name, email FROM test_table', $query);

        $query = (new QueryBuilder())->select(['id', 'name, lastname'])->select('email')->from('test_table')->getQuery();
        $this->assertEquals('SELECT id, name, lastname, email FROM test_table', $query);
    }

    public function testFromWithName()
    {
        $query = (new QueryBuilder())->select('t.id')->from('test_table', 't')->getQuery();
        $this->assertEquals('SELECT t.id FROM test_table AS t', $query);
    }

    public function testWhere()
    {
        $query = (new QueryBuilder())->select('*')->from('test_table')->where([
            'id' => 3,
            'name' => ':name'
        ])->getQuery();
        $this->assertEquals('SELECT * FROM test_table WHERE id = 3 AND name = :name', $query);
    }

    public function testNoSelect()
    {
        $this->expectException(Exception::class);
        (new QueryBuilder())->from('test_table')->getQuery();
    }

    public function testNoFrom()
    {
        $this->expectException(Exception::class);
        (new QueryBuilder())->select('*')->getQuery();
    }

    public function testWithOrder()
    {
        $query = (new QueryBuilder())->select('*')->from('test_table')->orderBy('id', 'DESC')->getQuery();
        $this->assertEquals('SELECT * FROM test_table ORDER BY id DESC', $query);
        
        $query = (new QueryBuilder())->select(['name', 'email'])->from('test_table')->orderBy('id')->getQuery();
        $this->assertEquals('SELECT name, email FROM test_table ORDER BY id ASC', $query);
    }

    public function testWithLimit()
    {
        $query = (new QueryBuilder())->select('*')->from('test_table')->limit(10)->getQuery();
        $this->assertEquals('SELECT * FROM test_table LIMIT 10', $query);
    }

    public function testWithOrderAndLimit()
    {
        $query = (new QueryBuilder())->select('*')->from('test_table')->orderBy('id', 'DESC')->limit(10)->getQuery();
        $this->assertEquals('SELECT * FROM test_table ORDER BY id DESC LIMIT 10', $query);
    }

    public function testUnderDisorder()
    {
        $query = (new QueryBuilder())->limit(10)->where([
            'id' => 3,
            'name' => ':name'
        ])->orderBy('id', 'DESC')->from('test_table')->select(['id', 'name, email'])->getQuery();
        $this->assertEquals('SELECT id, name, email FROM test_table WHERE id = 3 AND name = :name ORDER BY id DESC LIMIT 10', $query);
    }

    public function testJoins()
    {
        $query = (new QueryBuilder())->select('id, name')->from('test_table', 't')->join('other_table', [
            ['t.id_other', '=', 'o.id'],
            ['o.price', '>', 3]
        ], 'o')->getQuery();
        $this->assertEquals('SELECT id, name FROM test_table AS t INNER JOIN other_table AS o ON t.id_other = o.id AND o.price > 3', $query);

        $query = (new QueryBuilder())->select('id, name')->from('test_table', 't')->join('other_table', [
            ['t.id_other', '=', 'o.id']
        ], 'o', 'LEFT')->getQuery();
        $this->assertEquals('SELECT id, name FROM test_table AS t LEFT JOIN other_table AS o ON t.id_other = o.id', $query);
    }
}
