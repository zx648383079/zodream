<?php
defined('APP_DIR') or exit();
use Zodream\Domain\Html\Bootstrap\DetailWidget;
/** @var $this \Zodream\Domain\View\View */
$this->extend(array(
	'layout' => array(
		'head'
	))
);
$data = $this->get('data');
?>


<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><?=$data['id']?></h3>
	</div>
	<div class="panel-body">
		<?=DetailWidget::show([
                'data' => $data,
				'items' => [
					'id' => 'ID',
{data}
					'update_at'	=> '更新时间:datetime',
					'create_at' => '创建时间:datetime'
				]
		])?>
	</div>
</div>


<?php
$this->extend(array(
	'layout' => array(
		'foot'
	))
);
?>