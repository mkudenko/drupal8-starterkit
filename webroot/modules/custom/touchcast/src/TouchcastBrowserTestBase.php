<?php namespace Drupal\touchcast;

use Drupal\Core\Language\LanguageInterface;
use Drupal\node\Entity\Node;
use Drupal\simpletest\BrowserTestBase;
use Drupal\user\Entity\Role;

abstract class TouchcastBrowserTestBase extends BrowserTestBase
{

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    public function setUp()
    {
        $this->faker = \Faker\Factory::create();

        parent::setUp();
    }

    public function assertResponseOk($message = '')
    {
        $this->assertResponseStatus(200, $message);
    }

    public function assertResponseStatus($expectedCode, $message = '')
    {
        $actualCode = $this->getSession()->getStatusCode();

        $this->assertEquals($expectedCode, $actualCode, $message);
    }

    public function assertCanAccessContentOverviewPage()
    {
        $this->drupalGet('/admin/content');

        $this->assertResponseOk('Cannot access content overview page.');
    }

    public function assertCanAccessNodeCreatePage($type)
    {
        $this->drupalGet('/node/add/' . $type);

        $this->assertResponseOk('Cannot create "' . $type . '"" content.');
    }

    public function assertCanAccessNodeEditPage($type)
    {
        $node = $this->createNode($type);

        $this->drupalGet('node/' . $node->id() . '/edit');

        $this->assertResponseOk('Cannot edit "' . $type . '"" content.');
    }

    /**
     * Creates a node with a specified url alias.
     *
     * @param string $type
     * @param string $urlAlias
     *
     * @return Node
     */
    protected function createNode($type, $title = '', $urlAlias = '')
    {
        $title = ($title) ?: $this->faker->sentence(3);

        $node = Node::create(array(
            'type' => $type,
            'title' => $title,
            'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
            'uid' => '1',
            'status' => 1,
            'field_fields' => array(),
        ));

        $node->save();

        if ($urlAlias) {
            $aliasStorage = $this->container->get('path.alias_storage');
            $aliasStorage->save('/node/' . $node->id(), $urlAlias, LanguageInterface::LANGCODE_NOT_SPECIFIED, 0);
        }

        return $node;
    }

    protected function loginAsAdministrator()
    {
        $this->loginWithRole('administrator');
    }

    protected function loginAsEditor()
    {
        $this->loginWithRole('editor');
    }

    protected function loginWithRole($role)
    {
        $permissions = $this->getRolePermissions($role);

        $account = $this->drupalCreateUser($permissions);

        $this->drupalLogin($account);
    }

    /**
     * Returns an array of role permissions.
     *
     * @param string $rid
     *   Role ID.
     *
     * @return array
     */
    protected function getRolePermissions($rid)
    {
        $role = Role::load($rid);

        return $role->getPermissions();
    }

}
