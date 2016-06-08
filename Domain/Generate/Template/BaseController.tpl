<?php
namespace Service\{module};

use Zodream\Domain\Routing\Controller as BaseController;
abstract class Controller extends BaseController {

    /**
	 * @param string|Model $table
	 * @param string|int $id
	 */
	protected function delete($table, $id) {
		if (is_string($table)) {
			$table = EmpireModel::query($table);
		}
		$row = $table->deleteById($id);
        if (empty($row)) {
        Log::save("未成功删除表{$table->getTable()}中的Id".$id, 'delete');
        Redirect::to(-1, 2, '删除失败！');
        }
        Log::save("成功删除表{$table->getTable()}中的Id".$id, 'delete');
        Redirect::to(-1, 2, '删除成功！');
    }
}