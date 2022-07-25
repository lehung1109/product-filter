<?php namespace ProductFilter\Rest;

use Premmerce\SDK\V2\FileManager\FileManager;

/**
 * Class Frontend
 *
 * @package ProductFilter\Frontend
 */
class Rest {


    /**
     * @var FileManager
     */
    private $fileManager;

    public function __construct( FileManager $fileManager ) {
        $this->fileManager = $fileManager;
    }

}