<?php

namespace Drupal\tc_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Path\AliasManagerInterface;
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
     * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
     */
    public function __construct(AliasManagerInterface $aliasManager)
    {
        $this->aliasManager = $aliasManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('path.alias_manager')
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

}
