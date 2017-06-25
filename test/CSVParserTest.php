<?php namespace KenBarbour\Util\CSVParser\Test;

use KenBarbour\Util\CSVParser\CSVParser;
use PHPUnit\Framework\TestCase;

class CSVParserTest extends TestCase
{
    function testFromFile()
    {
        $parser = new CSVParser();
        $parser->fromFile('php://memory');
        try {
            $parser->fromFile('missing-file.txt');
            $this->fail('Should fail on missing file');
        } catch (\Exception $e) {
            //This is ok
        }
    }

    function testMapHeadings()
    {
        $str = "\"Heading1\",\"Heading2\",\"Heading3\"\n1,2,3\n4,5,6";
        $parser = new CSVParser();
        $parser->fromString($str)
            ->hasHeader();
        $line = $parser->fetch();
        $this->assertArrayHasKey('Heading1',$line);
        $this->assertArrayHasKey('Heading2',$line);
        $this->assertArrayHasKey('Heading3',$line);
    }

    function testAltHeadings()
    {
        $str = "\"Heading1\",\"Heading2\",\"Heading3\"\n1,2,3\n4,5,6";
        $parser = new CSVParser();
        $parser->fromString($str)
            ->hasHeader()
            ->altHeading('real1','Heading1')
            ->altHeading('real2','Heading2')
            ->altHeading('real3','Heading3');
        $line = $parser->fetch();
        $this->assertArrayHasKey('real1',$line);
        $this->assertArrayHasKey('real2',$line);
        $this->assertArrayHasKey('real3',$line);
        $this->assertArrayNotHasKey('Heading1',$line);
        $this->assertArrayNotHasKey('Heading2',$line);
        $this->assertArrayNotHasKey('Heading3',$line);
    }

    function testNullElements()
    {
        $str = "\"Heading1\",\"Heading2\",\"Null\"\n1,2,\\N";
        $parser = new CSVParser();
        $parser->fromString($str)
            ->hasHeader();
        $line = $parser->fetch();
        $this->assertArrayHasKey('Heading1',$line);
        $this->assertArrayHasKey('Heading2',$line);
        $this->assertArrayHasKey('Null',$line);
        $this->assertEquals(1,$line['Heading1']);
        $this->assertEquals(2,$line['Heading2']);
        $this->assertNull($line['Null']);
    }
}