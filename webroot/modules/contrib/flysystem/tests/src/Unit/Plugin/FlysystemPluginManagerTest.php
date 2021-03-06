<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\Plugin\FlysystemPluginManagerTest.
 */

namespace Drupal\Tests\flysystem\Unit\Plugin;

use Drupal\Core\Cache\MemoryBackend;
use Drupal\flysystem\Plugin\FlysystemPluginManager;

/**
 * @coversDefaultClass \Drupal\flysystem\Plugin\FlysystemPluginManager
 * @group flysystem
 */
class FlysystemPluginManagerTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \Drupal\flysystem\Plugin\FlysystemPluginManager
   */
  public function test() {
    $namespaces = new \ArrayObject();
    $cache_backend = new MemoryBackend('bin');
    $module_handle = $this->getMock('Drupal\Core\Extension\ModuleHandlerInterface');

    $manager = new FlysystemPluginManager($namespaces, $cache_backend, $module_handle);
    $this->assertSame('missing', $manager->getFallbackPluginId('beep'));
    $this->assertInternalType('array', $manager->getDefinitions());

    // Test alterDefinitions().
    $method = new \ReflectionMethod($manager, 'alterDefinitions');
    $method->setAccessible(TRUE);

    $definitions = [
      'test1' => ['extensions' => []],
      'test2' => ['extensions' => ['pdo']],
      'test3' => ['extensions' => ['missing_extension']],
    ];

    $method->invokeArgs($manager, [&$definitions]);
    $this->assertSame(2, count($definitions));
    $this->assertArrayHasKey('test1', $definitions);
    $this->assertArrayHasKey('test2', $definitions);
    $this->assertArrayNotHasKey('test3', $definitions);
  }

}
