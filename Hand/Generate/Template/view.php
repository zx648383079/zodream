<?php 
$this->extend(array(
		'layout' => array(
				'head'
		)
));
?>
<div>
<a href="<?php $this->url('{name}');?>">返回</a>
<a href="<?php $this->url('{name}/edit/'.$value['id']);?>">edit</a>
<a href="<?php $this->url('{name}/delete/'.$value['id']);?>">delete</a>
</div>
<div>
{data}
</div>


<?php 
$this->extend(array(
		'layout' => array(
				'foot'
		)
));
?>