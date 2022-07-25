<?php namespace ProductFilter\Ajax;

use Premmerce\SDK\V2\FileManager\FileManager;

/**
 * Class Frontend
 *
 * @package ProductFilter\Frontend
 */
class Ajax {


    /**
     * @var FileManager
     */
    private $fileManager;

    public function __construct( FileManager $fileManager ) {
        $this->fileManager = $fileManager;
    }

}