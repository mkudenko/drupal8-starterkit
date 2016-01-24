<?php namespace Drupal\Tests\tc_api\Functional;

use Drupal\Core\Language\LanguageInterface;
use Drupal\simpletest\BrowserTestBase;
use Drupal\touchcast\TouchcastBrowserTestBase;
use GuzzleHttp\Client;
use Drupal\node\Entity\Node;

/**
 * Class TcApiControllerTest
 * @group tc_api
 */
class TcApiControllerTest extends BrowserTestBase
{

    public static $modules = [
        'tc_api',
        'path',
        'node',
    ];

    public function testGetPage()
    {
        $content_type = $this->container->get('entity.manager')->getStorage('node_type')->create(array(
            'name' => 'TmpArticle',
            'title_label' => 'TmpTitle',
            'type' => 'TmpArticle',
            'create_body' => TRUE,
        ));
        $content_type->save();

        $node = Node::create(array(
            'type' => 'TmpArticle',
            'title' => 'your title',
            'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
            'uid' => '1',
            'status' => 1,
            'field_fields' => array(),
        ));

        $node->save();

        $aliasStorage = $this->container->get('path.alias_storage');
        $aliasStorage->save('/node/1', '/test/tmp', LanguageInterface::LANGCODE_NOT_SPECIFIED, 0);

        $response = $this->drupalGet('/api/page', ['query' => ['url' => '/test/tmp']]);
        $this->assertEquals(200, $this->getSession()->getStatusCode());
        $this->assertJson($response);

        $response = $this->drupalGet('/api/page');
        $this->assertEquals(400, $this->getSession()->getStatusCode());
        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('message', $responseArray);

        $response = $this->drupalGet('/api/page', ['query' => ['url' => '/test/tmp1']]);
        $this->assertEquals(400, $this->getSession()->getStatusCode());
        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('message', $responseArray);
    }

}
