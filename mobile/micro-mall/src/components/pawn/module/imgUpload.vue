<template>
	<section class="img-upload">
		<h3 class="title">上传图片：</h3>
		<div class="pic" v-for="(item, index) in houseImg" :key="index">
			<img v-bind:src="item.url" alt="">
			<yd-icon name="error-outline" class="btn-close" v-on:click.native="delectPic(index)"></yd-icon>
		</div>
		<div class="pic-upload">
			<span>+</span>
			<input type="file" multiple accept="image/*" v-on:change="onFileFrontChange">
		</div>
	</section>
</template>

<script>
	export default {
		components: {},
		data() {
			return {
				imgNum: 0,
				houseImg: [],
				imgBase64Data: null,
				uploadSuccessNum: 0, //单次上传成功数量
				uploadFailedNum: 0, //单次上传失败数量
				selectNum: 0, //单次选择文件数
			};
		},
		props: ["housingImg", "fileType"],
		methods: {
			onFileFrontChange: function(e) {
				let _this = this;
				let files = e.target.files || e.dataTransfer.files;
				let len = files.length;
				//已选择图片数
				_this.selectNum = len;
				if (this.imgNum + len > 5) {
					this.$dialog.toast({
						mes: `最多上传5张，还可上传 ${5-this.imgNum} 张`,
						timeout: 1500,
						icon: "error",
					});
					return;
				}
				this.$dialog.loading.open("正在上传...");
				for (let i = 0; i < len; i++) {
					console.log("​files", len)
					let img = files[i];
					var reader = new FileReader();
					reader.readAsDataURL(img);
					reader.onload = function(e) {
						var img = new Image();
						img.src = this.result;
						img.onload = function() {
							let myCanvas = document.createElement("canvas");
							myCanvas.width = img.width;
							myCanvas.height = img.height;
							let context = myCanvas.getContext("2d");
							context.drawImage(img, 0, 0);
							_this.imgBase64Data = myCanvas.toDataURL("image/jpeg", 0.1);
							_this.upLoadImg();
						};
					};
				}
			},
			dataURItoBlob: function(imgBase64Data) {
				let photoType = imgBase64Data.substring(imgBase64Data.indexOf(",") + 1);
				//进制转换
				const b64toBlob = (b64Data, contentType = "", sliceSize = 512) => {
					const byteCharacters = atob(b64Data);
					const byteArrays = [];
					for (
						let offset = 0; offset < byteCharacters.length; offset += sliceSize
					) {
						const slice = byteCharacters.slice(offset, offset + sliceSize);
						const byteNumbers = new Array(slice.length);
						for (let i = 0; i < slice.length; i++) {
							byteNumbers[i] = slice.charCodeAt(i);
						}
						const byteArray = new Uint8Array(byteNumbers);
						byteArrays.push(byteArray);
					}
					const blob = new Blob(byteArrays, {
						type: contentType
					});
					return blob;
				};
				const contentType = "image/jepg";
				const b64Data2 = photoType;
				const blob = b64toBlob(b64Data2, contentType);
				return blob;
			},
			upLoadImg: function() {
				let that = this,
					imgToken = null;
				let fileTyppe = that.fileType;
				// 构造上传图片的数据
				let formData = new FormData();
				const contentType = "image/jpeg";
				const blob = that.dataURItoBlob(that.imgBase64Data);
				formData.append("file", blob, "client-camera-photo.png");
				formData.append("filetype", fileTyppe);
				that
					.$http({
						url: "/file/mobile_img.php",
						method: "POST",
						data: formData,
						headers: {
							"Content-Type": "multipart/form-data"
						}
					})
					.then(function(res) {
						if (res.data.errno === 0) {
							that.imgNum++;
							that.uploadSuccessNum++;
							var imgUrl = res.data.data.url;
							var AuthImgUrl = res.data.data.auth_url;
							that.houseImg.push({
								url: AuthImgUrl
							});
							that.housingImg.push({
								url: imgUrl
							});
							that.$emit("picUrlSet", that.housingImg);
						} else {
							that.uploadFailedNum++;
						}
						if (that.uploadSuccessNum + that.uploadFailedNum === that.selectNum) {
							that.$dialog.loading.close();
							that.$dialog.toast({
								mes: `${that.uploadSuccessNum} 张成功，${that.uploadFailedNum} 张失败，`,
								timeout: 3000,
								icon: "success"
							});
						}
					})
					.catch(function(err) {
						that.$dialog.loading.close();
						that.$dialog.toast({
							mes: '上传异常',
							timeout: 1500,
							icon: "error"
						});
					});
			},
			delectPic: function(index) {
				this.houseImg.splice(index, 1);
				this.housingImg.splice(index, 1);
				this.imgNum--;
				this.$emit("picUrlSet", this.housingImg);
			}
		}
	};
</script>

<style scoped>
</style>
