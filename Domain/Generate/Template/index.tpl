<?php
use Zodream\Infrastructure\Html;
/** @var $this \Zodream\Domain\View\Engine\DreamEngine */
/** @var $page \Zodream\Domain\Html\Page */
$this->extend(array(
	'layout' => array(
		'head'
	)
));
$page = $this->get('page');
?>
<div class="row">
	<div class="col-md-3 col-md-offset-2">
		<a href="<?php $this->url('{name}/add');?>" class="btn btn-primary">新增</a>
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
					<a href="<?php $this->url('{name}/view/id/'.$value['id']);?>">view</a>
					<a href="<?php $this->url('{name}/edit/id/'.$value['id']);?>">edit</a>
					<a href="<?php $this->url('{name}/delete/id/'.$value['id']);?>">delete</a>
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


<?php 
$this->extend(array(
	'layout' => array(
		'foot'
	)
));
?>