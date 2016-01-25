<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Adapter\MissingAdapter.
 */

namespace Drupal\flysystem\Flysystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

/**
 * An adapter used when a plugin is missing. It fails at everything.
 */
class MissingAdapter implements AdapterInterface
{

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return false;
    }

}
