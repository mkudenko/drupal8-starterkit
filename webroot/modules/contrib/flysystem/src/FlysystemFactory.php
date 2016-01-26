<?php

/**
 * @file
 * Contains \Drupal\flysystem\FlysystemFactory.
 */

namespace Drupal\flysystem;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Site\Settings;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Replicate\ReplicateAdapter;
use Psr\Log\LoggerInterface;

/**
 * A factory for Flysystem filesystems.
 */
class FlysystemFactory {

  use SerializationStopperTrait;

  /**
   * Default settings.
   *
   * @var array
   */
  protected $defaults = [
    'driver' => '',
    'config' => [],
    'replicate' => FALSE,
    'cache' => FALSE,
  ];

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * A cache of filesystems.
   *
   * @var \League\Flysystem\FilesystemInterface[]
   */
  protected $filesystems = [];

  /**
   * The logger to use.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The Flysystem plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Created plugins.
   *
   * @var \Drupal\flysystem\Plugin\FlysystemPluginInterface[]
   */
  protected $plugins = [];

  /**
   * Settings for stream wrappers.
   *
   * @var array
   */
  protected $settings;

  /**
   * Constructs a FlysystemFactory object.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   The plugin manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Site\Settings $settings
   *   The system settings.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(PluginManagerInterface $plugin_manager, CacheBackendInterface $cache, Settings $settings, LoggerInterface $logger) {
    $this->pluginManager = $plugin_manager;
    $this->cacheBackend = $cache;
    $this->logger = $logger;
    $this->settings = $settings->get('flysystem', []);

    // Apply defaults.
    foreach ($this->settings as $scheme => $configuration) {
      $this->settings[$scheme] += $this->defaults;
    }
  }

  /**
   * Returns the filesystem for a given scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return \League\Flysystem\FilesystemInterface
   *   The filesystem for the scheme.
   */
  public function getFilesystem($scheme) {
    if (!isset($this->filesystems[$scheme])) {
      $this->filesystems[$scheme] = new Filesystem($this->getAdapter($scheme));
    }

    return $this->filesystems[$scheme];
  }

  /**
   * Returns the plugin for a scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return \Drupal\flysystem\Plugin\FlysystemPluginInterface
   *   The plugin.
   */
  public function getPlugin($scheme) {
    if (!isset($this->plugins[$scheme])) {
      $settings = $this->getSettings($scheme);

      $this->plugins[$scheme] = $this->pluginManager->createInstance($settings['driver'], $settings['config']);
    }

    return $this->plugins[$scheme];
  }

  /**
   * Calls FlysystemPluginInterface::ensure() on each plugin.
   *
   * @param bool $force
   *   (optional) Wheter to force the insurance. Defaults to false.
   *
   * @return array
   *   Errors keyed by scheme.
   */
  public function ensure($force = FALSE) {
    $errors = [];

    foreach ($this->settings as $scheme => $configuration) {

      foreach ($this->getPlugin($scheme)->ensure($force) as $error) {

        $errors[$scheme][] = $error;
        $this->logger->log($error['severity'], $error['message'], $error['context']);
      }
    }

    return $errors;
  }

  /**
   * Returns the adapter for a scheme.
   *
   * @param string $scheme
   *   The scheme to find an adapter for.
   *
   * @return \League\Flysystem\AdapterInterface
   *   The correct adapter from settings.
   */
  protected function getAdapter($scheme) {
    $settings = $this->getSettings($scheme);

    $adapter = $this->getPlugin($scheme)->getAdapter();

    if ($settings['replicate']) {
      $replica = $this->getAdapter($settings['replicate']);
      $adapter = new ReplicateAdapter($adapter, $replica);
    }

    if ($settings['cache']) {
      $cache = new DrupalFlysystemCache($this->cacheBackend, 'flysystem:' . $scheme);
      $adapter = new CachedAdapter($adapter, $cache);
    }

    return $adapter;
  }

  /**
   * Finds the settings for a given scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return array
   *   The settings array from settings.php.
   */
  protected function getSettings($scheme) {
    return isset($this->settings[$scheme]) ? $this->settings[$scheme] : $this->defaults;
  }

}
