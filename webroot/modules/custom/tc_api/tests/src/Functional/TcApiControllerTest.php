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
        'node',
        'path',
        'tc_api',
    ];

    private $expectedManifest;

    private $nodesTitles;

    public function setUp()
    {
        parent::setUp();

        $faker = \Faker\Factory::create();

        $contentTypeName = $faker->word;
        $this->createContentType($contentTypeName);

        $nodesData = [
            [
                'title' => $faker->sentence(3),
                'alias' => '/' . implode('/', $faker->words()),
            ],
            [
                'title' => $faker->sentence(3),
                'alias' => '',
            ],
            [
                'title' => $faker->sentence(3),
                'alias' => '/' . $faker->word . '/' . $faker->randomDigit,
            ],
        ];

        foreach ($nodesData as $key => $nodeData) {
            $nodesData[$key]['node'] = $this->createNode($contentTypeName, $nodeData['title'], $nodeData['alias']);
        }

        $nodeTitles = [];

        $expectedManifest = ['urls' => []];
        foreach ($nodesData as $key => $nodeData) {
            $nid = $nodeData['node']->id();
            $url = ($nodeData['alias']) ?: '/node/' . $nid;
            $expectedManifest['urls'][] = [
                'id' => $nid,
                'url' => $url,
                'changed_time' => $nodeData['node']->getChangedTime(),
            ];
            $nodeTitles[$url] = $nodeData['title'];
        }

        $this->expectedManifest = $expectedManifest;
        $this->nodesTitles = $nodeTitles;
    }

    /**
     * That test tests the all the endpoints that the denormalizer microservice would hit.
     *
     * It's not broken down into smaller tests because every webtest takes about 50 seconds to set up.
     */
    public function testApiWorkflow()
    {
        // Test the manifest endpoint.
        $response = $this->drupalGet('/api/manifest');
        $this->assertEquals(200, $this->getSession()->getStatusCode());
        $responseArray = json_decode($response, true);
        $this->assertEquals($this->expectedManifest, $responseArray);

        // Test the endpoints for each node.
        foreach ($responseArray['urls'] as $manifestData) {
            $response = $this->drupalGet('/api/page', ['query' => ['url' => $manifestData['url']]]);
            $this->assertEquals(200, $this->getSession()->getStatusCode());
            $responseArray = json_decode($response, true);
            $this->assertEquals($this->nodesTitles[$manifestData['url']], $responseArray['title'][0]['value']);
        }

        // Test the node endpoint exception.
        $response = $this->drupalGet('/api/page');
        $this->assertEquals(400, $this->getSession()->getStatusCode());
        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('message', $responseArray);

        // Test the node enpoint exception.
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
     * Creates a node with a specified url alias.
     *
     * @param string $type
     * @param string $urlAlias
     *
     * @return Node
     */
    private function createNode($type, $title, $urlAlias = '')
    {
        $node = Node::create(array(
            'type' => $type,
            'title' => $title,
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
