<?php
defined('APP_DIR') or exit();
echo '<?php';
?>
namespace Domain\Model\<?=$module?>;

use Domain\Model\Model;
/**
 * Class <?=$name.APP_MODEL?>
<?php foreach ($property as $key => $item):?>
 * @property <?=$item?> $<?=$key?>
<?php endforeach;?>
 */
class <?=$name.APP_MODEL?> extends Model {
	public static function tableName() {
        return '<?=$table?>';
    }

<?php if (isset($pk) && !empty($pk)):?>
	protected $primaryKey = [
    <?php foreach ($pk as $item):?>
        '<?=$item?>',
    <?php endforeach;?>
    ];
<?php endif;?>

	protected function rules() {
		return [
    <?php foreach ($rules as $key => $item):?>
            '<?=$key?>' => '<?=$item?>',
    <?php endforeach;?>
        ];
	}

	protected function labels() {
		return [
        <?php foreach ($labels as $key => $item):?>
            '<?=$key?>' => '<?=$item?>',
        <?php endforeach;?>
        ];
	}
}