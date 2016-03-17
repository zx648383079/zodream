<?php 
$this->extend(array(
	'layout' => array(
		'head'
	)
));
$page = $this->get('page');
?>
<div>
<form action="<?php $this->url();?>" method="get">
<input type="text" name="search">
<button type="submit">搜索</button>
</form>
</div>
<div>
<a href="<?php $this->url('{name}/add');?>">新增</a>
</div>
<div>
<table>
<thead>
<tr>
{column}
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach ($page->getPage() as $value) {?>
	<tr>
		{data}
		<td>
		<a href="<?php $this->url('{name}/view/id/'.$value['id']);?>">view</a>
		<a href="<?php $this->url('{name}/edit/id/'.$value['id']);?>">edit</a>
		<a href="<?php $this->url('{name}/delete/id/'.$value['id']);?>">delete</a>
		</td>
	</tr>
<?php }?>
</tbody>
<tfoot>
<tr>
<th colspan="3">
<?php $page->pageLink();?>
</th>
</tr>
</tfoot>
</table>
</div>


<?php 
$this->extend(array(
	'layout' => array(
		'foot'
	)
));
?>