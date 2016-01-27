<?php namespace Drupal\Tests\tc_api\Functional;

use Drupal\touchcast\ConfigTouchcastBrowserTestBase;

/**
 * Class TcApiTest
 * @group tc_api
 */
class TcApiTest extends ConfigTouchcastBrowserTestBase
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

        $contentTypeName = $this->faker->word;
        $this->createContentType($contentTypeName);

        $nodesData = [
            [
                'title' => $this->faker->sentence(3),
                'alias' => '/' . implode('/', $this->faker->words()),
            ],
            [
                'title' => $this->faker->sentence(3),
                'alias' => '',
            ],
            [
                'title' => $this->faker->sentence(3),
                'alias' => '/' . $this->faker->word . '/' . $this->faker->randomDigit,
            ],
        ];

        foreach ($nodesData as $key => $nodeData) {
            $nodesData[$key]['node'] = $this->createNode($contentTypeName, $nodeData['title'], $nodeData['alias']);
        }

        $nodeTitles = [];

        $expectedManifest = ['pages' => []];
        foreach ($nodesData as $key => $nodeData) {
            $nid = $nodeData['node']->id();
            $url = ($nodeData['alias']) ?: '/node/' . $nid;
            $expectedManifest['pages'][] = [
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
    public function testApi()
    {
        $this->checkManifestEndpoint();

        $this->checkPageEndpoint();

        $this->checkPageEndpointThrowsExceptionForMissingUrl();

        $this->checkPageEndpointThrowsExceptionForInvalidUrl();
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

    private function checkManifestEndpoint()
    {
        $response = $this->drupalGet('/api/manifest');
        $this->assertResponseOk();
        $responseArray = json_decode($response, true);
        $this->assertEquals($this->expectedManifest, $responseArray);
    }

    private function checkPageEndpoint()
    {
        foreach ($this->expectedManifest['pages'] as $pageData) {
            $response = $this->drupalGet('/api/page', ['query' => ['url' => $pageData['url']]]);
            $this->assertResponseOk();
            $responseArray = json_decode($response, true);
            $this->assertEquals($this->nodesTitles[$pageData['url']], $responseArray['title'][0]['value']);
        }
    }

    private function checkPageEndpointThrowsExceptionForMissingUrl()
    {
        $response = $this->drupalGet('/api/page');
        $this->assertResponseStatus(400);
        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('message', $responseArray);
    }

    private function checkPageEndpointThrowsExceptionForInvalidUrl()
    {
        $response = $this->drupalGet('/api/page', ['query' => ['url' => '/invalid/alias']]);
        $this->assertResponseStatus(400);
        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('message', $responseArray);
    }

}
