<template>
	<section class="info-contaienr">
		<!-- header -->
		<yd-navbar class="fixed-header" height=".88rem" fontsize=".34rem" title="个人资料">
			<router-link to="/user" slot="left">
				<yd-navbar-back-icon></yd-navbar-back-icon>
			</router-link>
		</yd-navbar>
		<!-- content -->
		<yd-cell-group class="info-content-box" style="padding-top: .77rem;">
			<yd-cell-item arrow>
				<span slot="left" class="letter-spacing">头像</span>
				<input id="change"  type="file"  accept="image" v-on:change="change" class="upload-spacing" slot="right">
				<img  :src="userImg" alt="" :onerror="errorImg" class="info-head-portrait" slot="right">
			</yd-cell-item>
			<yd-cell-item>
				<span slot="left" class="letter-spacing">姓名</span>
				<yd-input v-model="userName" class="info-content-input" slot="right"></yd-input>
			</yd-cell-item>
			<yd-cell-item>
				<span slot="left" class="letter-spacing">性别</span>
				<yd-input readonly :show-clear-icon="false" value="保密" class="info-content-input" @click.native="sexShow=!sexShow" v-if="sex==='0'" slot="right"/>
				<yd-input readonly :show-clear-icon="false" value="男" class="info-content-input" @click.native="sexShow=!sexShow" v-else-if="sex==='1'" slot="right"/>
				<yd-input readonly :show-clear-icon="false" value="女" class="info-content-input" @click.native="sexShow=!sexShow" v-else-if="sex==='2'" slot="right"/>
			</yd-cell-item>
			<yd-cell-item>
				<span slot="left" class="letter-spacing">手机</span>
				<yd-input readonly :show-clear-icon="false" v-model="mobile" class="info-content-input" slot="right"></yd-input>
			</yd-cell-item>
			<yd-cell-item>
				<span slot="left" class="letter-spacing">生日</span>
				<yd-datetime type="date" start-date="1900-01-01" v-model="birthday" class="info-content-input" slot="right" />
			</yd-cell-item>
			<yd-cell-item>
				<span slot="left" class="letter-spacing">QQ</span>
				<yd-input v-model="oicq" class="info-content-input" slot="right" ></yd-input>
			</yd-cell-item>
			<yd-cell-item>
				<span slot="left" class="letter-spacing">微信</span>
				<yd-input v-model="wchat" class="info-content-input" slot="right"></yd-input>
			</yd-cell-item>
			<yd-cell-item>
				<span slot="left" class="letter-spacing">地址</span>
				<sty-area v-bind:cityNames="cityNames" v-on:cityIds="cityIds" slot="right"></sty-area>
			</yd-cell-item>
			<yd-cell-item>
				<span slot="left" class="">详细地址</span>
				<yd-input v-model="address" class="info-content-input" slot="right"></yd-input>
			</yd-cell-item>
		</yd-cell-group>
		<div class="save-bnt-box">
			<yd-button size="large" type="danger" color="#fff" @click.native="userInfoSave">保存</yd-button>
		</div>

		 <div id="demo">
		    <!-- 遮罩层 -->
		    <div class="container" v-show="panel">
		        <div>
		            <img id="image" :src="url" alt="Picture">
		        </div>
		        <button type="button" id="button" @click="commit">确定</button>
		        <button type="button" id="cancel" @click="cancel">取消</button>
		    </div>
		</div>
		<!-- 性别菜单 -->
		<yd-actionsheet :items="sexItems" v-model="sexShow" cancel="取消"></yd-actionsheet>
	</section>
</template>
<script>
import Qs from 'qs'
import Area from '@/common/area'
import Cropper from "cropperjs";
export default {
	name: 'UserInfo',
	components: { 'sty-area': Area },
	props: {
      imgType: {
        type: String
      },
      proportionX: {
        type: Number
      },
      proportionY: {
        type: Number
      }
    },
	data() {
		return {
			sexShow: false,
			sexItems: [
				{ label: '男', callback: () => { this.sex = '1' } },
				{ label: '女', callback: () => { this.sex = '2' } },
				{ label: '保密', callback: () => { this.sex = '0' } },
			],
			userImg: require('../../assets/img/headerr.jpg'),
			imgData: '',
			userName: '',
			sex: '',
			mobile: '',
			birthday: '',
			oicq: '',
			wchat: '',
			cityNames: '',
			province_id: '',
			city_id: '',
			area_id: '',
			street_id: '',
			address: '',
			errorImg: 'this.src="' + require('../../assets/img/headerr.jpg') + '"',
	        picValue: "",
	        cropper: "",
	        croppable: false,
	        panel: false,
	        url: "",
	        imgCropperData: {
	          accept: "image/gif, image/jpeg, image/png, image/jpg"
	        }
		}
	},
	mounted() {
	    //初始化这个裁剪框
	    var self = this;
	    var image = document.getElementById("image");
	    this.cropper = new Cropper(image, {
	      aspectRatio: 1,
	      viewMode: 1,
	      background: false,
	      zoomable: false,
	      ready: function() {
	        self.croppable = true;
	      }
	    });
	},
	created() { this.userInfoGet() },
	methods: {
		userInfoGet() {
			let _this = this,
				_data = Qs.stringify({ user_id: localStorage.getItem('userId') });

			_this.$http.post('/api/v1/User/userInfo', _data).then(function(response) {
				if (response.data.errno === '0') {
					let site = response.data.result.province_name + response.data.result.city_name + response.data.result.area_name;
					_this.userImg = response.data.result.user_img;
					_this.userName = response.data.result.name;
					_this.sex = response.data.result.sex;
					_this.mobile = response.data.result.mobile;
					if (response.data.result.birthday !== null) {
						_this.birthday = response.data.result.birthday;
					}
					_this.oicq = response.data.result.qq;
					_this.wchat = response.data.result.wchat;
					_this.cityNames = site.toString();
					_this.province_id = response.data.result.province_id;
					_this.city_id = response.data.result.city_id;
					_this.area_id = response.data.result.area_id;
					_this.address = response.data.result.address;
				} else {
					_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
				}
			}).catch(function(error) {
				_this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
			});
		},
		//取消上传
	    cancel() {
	        this.panel = false;
            //var obj = document.getElementById('change') ;
            //obj.outerHTML=obj.outerHTML;
	    },
	    //创建url路径
	    getObjectURL(file) {
	      var url = null;
	      if (window.createObjectURL != undefined) {
	        // basic
	        url = window.createObjectURL(file);
	      } else if (window.URL != undefined) {
	        // mozilla(firefox)
	        url = window.URL.createObjectURL(file);
	      } else if (window.webkitURL != undefined) {
	        // webkit or chrome
	        url = window.webkitURL.createObjectURL(file);
	      }
	      return url;
	    },
	    //input框change事件，获取到上传的文件
	    change(e) {
	      let files = e.target.files || e.dataTransfer.files;
	      if (!files.length) return;
	      let type = files[0].type; //文件的类型，判断是否是图片
	      let size = files[0].size; //文件的大小，判断图片的大小
	      if (this.imgCropperData.accept.indexOf(type) == -1) {

	        _this.$dialog.toast({ mes: "请选择我们支持的图片格式！", timeout: 1500, icon: 'error' });
	        return false;
	      }
	      if (size > 12328960) {
	        _this.$dialog.toast({ mes: "请选择10M以内的图片！", timeout: 1500, icon: 'error' });
	        return false;
	      }
	      this.picValue = files[0];
	      this.url = this.getObjectURL(this.picValue);
	      //每次替换图片要重新得到新的url
	      if (this.cropper) {
	        this.cropper.replace(this.url);
	      }
	      this.panel = true;
	    },
	    //确定提交
	    commit() {
	      this.panel = false;
	      var croppedCanvas;
	      var roundedCanvas;
	      if (!this.croppable) {
	        return;
	      }
	      // Crop
	      croppedCanvas = this.cropper.getCroppedCanvas();
	      // Round
	      roundedCanvas = this.getRoundedCanvas(croppedCanvas);
	      this.userImg = roundedCanvas.toDataURL("image/jpeg", 0.1);
	      //上传图片
	      this.postImg();
	    },
	    //canvas画图
	    getRoundedCanvas(sourceCanvas) {
	      var canvas = document.createElement("canvas");
	      var context = canvas.getContext("2d");
	      var width = sourceCanvas.width;
	      var height = sourceCanvas.height;
	      canvas.width = width;
	      canvas.height = height;
	      context.imageSmoothingEnabled = true;
	      context.drawImage(sourceCanvas, 0, 0, width, height);
	      context.globalCompositeOperation = "destination-in";
	      context.beginPath();
	       /* context.arc(
	        width / 2,
	        height / 2,
	        Math.min(width, height) / 2,
	        0,
	        2 * Math.PI,
	        true
	      ); */
	      context.fill();

	      return canvas;
	    },
	    //提交上传函数
	    postImg() {

	    	let _this = this, imgToken = null, imgBase64Data = null;
	        // 判断是否选择图片
	       /* if (_this.hasSelectImg == false) {
	          _this.$dialog.toast({ mes: '请选择图片，然后进行裁剪操作！', timeout: 1500, icon: 'error' });
	          return false;
	        }*/
	        // 确认按钮不可用
	       // _this.cropperLoading = true;
	        imgBase64Data = this.userImg;

	    	// 构造上传图片的数据
			let formData = new FormData();
			// 截取字符串
			let photoType = imgBase64Data.substring(imgBase64Data.indexOf(",") + 1);
			//进制转换
			const b64toBlob = (b64Data, contentType = '', sliceSize = 512) => {
			  const byteCharacters = atob(b64Data);
			  const byteArrays = [];
			  for(let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
			    const slice = byteCharacters.slice(offset, offset + sliceSize);
			    const byteNumbers = new Array(slice.length);
			    for(let i = 0; i < slice.length; i++) {
			      byteNumbers[i] = slice.charCodeAt(i);
			    }
			    const byteArray = new Uint8Array(byteNumbers);
			    byteArrays.push(byteArray);
			  }
			  const blob = new Blob(byteArrays, {
			    type: contentType
			  });
			  return blob;
			}
			const contentType = 'image/jepg';
			const b64Data2 = photoType;
			const blob = b64toBlob(b64Data2, contentType);
			formData.append("file", blob, "client-camera-photo.png");
			formData.append("type", _this.imgType);
			formData.append("filetype", 1);

	     	 _this.$http({
                url: '/file/mobile_img.php',
                method: 'POST',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(function (response) {
                _this.$dialog.loading.close();
				if (response.data.errno == 0) {
					_this.imgData = response.data.data.url;
					_this.userImg = response.data.data.auth_url;
				} else {
					_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
				}
            }).catch(function(err) {
                that.$dialog.loading.close();
                that.$dialog.toast({ mes: err, timeout: 1500, icon: 'error' });
            })

	    },
		cityIds(data) {
			this.province_id = data.provinceId;
			this.city_id = data.cityId;
			this.area_id = data.areaId;
			this.street_id = data.streetId;
		},
		userInfoSave() {
			let _this = this,
				_data = Qs.stringify({
					user_id: localStorage.getItem('userId'),
					user_img: _this.imgData,
					name: _this.userName,
					mobile: _this.mobile,
					sex: _this.sex,
					birthday: _this.birthday,
					qq: _this.oicq,
					wchat: _this.wchat,
					province_id: _this.province_id,
					city_id: _this.city_id,
					area_id: _this.area_id,
					address: _this.address
				});

            var  isQQ   = new RegExp(/^[1-9][0-9]{4,9}$/);

            if(!isQQ.test(_this.oicq)){
                _this.$dialog.toast({ mes: '请输入正确的qq号!', timeout: 1500, icon: 'error' });
                return;
            }

            var isWchat = new RegExp(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]/g,'');
            if(isWchat.test(_this.wchat)){
                _this.$dialog.toast({ mes: '请输入正确的微信号!', timeout: 1500, icon: 'error' });
                return;
            }


			_this.$dialog.loading.open('很快加载好了');
			_this.$http.post('/api/v1/User/editUser', _data).then(function(response) {
				if (response.data.errno === '0') {
					_this.$dialog.loading.close();
					_this.$dialog.toast({ mes: '保存成功', timeout: 1500, icon: 'success' });
					_this.$router.push('/user');
				} else {
					_this.$dialog.loading.close();
					_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
				}
			}).catch(function(error) {
				_this.$dialog.loading.close();
				_this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
			});
		}
	}
}

</script>
<style>
</style>