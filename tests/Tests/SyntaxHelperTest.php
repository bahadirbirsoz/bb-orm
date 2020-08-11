<?php

namespace BbOrm\Test\Tests;

use PHPUnit\Framework\TestCase;
use BbOrm\SyntaxHelper;
class SyntaxHelperTest extends TestCase
{

    public function testCanConvertFromSnakeToCamelCase(){
        $this->assertSame('camelCaseName', SyntaxHelper::snakeToCamel("camel_case_name") );
    }

    public function testCanConvertFromCamelToSnake(){
        $this->assertSame('snake_case_name', SyntaxHelper::camelToSnake("snakeCaseName") );
    }

}