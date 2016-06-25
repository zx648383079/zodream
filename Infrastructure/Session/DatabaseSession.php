<?php
namespace Zodream\Infrastructure\Session;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/6
 * Time: 9:56
 */
use Zodream\Infrastructure\Error;
class DatabaseCache extends Session {

    /**
     * @var \Zodream\Domain\Model
     */
    private $_model;

    public function useCustomStorage() {
        return true;
    }

    public function regenerateID($deleteOldSession = false) {
        $oldID = session_id();

        // if no session is started, there is nothing to regenerate
        if (empty($oldID)) {
            return;
        }
        $newID = session_id();
        $row = $this->_model->findById($oldID);
        if (!empty($row)) {
            if ($deleteOldSession) {
                $this->_model->updateById($oldID, array(
                    'id' => $newID
                ));
            } else {
                $row['id'] = $newID;
                $this->_model->add($row);
            }
        } else {
            $this->_model->add(array(
                'id' => $newID
            ));
        }
    }

    public function readSession($id) {
        $data = $this->_model->findOne(array(
            'expire > '.time(),
            'id = '.$id
        ), 'data');
        return empty($data) ? '' : $data['data'];
    }

    public function writeSession($id, $data) {
        try {
            $exists = $this->_model->findById($id);
            if (empty($exists)) {
                $this->_model->add(array(
                    'id' => $id,
                    'data' => $data
                ));
            } else {
                unset($exists['id']);
                $exists['data'] = $data;
                $this->_model->updateById($id, $exists);
            }
        } catch (\Exception $e) {
            Error::out('WRITESESSION FAIL', __FILE__, __LINE__);
        }
        return true;
    }

    public function destroySession($id) {
        $this->_model->deleteById($id);

        return true;
    }

    public function gcSession($maxLifetime) {
        $this->_model->delete(array(
            'expire < '.time()
        ));
        return true;
    }
}