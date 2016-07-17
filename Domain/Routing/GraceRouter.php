<?php
namespace Zodream\Domain\Routing;
/**
 * 优雅链接
 */
use Zodream\Infrastructure\DomainObject\RouterObject;

class GraceRouter extends BaseRouter implements RouterObject {
	protected function getRouteByUrl() {
		$urlParams = explode('.php', Url::getUriWithoutParam());
		return end($urlParams);
	}
	
	public function to($file) {
		return rtrim(APP_URL, '/').'/'.ltrim($file, '/');
	}
	
	public function run() {
		return $this->runByUri($this->getRouteByUrl());
	}
}