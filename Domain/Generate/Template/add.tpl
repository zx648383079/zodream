<?php
defined('APP_DIR') or exit();
use Zodream\Domain\Html\Bootstrap\FormWidget;
/** @var $this \Zodream\Domain\View\View */
$this->extend('layout/head');
?>


<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">增加</h3>
	</div>
	<div class="panel-body">
		<?=FormWidget::begin($data)
		->hidden('id')
{data}
		->button()
		->end();
		?>
		<p><?=$error?></p>
	</div>
</div>


<?php $this->extend('layout/foot');?>