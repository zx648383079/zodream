<?php
namespace Zodream\Infrastructure\DomainObject;

use Zodream\Domain\Routing\Route;

interface RouterObject {
	/**
	 * 生成url
	 * @param string $file
	 * @return string|array
	 */
	public function to($file);

	/**
	 * @return Route
	 */
	public function run();

	/**
	 * @return Route
	 */
	public function getRoute();
}