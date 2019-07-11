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
        Yaf_Dispatcher::getInstance ()->enableView ();
        $this->getView ()->assign ( "data", $data );
    }
}
