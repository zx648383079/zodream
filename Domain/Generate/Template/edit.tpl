<?php 
$this->extend(array(
		'layout' => array(
				'head'
		)
));
?>
<div>
<a href="<?php $this->url('{name}');?>">返回</a>
</div>
<div>
<form action="<?php $this->url();?>" method="post">
{data}
<button type="submit">保存</button>
</form>
</div>


<?php 
$this->extend(array(
		'layout' => array(
				'foot'
		)
));
?>