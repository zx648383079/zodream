<?php 
$this->extend(array(
		'layout' => array(
				'head'
		)
));
?>
<div>
<form action="<?php $this->url('{name}');?>" method="get">
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
<?php foreach ($this->get('data', array()) as $value) {?>
	<tr>
		{data}
		<td>
		<a href="<?php $this->url('{name}/view/'.$value['id']);?>">view</a>
		<a href="<?php $this->url('{name}/edit/'.$value['id']);?>">edit</a>
		<a href="<?php $this->url('{name}/delete/'.$value['id']);?>">delete</a>
		</td>
	</tr>
<?php }?>
</tbody>
<tfoot>
<tr>
<th>

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