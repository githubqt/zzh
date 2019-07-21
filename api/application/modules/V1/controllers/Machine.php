<?php
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
            $machine = \Machine\MachineModel::findOneWhere(['id'=>1]);
        } else {
            $machine = \Machine\MachineModel::findOneWhere(['self_code'=>$self_code]);
        }
        $data ['machine_id'] = $machine ['id'];
        $data ['machine_name'] = $machine ['name'];
        $data ['machine_self_code'] = $machine ['self_code'];
        $data ['machine_custom_code'] = $machine ['custom_code'];
        $data ['machine_note'] = $machine ['note'];
        $data ['parent_id'] = $machine ['parent_id'];
        //父级
        $parent = array(
            'id' => 0,
            'parent_id' => 0,
            'name' => '顶级设备',
            'scheme' => '/v1/Machine/machine?self_code=0',
        );
        if ($machine ['parent_id'] != 0) {
            $parent_info = \Machine\MachineModel::findOneWhere(['id'=>$machine ['parent_id']]);
            if (!empty($parent_info)) {
                $parent = array(
                    'id' => $parent_info['id'],
                    'parent_id' => $parent_info['parent_id'],
                    'name' => $parent_info['name'],
                    'scheme' => '/v1/Machine/machine?self_code='.$parent_info['self_code'],
                );
            }
        }
        $data['parent'] = $parent;
        //查询子集
        $child_info = \Machine\MachineModel::findOneWhere(['parent_id'=>$machine ['id'],'is_del'=>2]);
        if (!empty($child_info)) {

        }
        Yaf_Dispatcher::getInstance ()->enableView ();
        $this->getView ()->assign ( "data", $data );
    }


}
