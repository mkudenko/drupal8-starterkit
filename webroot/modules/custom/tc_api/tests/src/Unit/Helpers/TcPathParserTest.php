<?php namespace Drupal\Tests\tc_api\Unit\Helpers;

use Drupal\tc_api\Helpers\TcPathParser;
use Drupal\Tests\UnitTestCase;

/**
 * @group tc_api
 * @covers TcPathParser
 */
class TcPathParserTest extends UnitTestCase
{

    public function testGetEntityDeterminesNodeEntityType()
    {
        $parser = new TcPathParser('/node/1');

        $this->assertEquals('node', $parser->getEntityType());
    }

    /**
     * @expectedException \Drupal\tc_api\Exceptions\TcPathParserException
     */
    public function testGetEntityTypeThrowsExceptionsForUnknownEntityTypes()
    {
        $parser = new TcPathParser('/unknown/1');

        $parser->getEntityType();
    }

    /**
     * @dataProvider entityIdUrls
     */
    public function testGetEntityId($url, $expectedId)
    {
        $parser = new TcPathParser($url);

        $this->assertEquals($expectedId, $parser->getEntityId());
    }

    /**
     * @expectedException \Drupal\tc_api\Exceptions\TcPathParserException
     */
    public function testGetEntityIdThrowsExceptionWhenIdIsNotInUrl()
    {
        $parser = new TcPathParser('/test/without/id');

        $parser->getEntityId();
    }

    public function entityIdUrls()
    {
        return [
            ['/node/154', 154],
            ['/taxonomy/term/364', 364],
        ];
    }

}
