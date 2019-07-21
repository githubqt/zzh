<?php
use Machine\MachineModel;
use Custom\YDLib;
use Core\Express;
use Order\OrderChildModel;
use User\UserAddressModel;
use Freight\FreightSetModel;

/**
 * 设备管理
 * 
 * @version v0.01
 * @author lqt
 * @time 2018-05-14
 */
class MachineController extends BaseController {

	public function logAction() {
		$self_code = $this->_request->getPost ( 'self_code' );
		if (empty($self_code)) {
            $self_code = '10001482000001';
        }
        $machine = \Machine\MachineModel::findOneWhere(['self_code'=>$self_code]);
		$logInfo = \Machine\MachineservicelogModel::findAllWhere(['machine_id'=>$machine['id'],'is_del'=>BaseModel::DELETE_SUCCESS]);
		$data ['machine_id'] = $machine ['id'];
		$data ['machine_name'] = $machine ['name'];
		$data ['machine_self_code'] = $machine ['self_code'];
		$data ['machine_custom_code'] = $machine ['custom_code'];
		$data ['machine_note'] = $machine ['note'];
		$data ['log'] = $logInfo;
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}

    public function logviewAction() {
        $self_code = $this->_request->get ( 'self_code' );
        if (empty($self_code)) {
            $self_code = '10001446900001';
        }
        $machine = \Machine\MachineModel::findOneWhere(['self_code'=>$self_code]);
        $logInfo = \Machine\MachineservicelogModel::findAllWhere(['machine_id'=>$machine['id'],'is_del'=>BaseModel::DELETE_SUCCESS]);
        $data ['machine_id'] = $machine ['id'];
        $data ['machine_name'] = $machine ['name'];
        $data ['machine_self_code'] = $machine ['self_code'];
        $data ['machine_custom_code'] = $machine ['custom_code'];
        $data ['machine_note'] = $machine ['note'];
        $data ['log'] = $logInfo;
        Yaf_Dispatcher::getInstance ()->enableView ();
        $this->getView ()->assign ( "data", $data );
    }

    public function machineAction() {
        $self_code = $this->_request->get ( 'self_code' );
        if (empty($self_code)) {
            $self_code = '10001446900001';
        }
        if ($self_code == 0) {
            $machine = MachineModel::findOneWhere(['id'=>1]);
        } else {
            $machine = MachineModel::findOneWhere(['self_code'=>$self_code]);
        }
        $data ['machine_id'] = $machine ['id'];
        $data ['machine_name'] = $machine ['name'];
        $data ['machine_self_code'] = $machine ['self_code'];
        $data ['machine_custom_code'] = $machine ['custom_code'];
        $data ['machine_note'] = $machine ['note'];
        $data ['parent_id'] = $machine ['parent_id'];
        $data ['scheme'] = '/v1/Machine/machine?self_code='.$machine['self_code'];
        $data ['log_scheme'] = '/v1/Machine/logview?self_code='.$machine['self_code'];
        //父级
        if ($machine ['parent_id'] != 0) {
            $parent_info = MachineModel::findOneWhere(['id'=>$machine ['parent_id']]);
            if (!empty($parent_info)) {
                $parent = array(
                    'machine_id' => $parent_info['id'],
                    'machine_name' => $parent_info ['name'],
                    'machine_self_code' => $parent_info ['self_code'],
                    'machine_custom_code' => $parent_info ['custom_code'],
                    'machine_note' => $parent_info ['note'],
                    'parent_id' => $parent_info['parent_id'],
                    'scheme' => '/v1/Machine/machine?self_code='.$parent_info['self_code'],
                    'log_scheme' => '/v1/Machine/logview?self_code='.$parent_info['self_code'],
                );
                $data['parent'] = $parent;
            }
        }
        //查询子集
        $data['child_html'] = self::getChildHtml($machine['supplier_id'], $machine['id']);
        Yaf_Dispatcher::getInstance ()->enableView ();
        $this->getView ()->assign ( "data", $data );
    }

    //循环获取子集
    public static function getChild($supplier_id, $id) {
        $child = MachineModel::findMoreWhere(array('supplier_id' => $supplier_id, 'parent_id' => $id, 'is_del' => 2));
        if (!empty($child)) {
            $childer = [];
            foreach ($child as $key=>$value) {
                $childer_item = array(
                    'machine_id' => $value['id'],
                    'machine_name' => $value ['name'],
                    'machine_self_code' => $value ['self_code'],
                    'machine_custom_code' => $value ['custom_code'],
                    'machine_note' => $value ['note'],
                    'parent_id' => $value['parent_id'],
                    'scheme' => '/v1/Machine/machine?self_code='.$value['self_code'],
                    'log_scheme' => '/v1/Machine/logview?self_code='.$value['self_code'],
                );
                $childer_s = self::getChild($supplier_id, $value['id']);
                if (!empty($childer_s)) {
                    $childer_item['child'] = $childer_s;
                }
                $childer[] = $childer_item;
            }
            return $childer;
        }
        return [];
    }

    //循环获取子集html
    public static function getChildHtml($supplier_id, $id) {
        $child = MachineModel::findMoreWhere(array('supplier_id' => $supplier_id, 'parent_id' => $id, 'is_del' => 2));
        if (!empty($child)) {
            $childer = '';
            foreach ($child as $key=>$value) {
                $childer .= '
                   <div data-role="collapsible">
                      <a href="/v1/Machine/machine?self_code='.$value['self_code'].'">设备详情</a>
                      <h1>'.$value['name'].'</h1>';
                $childer_s = self::getChildHtml($supplier_id, $value['id']);
                if (!empty($childer_s)) {
                    $childer .= $childer_s;
                }
                $childer .= '</div>';
            }
            return $childer;
        }
        return '';
    }
}
