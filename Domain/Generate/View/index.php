<?php
defined('APP_DIR') or exit();
/** @var $this \Zodream\Domain\View\View */
?>
<html>
<head>
    <title>生成器</title>
</head>
<body>
<div>
    <form method="post">
        表选择：<div>
            <?php foreach ($this->gain('table', []) as $item):?>
            <div style="width: 30%; display: inline-block"><input type="checkbox" name="table[]" value="<?=$item?>"> <?=$item?></div>
            <?php endforeach;?>
        </div>
        <hr>
        生成模式：<div>
            <input type="checkbox" name="controller" value="1"> 控制器
            <input type="checkbox" name="model" value="2"> 模型
            <input type="checkbox" name="view" value="3"> 视图
        </div>
        <hr>
        <input type="checkbox" name="replace" value="11">使用强制模式 <br>
        <hr>
        <button type="submit">确定</button>
    </form>
</div>
</body>
</html>
