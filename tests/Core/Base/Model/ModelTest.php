<?php

use Loader\Container;
use System\Core\Base\Model\Model;
use System\Core\Test\TestCase;

class DummyModel extends Model
{
    // Allow injecting a mock db for testing
    public function setDb($db) { $this->db = $db; }
}
/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
class ModelTest extends TestCase
{
    protected $model;

    protected $table = 'users';

    protected $data = ['name' => 'John'];

    protected $primary_key = 'id';
    protected $id = 1;

    public function testModelInitialization()
    {
        $model = $this->getMockForAbstractClass(Model::class);
        $this->assertInstanceOf(Model::class, $model);
    }


    public function setUp(): void
    {
        parent::setUp();
        // Use DummyModel to allow db injection
        // $this->model = $this->getMockBuilder(DummyModel::class)
        //     ->disableOriginalConstructor()
        //     ->onlyMethods([])
        //     ->getMock();
        // $this->model->setDb($this->db);

        $this->db->shouldReceive('from')->with($this->table)->andReturnSelf();
        $this->db->shouldReceive('where')->with([$this->primary_key => $this->id])->andReturnSelf();
        $this->db->shouldReceive('getOne')->andReturn($this->data);
        $this->db->shouldReceive('getAll')->andReturn([$this->data]);
        $this->db->shouldReceive('selectAll')->andReturnSelf();

        $this->db->shouldReceive('update')
            ->with($this->table, $this->data, [$this->primary_key => $this->id])
            ->andReturn(true);

        $this->db->shouldReceive('insert')
            ->with($this->table, $this->data)
            ->andReturn(true);

        $this->db->shouldReceive('delete')
            ->with($this->table, [$this->primary_key => $this->id])
            ->andReturn(true);

                Container::set('db', $this->db);

        $this->model = $this->getMockForAbstractClass(Model::class);
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */

    public function testFind()
    {

        $expected = ['name' => 'John'];

        

        $result = $this->model->find($this->table, $this->id, $this->primary_key);
        $this->assertEquals($expected, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAll()
    {
        $expected = [
            ['name' => 'John'],
        ];

        $result = $this->model->all($this->table);
        $this->assertEquals($expected, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */

    public function testInsert()
    {
        
        $result = $this->model->insert($this->table, $this->data);
        $this->assertTrue($result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */

    public function testUpdate()
    {

        $result = $this->model->update($this->table, $this->id, $this->data, $this->primary_key);
        $this->assertTrue($result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDelete()
    {

        
        $result = $this->model->delete($this->table, $this->id, $this->primary_key);
        $this->assertTrue($result);
    }
}