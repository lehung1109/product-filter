<?php namespace ProductFilter\Admin;

use Premmerce\SDK\V2\FileManager\FileManager;

/**
 * Class Admin
 *
 * @package ProductFilter\Admin
 */
class Admin {

	/**
	 * @var FileManager
	 */
	private $fileManager;

	/**
	 * Admin constructor.
	 *
	 * Register menu items and handlers
	 *
	 * @param FileManager $fileManager
	 */
	public function __construct( FileManager $fileManager ) {
		$this->fileManager = $fileManager;
	}

}