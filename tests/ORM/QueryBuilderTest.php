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

    public function testInsert()
    {
        $query = (new QueryBuilder())->insertInto('users')->values([
            'name' => 'Hello',
            'firstname' => 'World',
            'age' => 34
        ])->getQuery();
        $this->assertEquals('INSERT INTO users (name, firstname, age) VALUES ("Hello", "World", "34")', $query);

        $query = (new QueryBuilder())->insertInto('users', 'u')->values([
            'u.name' => 'Hello',
            'u.firstname' => 'World',
            'u.age' => 34
        ])->getQuery();
        $this->assertEquals('INSERT INTO users AS u (u.name, u.firstname, u.age) VALUES ("Hello", "World", "34")', $query);

        $query = (new QueryBuilder())->insertInto('users')->values([
            'name' => ':name',
            'firstname' => ':firstname',
            'age' => ':age'
        ])->getQuery();
        $this->assertEquals('INSERT INTO users (name, firstname, age) VALUES (:name, :firstname, :age)', $query);

        $query = (new QueryBuilder())->insertInto('users')->values([
            'name' => '?',
            'firstname' => '?',
            'age' => '?'
        ])->getQuery();
        $this->assertEquals('INSERT INTO users (name, firstname, age) VALUES (?, ?, ?)', $query);

        $query = (new QueryBuilder())->insertInto('users')->values([
            'name' => '?',
            'firstname' => '?',
            'age' => '?'
        ])->getQuery();
        $this->assertEquals('INSERT INTO users (name, firstname, age) VALUES (?, ?, ?)', $query);
    }

    public function testUpdate()
    {
        $query = (new QueryBuilder())->update('users')->values([
            'name' => 'Hello',
            'firstname' => 'World',
            'age' => 34
        ])->where([
            'id' => ':id'
        ])->getQuery();
        $this->assertEquals('UPDATE users SET name = "Hello", firstname = "World", age = "34" WHERE id = :id', $query);

        $query = (new QueryBuilder())->update('users', 'u')->values([
            'u.name' => 'Hello',
            'u.firstname' => 'World',
            'u.age' => 34
        ])->where([
            'u.id' => ':id',
        ])->getQuery();
        $this->assertEquals('UPDATE users AS u SET u.name = "Hello", u.firstname = "World", u.age = "34" WHERE u.id = :id', $query);

        $query = (new QueryBuilder())->update('u')->from('users', 'u')->values([
            'u.name' => 'Hello',
            'u.firstname' => 'World',
            'u.age' => 34
        ])->join('profil', [
            ['p.id', '=', 'u.id_profil']
        ], 'p')->where([
            'u.id' => ':id',
            'p.id' => 3
        ])->getQuery();
        $this->assertEquals('UPDATE u FROM users AS u INNER JOIN profil AS p ON p.id = u.id_profil SET u.name = "Hello", u.firstname = "World", u.age = "34" WHERE u.id = :id AND p.id = 3', $query);

        $query = (new QueryBuilder())->update('u')->from('users', 'u')->values([
            'u.name' => ':name',
            'u.firstname' => ':firstname',
            'u.age' => ':age'
        ])->join('profil', [
            ['p.id', '=', 'u.id_profil']
        ], 'p')->where([
            'u.id' => ':id',
            'p.id' => 3
        ])->getQuery();
        $this->assertEquals('UPDATE u FROM users AS u INNER JOIN profil AS p ON p.id = u.id_profil SET u.name = :name, u.firstname = :firstname, u.age = :age WHERE u.id = :id AND p.id = 3', $query);
    }
}
