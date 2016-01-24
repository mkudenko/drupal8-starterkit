<?php

namespace Drupal\tc_api\Helpers;

use Drupal\tc_api\Exceptions\TcPathParserException;

/**
 * Parses the internal URL to determine the entity type and the entity id.
 */
class TcPathParser
{

    /**
     * @var array
     */
    private $pathArray;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->pathArray = explode('/', $path);
    }

    /**
     * Determines entity type by the internal URL.
     *
     * @return string
     *
     * @throws TcPathParserException
     */
    public function getEntityType()
    {
        foreach ($this->pathArray as $arg) {
            if (!$arg) {
                continue;
            }
            if ($arg == 'node') {
                return 'node';
            }
        }

        throw new TcPathParserException('Entity type could not be determined.');
    }

    /**
     * Determines entity ID by the internal URL.
     *
     * @return int
     *
     * @throws TcPathParserException
     */
    public function getEntityId()
    {
        foreach ($this->pathArray as $arg) {
            if (is_numeric($arg)) {
                return $arg;
            }
        }

        throw new TcPathParserException('Entity ID was not found in path.');
    }

}
