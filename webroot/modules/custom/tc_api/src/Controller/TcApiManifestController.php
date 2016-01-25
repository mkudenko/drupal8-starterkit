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
        $pagesData = $this->getSourceUrlsAndChangedTimestampsForExistingPages();

        $sourceUrls = $this->getListOfSourceUrls($pagesData);

        $urlAliases = $this->getPageUrlAliases($sourceUrls);

        $this->addUrlAliasesToPagesData($pagesData, $urlAliases);

        return $this->jsonSuccess(['pages' => $pagesData]);
    }

    /**
     * @return array
     */
    private function getSourceUrlsAndChangedTimestampsForExistingPages()
    {
        $data = [];

        $nodesQueryResult = $this->connection->select('node_field_data')
            ->fields('node_field_data', ['nid', 'changed'])
            ->execute();

        foreach ($nodesQueryResult as $nodeData) {
            $data[] = [
                'source_url' => '/node/' . $nodeData->nid,
                'changed_time' => $nodeData->changed,
            ];
        }

        return $data;
    }

    /**
     * @param array $pagesData
     *
     * @return array
     */
    private function getListOfSourceUrls($pagesData)
    {
        $sourceUrls = [];
        foreach ($pagesData as $pageData) {
            $sourceUrls[] = $pageData['source_url'];
        }

        return $sourceUrls;
    }

    /**
     * @param array $sourceUrls
     *
     * @return array
     */
    private function getPageUrlAliases($sourceUrls)
    {
        $aliases = [];

        $aliasesQuery = $this->connection->select('url_alias')
            ->fields('url_alias', ['source', 'alias']);
        $aliasesQuery->condition('source', $sourceUrls, 'IN');

        $result = $aliasesQuery->execute();

        foreach ($result as $record) {
            $aliases[$record->source] = $record->alias;
        }

        return $aliases;
    }

    /**
     * @param array $pagesData
     * @param array $urlAliases
     *
     * @return array
     */
    private function addUrlAliasesToPagesData(&$pagesData, $urlAliases)
    {
        foreach ($pagesData as $key => $pageData) {
            $pageSourceUrl = $pageData['source_url'];

            if (isset($urlAliases[$pageSourceUrl])) {
                $pageData['url'] = $urlAliases[$pageSourceUrl];
            } else {
                $pageData['url'] = $pageSourceUrl;
            }

            unset($pageData['source_url']);
            $pagesData[$key] = $pageData;
        }
    }

}
