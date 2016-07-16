<?php
namespace Zodream\Domain\Response;
/**
 * 导出类
 *
 * @author Jason
 */
use Zodream\Infrastructure\DomainObject\ExpertObject;
use Zodream\Infrastructure\FileSystem;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

class ExportResponse extends BaseResponse {
	
	protected $type;
	
	protected $content;
	
	protected $fileName;
	
	public function __construct($content, $fileName) {
		$this->content = $content;
		$this->fileName = $fileName;
		$this->type = FileSystem::getExtension($fileName);
	}

	public function sendHeaders() {
		ResponseResult::sendContentType($this->type);
		ResponseResult::sendContentDisposition($this->fileName);
		ResponseResult::sendCacheControl('must-revalidate,post-check=0,pre-check=0');
		ResponseResult::sendExpires(0);
		ResponseResult::sendPragma('public');
	}

	public function sendContent() {
		if ($this->content instanceof ExpertObject) {
			echo $this->content->send();
			return;
		}
		echo StringExpand::value($this->content);
	}

}