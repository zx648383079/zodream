<?php
defined('APP_DIR') or exit();
?>
    /**
     * Generated from @assert <?=$annotation?>.
     *
     * @covers <?=$className?>::<?=$origMethodName?>
     */
    public function test<?=$methodName?>() {
        $this->assert<?=$assertion?>(
            <?=$expected?>,
            <?=$className?>::<?=$origMethodName?>(<?=$arguments?>)
        );
    }
