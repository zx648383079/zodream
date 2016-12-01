<?php
use Zodream\Infrastructure\Html;
/** @var $this \Zodream\Domain\View\View */
/** @var $page \Zodream\Domain\Html\Page */

$this->extend('layout/head');
?>
<div class="row">
	<div class="col-md-3 col-md-offset-2">
		<?=Html::a('新增', '{name}/add', ['class' => 'btn btn-primary'])?>
	</div>
</div>

<table>
	<thead>
		<tr>
{column}
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($page->getPage() as $item) :?>
			<tr>
{data}
				<td>
					<?=Html::a('查看', ['{name}/view', 'id' => $item['id']])?>
					<?=Html::a('编辑', ['{name}/edit', 'id' => $item['id']])?>
					<?=Html::a('删除', ['{name}/delete', 'id' => $item['id']])?>
				</td>
			</tr>
		<?php endforeach;?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="5">
				<?php $page->pageLink();?>
			</th>
		</tr>
	</tfoot>
</table>


<?php $this->extend('layout/foot');?>