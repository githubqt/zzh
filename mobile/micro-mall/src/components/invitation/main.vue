<template>
	<yd-layout class="invitation-main" title="我要邀请" link="/inviteList">
		<div class="mini-img-box">
			<img src="./../../../static/imgs/mini_code.jpg" alt="">
			<div class="img-box-text">
				<p>扫一扫上面的二维码图案，关注我们小程序</p>
				<p>或者</p>
			</div>
		</div>
		<div class="img-box-btn">
			<yd-button size="large" type="danger" color="#fff" @click.native="openMask">扫码注册</yd-button>
			<yd-button size="large" type="danger" color="#fff" @click.native="helpRegister">帮TA注册</yd-button>
		</div>
		<dialog-bar v-model="sendVal" type="danger" title="" content='
			    	        <div class="invitation-qrcode-item" > 
			    		        <div id="invitation-qrcode" ></div>
			    		        <h3><b>打开微信客户端扫我注册</b></h3>
			    	        </div>
			    	        '>
		</dialog-bar>
	</yd-layout>
</template>

<script>
	import QRCode from "qrcodejs2";
	import dialogBar from "./dialog.vue";
	import Qs from "qs";
	import {
		login
	} from "../../../tool/login";
	export default {
		name: "Codelogin",
		components: {
			"dialog-bar": dialogBar
		},
		data() {
			return {
				sendVal: false,
				invitationCode: ""
			};
		},
		created() {
			let _cookie_user_id = this.$route.query.user_id;
			let _cookie_token = this.$route.query.token;
			if (_cookie_user_id) {
				login({
					user_id: _cookie_user_id,
					token: _cookie_token
				});
			}
		},
		mounted() {
			let _this = this,
				ua = window.navigator.userAgent.toLowerCase();
			let user_id = localStorage.getItem("userId");
			if (window.__wxjs_environment == "miniprogram") {} else if (ua.match(/MicroMessenger/i) == "micromessenger" && !user_id) {
				_this.wechatLogin();
			}
			_this.getcode();
		},
		methods: {
			helpRegister() {
				let _this = this;
				_this.$router.push("/helpRegister");
			},
			wechatLogin() {
				let _this = this;
				window.location.href =
					_this.$API +
					"/v1/Weixin/wechatlogin/?identif=" +
					this.DOMAIN +
					"&redirect_url=" +
					encodeURIComponent(
						window.location.protocol + "//" + window.location.host + "/invitation"
					);
			},
			getcode() {
				let _this = this;
				//传到后台进行编码
				_this.$http
					.post("/api/v1/common/xencode")
					.then(function(response) {
						if (parseInt(response.data.errno) === 0) {
							_this.invitationCode = response.data.result.code;
						} else {
							console.log('TCL: getcode -> response', response);
						}
					})
					.catch(function(error) {
						console.log('TCL: getcode -> error', error);
					});
			},
			qrcode() {
				let _this = this;
				let qrcode = new QRCode("invitation-qrcode", {
					width: 250,
					height: 250, // 高度
					text: `https://${_this.DOMAIN}.m.zhahehe.com/mobile/login?invitation_id=${_this.invitationCode}`
					// 二维码内容
					// render: 'canvas' // 设置渲染方式（有两种方式 table和canvas，默认是canvas）
					// background: '#f0f'
					// foreground: '#ff0'
				});
			},
			openMask(index) {
				this.sendVal = true;
				this.qrcode();
			}
		}
	};
</script>

<style>

</style>
