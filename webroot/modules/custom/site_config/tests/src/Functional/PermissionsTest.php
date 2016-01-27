<?php namespace Drupal\Tests\site_config\Functional;

use Drupal\touchcast\ConfigTouchcastBrowserTestBase;

/**
 * Class PermissionsTest
 * @group site_config
 */
class PermissionsTest extends ConfigTouchcastBrowserTestBase
{

    /**
     * Tests basic permissions for each user role.
     */
    public function testPermissions()
    {
        $this->checkAdministratorRoleIsSetUpCorrectly();
        $this->checkEditorRoleIsSetUpCorrectly();
    }

    private function checkAdministratorRoleIsSetUpCorrectly()
    {
        $this->loginAsAdministrator();

        $this->assertCanAccessContentOverviewPage();

        $homepageContentType = 'homepage';
        $this->assertCanAccessNodeCreatePage($homepageContentType);
        $this->assertCanAccessNodeEditPage($homepageContentType);

        $this->drupalLogout();
    }


    private function checkEditorRoleIsSetUpCorrectly()
    {
        $this->loginAsEditor();

        $this->assertCanAccessContentOverviewPage();

        $homepageContentType = 'homepage';
        $this->assertCanAccessNodeCreatePage($homepageContentType);
        $this->assertCanAccessNodeEditPage($homepageContentType);

        $this->drupalLogout();
    }

}
