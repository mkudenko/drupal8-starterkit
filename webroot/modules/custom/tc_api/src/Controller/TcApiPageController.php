<?php

namespace Drupal\tc_api\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\tc_api\Helpers\TcPathParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * TC API page controller.
 */
class TcApiPageController extends TcApiBaseController implements ContainerInjectionInterface
{

    /**
     * @var AliasManagerInterface
     */
    protected $aliasManager;

    /**
     * @var EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * @param AliasManagerInterface $aliasManager
     * @param EntityTypeManagerInterface $entityTypeManager
     */
    public function __construct(AliasManagerInterface $aliasManager, EntityTypeManagerInterface $entityTypeManager)
    {
        $this->aliasManager = $aliasManager;
        $this->entityTypeManager = $entityTypeManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('path.alias_manager'),
            $container->get('entity_type.manager')
        );
    }

    /**
     * Returns JSON object with page data.
     *
     * Page URL is passed as a GET parameter.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getPage(Request $request)
    {
        $url_alias = $request->get('url');

        if (!$url_alias) {
            return $this->jsonInvalidRequest('Missing URL parameter.');
        }

        $path = $this->aliasManager->getPathByAlias($url_alias);

        $pathParser = new TcPathParser($path);

        try {
            $node = $this->entityTypeManager->getStorage($pathParser->getEntityType())->load($pathParser->getEntityId());
        } catch (\Exception $e) {
            return $this->jsonInvalidRequest($e->getMessage());
        }

        return $this->jsonSuccess($node->toArray());
    }

}
