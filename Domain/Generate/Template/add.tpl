<?php
defined('APP_DIR') or exit();
use Zodream\Domain\Html\Bootstrap\FormWidget;
/** @var $this \Zodream\Domain\Response\View */
$this->extend(array(
'layout' => array(
		'head'
	))
);
?>


<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">增加</h3>
	</div>
	<div class="panel-body">
		<?=FormWidget::begin($this->get('data'))
		->hidden('id')
{data}
		->button()
		->end();
		?>
		<p><?php $this->ech('error');?></p>
	</div>
</div>


<?php
$this->extend(array(
	'layout' => array(
		'foot'
	))
);
?>