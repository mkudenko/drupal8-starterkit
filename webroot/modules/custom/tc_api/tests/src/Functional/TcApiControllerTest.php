<?php namespace Drupal\Tests\tc_api\Functional;

use Drupal\Core\Language\LanguageInterface;
use Drupal\simpletest\BrowserTestBase;
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
        $faker = \Faker\Factory::create();

        $aliases = [
            '/' . implode('/', $faker->words()),
            '',
            '/' . implode('/', $faker->words(1)) . '/' . $faker->randomDigit,
        ];

        $contentTypeName = 'tmp';

        $this->createContentType($contentTypeName);

        $nodes = [];
        foreach ($aliases as $key => $alias) {
            $nodes[$key] = $this->createNode($contentTypeName, $alias);
        }

        $expectedManifest = ['urls' => []];
        foreach ($nodes as $key => $node) {
            $expectedManifest['urls'][] = [
                'url' => ($aliases[$key]) ?: '/node/' . $node->id(),
                'changed_time' => $node->getChangedTime(),
            ];
        }
        $response = $this->drupalGet('/api/manifest');
        $this->assertEquals(200, $this->getSession()->getStatusCode());
        $responseArray = json_decode($response, true);
        $this->assertEquals($expectedManifest, $responseArray);

        foreach ($responseArray['urls'] as $manifestData) {
            $response = $this->drupalGet('/api/page', ['query' => ['url' => $manifestData['url']]]);
            $this->assertEquals(200, $this->getSession()->getStatusCode());
            $this->assertJson($response);
        }

        $response = $this->drupalGet('/api/page');
        $this->assertEquals(400, $this->getSession()->getStatusCode());
        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('message', $responseArray);

        $response = $this->drupalGet('/api/page', ['query' => ['url' => '/invalid/alias']]);
        $this->assertEquals(400, $this->getSession()->getStatusCode());
        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('message', $responseArray);
    }

    /**
     * Creates a content type.
     *
     * @param string $type
     */
    private function createContentType($type)
    {
        $content_type = $this->container->get('entity.manager')->getStorage('node_type')->create(array(
            'name' => $type,
            'title_label' => 'Title',
            'type' => $type,
            'create_body' => TRUE,
        ));
        $content_type->save();
    }

    /**
     * Creates a node.
     *
     * @param string $type
     * @param string $urlAlias
     *
     * @return Node
     */
    private function createNode($type, $urlAlias = '')
    {
        $node = Node::create(array(
            'type' => $type,
            'title' => 'your title',
            'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
            'uid' => '1',
            'status' => 1,
            'field_fields' => array(),
        ));

        $node->save();

        if ($urlAlias) {
            $aliasStorage = $this->container->get('path.alias_storage');
            $aliasStorage->save('/node/' . $node->id(), $urlAlias, LanguageInterface::LANGCODE_NOT_SPECIFIED, 0);
        }

        return $node;
    }

}
