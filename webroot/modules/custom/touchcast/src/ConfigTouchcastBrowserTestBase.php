<?php namespace Drupal\touchcast;

use Drupal\Core\Database\Database;

abstract class ConfigTouchcastBrowserTestBase extends TouchcastBrowserTestBase
{

    /**
     * {@inheritdoc}
     */
    protected $profile = 'config_installer';

    /**
     * {@inheritdoc}
     */
    protected $configDirectories = array(
        'sync' => '_config',
    );

    /**
     * {@inheritdoc}
     */
    protected function installParameters()
    {
        $connection_info = Database::getConnectionInfo();
        $driver = $connection_info['default']['driver'];
        $connection_info['default']['prefix'] = $connection_info['default']['prefix']['default'];
        unset($connection_info['default']['driver']);
        unset($connection_info['default']['namespace']);
        unset($connection_info['default']['pdo']);
        unset($connection_info['default']['init_commands']);
        $parameters = array(
            'interactive' => FALSE,
            'parameters' => array(
                'profile' => $this->profile,
                'langcode' => 'en',
            ),
            'forms' => array(
                'install_settings_form' => array(
                    'driver' => $driver,
                    $driver => $connection_info['default'],
                ),
                'install_configure_form' => array(
                    'site_name' => 'Drupal',
                    'site_mail' => 'simpletest@example.com',
                    'account' => array(
                        'name' => $this->rootUser->name,
                        'mail' => $this->rootUser->getEmail(),
                        'pass' => array(
                            'pass1' => $this->rootUser->passRaw,
                            'pass2' => $this->rootUser->passRaw,
                        ),
                    ),
                    // form_type_checkboxes_value() requires NULL instead of FALSE values
                    // for programmatic form submissions to disable a checkbox.
                    'update_status_module' => array(
                        1 => NULL,
                        2 => NULL,
                    ),
                ),
                'config_installer_site_configure_form' => array(
                    'account' => array(
                        'name' => $this->rootUser->name,
                        'mail' => $this->rootUser->getEmail(),
                        'pass' => array(
                            'pass1' => $this->rootUser->passRaw,
                            'pass2' => $this->rootUser->passRaw,
                        ),
                    ),
                ),
                'config_installer_sync_configure_form' => array(
                    'sync_directory' => $this->configDirectories['sync'],
                ),
            ),
        );

        return $parameters;
    }

}
