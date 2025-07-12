<?php

use Loader\Container;
use System\Core\Base\Module\Module;
use System\Core\Base\Service\Service;
use System\Core\Test\TestCase;

class TestModel
{
    public function find($id)
    {
    }
    public function all()
    {
    }
    public function insert($data)
    {
    }
    public function update($id, $data)
    {
    }
    public function delete($id)
    {
    }
}

class ServiceTest extends TestCase
{
    protected $service;
    protected $model;

    public function setUp(): void
    {
        parent::setUp();
        Container::set('module', Mockery::mock(Module::class));
        $this->service = new Service();
        $this->model = Mockery::mock(TestModel::class);
    }

    public function testGetById()
    {
        $this->model->shouldReceive('find')->with(1)->andReturn((object) ['id' => 1]);
        $result = $this->service->getById($this->model, 1);
        $this->assertEquals((object) ['id' => 1], $result);
    }

    public function testGetAll()
    {
        $expected = [(object) ['id' => 1], (object) ['id' => 2]];
        $this->model->shouldReceive('all')->andReturn($expected);
        $result = $this->service->getAll($this->model);
        $this->assertEquals($expected, $result);
    }

    public function testCreate()
    {
        $data = ['name' => 'John'];
        $this->model->shouldReceive('insert')->with($data)->andReturn(true);
        $result = $this->service->create($this->model, $data);
        $this->assertTrue($result);
    }

    public function testUpdate()
    {
        $id = 1;
        $data = ['name' => 'Jane'];
        $this->model->shouldReceive('update')->with($id, $data)->andReturn(true);
        $result = $this->service->update($this->model, $id, $data);
        $this->assertTrue($result);
    }

    public function testDelete()
    {
        $id = 1;
        $this->model->shouldReceive('delete')->with($id)->andReturn(true);
        $result = $this->service->delete($this->model, $id);
        $this->assertTrue($result);
    }
    public function testToObject()
    {
        $data = ['id' => 1, 'name' => 'John'];
        $object = $this->service->toObject($data);
        $this->assertIsObject($object);
        $this->assertEquals(1, $object->id);
        $this->assertEquals('John', $object->name);
    }

    public function testToArrayOfObject()
    {
        $data = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ];
        $objects = $this->service->toArrayObjects($data);
        $this->assertIsArray($objects);
        $this->assertCount(2, $objects);
        $this->assertIsObject($objects[0]);
        $this->assertEquals('John', $objects[0]->name);
        $this->assertEquals('Jane', $objects[1]->name);
    }
}
