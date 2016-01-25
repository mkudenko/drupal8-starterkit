<?php

/**
 * @file
 * Contains \Drupal\flysystem\Plugin\FlysystemUrlTrait.
 */

namespace Drupal\flysystem\Plugin;

use Drupal\Core\Routing\UrlGeneratorTrait;
use League\Flysystem\Util;

/**
 * Helper trait for generating URLs from adapter plugins.
 */
trait FlysystemUrlTrait
{

    use UrlGeneratorTrait;

    /**
     * Returns a web accessible URL for the resource.
     *
     * This function should return a URL that can be embedded in a web page
     * and accessed from a browser. For example, the external URL of
     * "youtube://xIpLd0WQKCY" might be
     * "http://www.youtube.com/watch?v=xIpLd0WQKCY".
     *
     * @param string $uri
     *   The URI to provide a URL for.
     *
     * @return string
     *   Returns a string containing a web accessible URL for the resource.
     */
    public function getExternalUrl($uri)
    {
        $path = str_replace('\\', '/', $this->getTarget($uri));

        $arguments = [
            'scheme'   => $this->getScheme($uri),
            'filepath' => $path,
        ];

        return $this->url('flysystem.serve', $arguments, ['absolute' => true]);
    }

    /**
     * Returns the target file path of a URI.
     *
     * @param string $uri
     *   The URI.
     *
     * @return string
     *   The file path of the URI.
     */
    protected function getTarget($uri)
    {
        return Util::normalizePath(substr($uri, strpos($uri, '://') + 3));
    }

    /**
     * Returns the scheme from the internal URI.
     *
     * @param string $uri
     *   The URI.
     *
     * @return string
     *   The scheme.
     */
    protected function getScheme($uri)
    {
        return substr($uri, 0, strpos($uri, '://'));
    }

}
