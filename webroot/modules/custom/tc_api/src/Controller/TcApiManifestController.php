<?php

namespace Drupal\tc_api\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * TC API manifest controller class.
 */
class TcApiManifestController extends TcApiBaseController implements ContainerInjectionInterface
{

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('database')
        );
    }

    /**
     * Return JSON object with all existing page urls, timestamps of the last change.
     *
     * @return JsonResponse
     */
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

        return $this->jsonSuccess(['pages' => $pages]);
    }

}
