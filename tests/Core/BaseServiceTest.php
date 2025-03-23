<?php

use PHPUnit\Framework\TestCase;
use System\Core\BaseService;

class BaseServiceTest extends TestCase
{
    private $baseService;

    protected function setUp(): void
    {
        $this->baseService = new BaseService();
    }

    public function testToObject()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'age' => 30
        ];

        $result = $this->baseService->toObject($data);

        $this->assertIsObject($result);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals('john.doe@example.com', $result->email);
        $this->assertEquals(30, $result->age);
    }

    public function testToArrayObjects()
    {
        $data = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'age' => 30
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'age' => 25
            ]
        ];

        $result = $this->baseService->toArrayObjects($data);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertIsObject($result[0]);
        $this->assertEquals('John Doe', $result[0]->name);
        $this->assertEquals('john.doe@example.com', $result[0]->email);
        $this->assertEquals(30, $result[0]->age);

        $this->assertIsObject($result[1]);
        $this->assertEquals('Jane Smith', $result[1]->name);
        $this->assertEquals('jane.smith@example.com', $result[1]->email);
        $this->assertEquals(25, $result[1]->age);
    }
}