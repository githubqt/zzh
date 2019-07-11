/*定位服务函数入口
 */

function getLocationInfo() {
    try {
        if (!navigator.geolocation) {
            throw "定位服务不可用";
        } else {
            console.log('定位');
            navigator.geolocation.getCurrentPosition(this.onPosSuccess, this.onPosError);
        }
    } catch (e) {
        let _this = this;
        console.log(e);
        setTimeout(function () {
            _this.showPosition();
        }, 1500);
    }

}

function showPosition(position) {

    console.log('pos:showPosition', position);
    if (position) {
     var   latitude = position.coords.latitude;
     var   longitude = position.coords.longitude;
    }
    alert(latitude);
    alert(longitude);
    // _this.latitude = 39.966596;
    // _this.longitude = 116.396027;
    // _this.city = position.city;

}
function onPosSuccess(pos) {
    console.log('pos:onPosSuccess', pos);
    this.showPosition(pos);
}
function onPosError(err) {
    let _this = this;
    console.log('pos:onPosError', err);
    setTimeout(function() {
        _this.showPosition();
    }, 1500);
}


module.exports = {
    locationinfo:getLocationInfo
};



