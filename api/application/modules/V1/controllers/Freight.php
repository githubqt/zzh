<?php
use Custom\YDLib;
use Core\Express;
use Order\OrderChildModel;
use User\UserAddressModel;
use Freight\FreightSetModel;

/**
 * 快递管理
 * 
 * @version v0.01
 * @author lqt
 *         @time 2018-05-14
 */
class FreightController extends BaseController {
	/**
	 * 获取运费接口
	 *
	 * <pre>
	 * POST参数
	 * address_id : 收货地址ID [必填参数]
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Freight/charge
	 * 测试： http://testapi.qudiandang.com/v1/Freight/charge
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         "address_id": 1,
	 *         "charge": 10
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function chargeAction() {
		$data = [ ];
		$data ['address_id'] = $this->_request->getPost ( 'address_id' );
		
		if (! isset ( $data ['address_id'] ) || ! is_numeric ( $data ['address_id'] ) || $data ['address_id'] <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40098 );
		}
		
		$addressInfo = UserAddressModel::getInfoByID ( $data ['address_id'] );
		if (! $addressInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60507 );
		}
		
		$data ['charge'] = FreightSetModel::getFreightBYProvinceID ( $addressInfo ['province'] );
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data, FALSE );
	}
	
	/**
	 * 查询快递信息
	 *
	 * <pre>
	 * POST参数
	 * id : 子订单ID [必填参数]
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Freight/express
	 * 测试： http://testapi.qudiandang.com/v1/Freight/express
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *        
	 *         签收状态
	 *         指运单号当前的签收状态，通过state签收标记，详见下表：
	 *         状态值 名称 含义
	 *         0 在途 快件处于运输过程中
	 *         1 揽件 快件已由快递公司揽收
	 *         2 疑难 快递100无法解析的状态，或者是需要人工介入的状态，例如收件人电话错误。
	 *         3 签收 正常签收
	 *         4 退签 货物退回发货人并签收
	 *         5 派件 货物正在进行派件
	 *         6 退回 货物正处于返回发货人的途中
	 *        
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         "message": "ok",//---------------------快递状态信息
	 *         "nu": "482160608180",
	 *         "ischeck": "1",
	 *         "condition": "F00",
	 *         "com": "zhongtong",
	 *         "status": "200",
	 *         "state": "3",//---------------------签收状态
	 *         "data": [
	 *         {
	 *         "time": "2018-03-12 17:08:36",
	 *         "ftime": "2018-03-12 17:08:36",
	 *         "context": "【北京市】 快件已在 【北京通州潞城】 签收,签收人: 本人, 感谢使用中通快递,期待再次为您服务!"
	 *         },
	 *         {
	 *         "time": "2018-03-12 08:12:23",
	 *         "ftime": "2018-03-12 08:12:23",
	 *         "context": "【北京市】 快件已到达 【北京通州潞城】（15313966697、15313966698）,业务员 吴高强13311279969（13311279969） 正在第1次派件, 请保持电话畅通,并耐心等待"
	 *         }
	 *         ],
	 *         "express_id": "5",//-----------------------------------------------------------------------------快递公司ID
	 *         "express_name": "中通",//------------------------------------------------------------------------快递公司名称
	 *         "express_pinyin": "zhongtong",//-----------------------------------------------------------------快递公司拼音
	 *         "express_no": "482160608180"//-------------------------------------------------------------------快递单号
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function expressAction() {
		$id = $this->_request->getPost ( 'id' );
		
		if (! isset ( $id ) || ! is_numeric ( $id ) || $id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40058 );
		}
		
		$orderInfo = OrderChildModel::getInfoByID ( $id );
		if (! $orderInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60503 );
		}
		
		if (empty ( $orderInfo ['express_pinyin'] ) || empty ( $orderInfo ['express_no'] )) {
			YDLib::output ( ErrnoStatus::STATUS_60509 );
		}
		
		$express = new Express ();
		// $data = $express->searchExpress('shentong','3354502265341');
		$data = $express->searchExpress ( $orderInfo ['express_pinyin'], $orderInfo ['express_no'] );
		$data ['express_id'] = $orderInfo ['express_id'];
		$data ['express_name'] = $orderInfo ['express_name'];
		$data ['express_pinyin'] = $orderInfo ['express_pinyin'];
		$data ['express_no'] = $orderInfo ['express_no'];
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
}
