<?php

namespace Drupal\tc_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\tc_api\Helpers\TcPathParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Created by PhpStorm.
 * User: mkudenko
 * Date: 22/01/16
 * Time: 9:32 AM
 */
class TcApiController extends ControllerBase implements ContainerInjectionInterface
{

    /**
     * @var \Drupal\Core\Path\AliasStorageInterface
     */
    protected $aliasManager;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
     */
    public function __construct(AliasManagerInterface $aliasManager, Connection $connection)
    {
        $this->aliasManager = $aliasManager;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('path.alias_manager'),
            $container->get('database')
        );
    }

    public function getPage(Request $request)
    {
        $url_alias = $request->get('url');

        if (!$url_alias) {
            return new JsonResponse(['message' => 'Missing URL parameter.'], 400);
        }

        $path = $this->aliasManager->getPathByAlias($url_alias);

        $pathParser = new TcPathParser($path);

        try {
            $node = \Drupal::entityTypeManager()->getStorage($pathParser->getEntityType())->load($pathParser->getEntityId());
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        return new JsonResponse($node->toArray());
    }

    public function getManifest()
    {
        $pages = [];

        $nodesQueryResult = $this->connection->select('node_field_data')
            ->fields('node_field_data', ['nid', 'changed'])
            ->execute();

        $nodeSourceUrls = [];
        foreach ($nodesQueryResult as $nodeData) {
            $nodeSourceUrl = '/node/' . $nodeData->nid;
            $nodeSourceUrls[] = $nodeSourceUrl;
            $pages[] = [
                'id' => $nodeData->nid,
                'source' => $nodeSourceUrl,
                'changed_time' => $nodeData->changed,
            ];
        }

        $aliasesQuery = $this->connection->select('url_alias')
            ->fields('url_alias', ['source', 'alias']);
        $aliasesQuery->condition('source', $nodeSourceUrls, 'IN');
        $aliases = $aliasesQuery->execute()->fetchAllAssoc('source');

        foreach ($pages as $key => $page) {
            if (isset($aliases[$page['source']])) {
                $page['url'] = $aliases[$page['source']]->alias;
            } else {
                $page['url'] = $page['source'];
            }

            unset($page['source']);
            $pages[$key] = $page;
        }

        return new JsonResponse(['pages' => $pages]);
    }

}
