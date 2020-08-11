<?php

namespace BbOrm\Test\Tests;

use BbOrm\Connection;
use BbOrm\Model;
use PHPUnit\Framework\TestCase ;

class ConnectionTest extends TestCase
{

    public function testCanCreateValidConnectionObject(){
        $bbConnection = Connection::getInstance();
        $pdoConnection = $bbConnection->getConnection();
        $this->assertInstanceOf(\PDO::class,$pdoConnection);
    }

    public function testCanQueryDatabase(){
        $rawQuery = Model::select("SELECT  NOW() as `ts` ");
        $this->assertEquals(date("Y-m-d H:i:s"),$rawQuery[0]->ts);
    }

}