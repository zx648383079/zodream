<?php
namespace Zodream\Infrastructure\DomainObject;

interface RouterObject {
	/**
	 * 生成url
	 * @param string $file
	 * @return string
	 */
	public function to($file);

	/**
	 * @return ResponseObject
	 */
	public function run();

	/**
	 * @return Route
	 */
	public function getRoute();
}