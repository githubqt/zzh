/**
 * @author zhaoyu
 */
var QddUpload = {
   uploader:null,
   urlSwf : "/common/js/webuploader/0.1.5/Uploader.swf",					//swf地址
   urlUpload: '/index.php?c=Upload&a=image&classify=brand',	//请求方式
   filePicker: 'filePicker',	//焦点按钮id
   accept:{						//接受文件类型
		title: 'Images',
		extensions: 'gif,jpg,jpeg,bmp,png',
		mimeTypes: 'image/*'
   },
   thumbnailWidth:110,
   thumbnailHeight:110,		
   fileSingleSizeLimit: 5 * 1024 * 1024,    // 50 M
   isChecke: true,				//验证状态	
   duplicate: true, 
   state:"pending",				//当前状态
   btn:"btn-star",				//开始按钮id
   list:"fileList",   			//列表id
   filename:"filename",   			//文件名称
   logo_url:"logo_url",			//回写input
   file_upload_nolook:"不能预览",		//查看提示
   file_upload_sleep:"等待上传...",		//等待提示
   file_upload_progress:"上传中",		//进程提示
   file_upload_now:"已上传",			//已完成提示
   file_upload_err:"上传出错",			//错误提示
   file_upload_pause:"上传暂停",		//停止提示
   file_upload_start:"上传开始",			//开始提示
   init: function(){
   		var swf = this.urlSwf;
   		var server = this.urlUpload;
   		var filePicker = this.filePicker;
   		var accept = this.accept;
   		var fileSingleSizeLimit = this.fileSingleSizeLimit;
   		this.uploader =  WebUploader.create({
			auto: true,
			swf: swf,
			// 文件接收服务端。
			server: server,
			// 选择文件的按钮。可选。
			// 内部根据当前运行是创建，可能是input元素，也可能是flash.
			pick: '#'+filePicker,
		
			// 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
			resize: false,
			// 只允许选择图片文件。
			accept: accept,
			fileSingleSizeLimit: fileSingleSizeLimit    // 50 M
		});
		var self = this;
		//拦截队列
		this.uploader.on("fileQueued",function(file){
			self.fileQueued(file);
		});
		//拦截进程等待
		this.uploader.on("uploadProgress",function(file,percentage){
			self.uploadProgress(file,percentage);
		});
		
		this.uploader.on("uploadSuccess",function(file,response){
			self.uploadSuccess(file,response);
		});
		
		this.uploader.on("uploadError",function(file){
			self.uploadError(file);
		});
		
		this.uploader.on("uploadComplete",function(file){
			self.uploadComplete(file);
		});
		this.uploader.on("all",function(type){
			self.all(type);
		});
		
		var uploaderObj = this.uploader;
		var stateTxt = this.state;
		$("#"+this.btn).on('click', function () {
		    if (stateTxt === 'uploading') {
		        uploaderObj.stop();
		    } else {
		        uploaderObj.upload();
		    }
		});
				
   },
   run: function(){
   		this.init();		
   		window.setTimeout(function(){
			if($("input[name='file']").length>0){
		 		$("input[name='file']").removeAttr("accept");
			}
		},1000);
   },
   fileQueued: function( file ) { //队列
   		var fal = this.lastCheck();
   		this.isChecke = fal;
   		if (fal == false){
   			this.uploader.reset();
   			return false;  			
   		}		
		
		if (document.getElementById('logo_show') == undefined) {

			var $li = $(
			'<div id="' + file.id + '" class="item">' +
				'<div class="pic-box"><img></div>'+
				'<div class="info">' + file.name + '</div>' +
				'<p class="state">'+this.file_upload_sleep+'</p>'+
			'</div>'
			);
			$img = $li.find('img');

			$("#"+this.list).html( $li );
			$("#"+this.filename).html( file.name );

			var thumbnailWidth = this.thumbnailWidth;
			var thumbnailHeight = this.thumbnailHeight;
			var file_upload_nolook = this.file_upload_nolook;
			// 创建缩略图
			// 如果为非图片文件，可以不用调用此方法。
			// thumbnailWidth x thumbnailHeight 为 100 x 100
			this.uploader.makeThumb( file, function( error, src ) {
				if ( error ) {
					$img.replaceWith('<span>'+file_upload_nolook+'</span>');
					return;
				}
				$img.attr( 'src', src );
			}, thumbnailWidth, thumbnailHeight );
		}
   			
   },
   uploadProgress: function( file, percentage ) {
   		var $li = $( '#'+file.id ),
		$percent = $li.find('.progress-box .sr-only');
		// 避免重复创建
		if ( !$percent.length ) {
			$percent = $('<div class="progress-box"><span class="progress-bar radius"><span class="sr-only" style="width:0%"></span></span></div>').appendTo( $li ).find('.sr-only');
		}
		$li.find(".state").text(this.file_upload_progress);
		$percent.css( 'width', percentage * 100 + '%' );
   },
   uploadSuccess: function( file ,response) {
   		//console.log(document.getElementById('show_'+this.logo_url))
   		//console.log('show_'+this.logo_url)
		$("#"+this.logo_url).val(response.data);
		if(document.getElementById('show_'+this.logo_url) != undefined){
            $("#show_"+this.logo_url).attr("src",response.data);
		}else if (document.getElementById('logo_show') != undefined) {
			$("#logo_show").attr("src",response.data);
		}
		$( '#'+file.id ).addClass('upload-state-success').find(".state").text(this.file_upload_now);
		if (this.isChecke) {
			this.callBack(response);
		}
		
   },
   uploadError: function( file ) {
		$( '#'+file.id ).addClass('upload-state-error').find(".state").text(this.file_upload_err);
   },
   uploadComplete: function( file ) {
		$( '#'+file.id ).find('.progress-box').fadeOut();
   },
   callBack: function(response){
   	
   },
   all: function(type){
   		if (type === 'startUpload') {
	        this.state = 'uploading';
	    } else if (type === 'stopUpload') {
	        this.state = 'paused';
	    } else if (type === 'uploadFinished') {
	        this.state = 'done';
	    }
	    if (this.state === 'uploading') {
	        $("#"+this.btn).text(this.file_upload_pause);
	    } else {
	        $("#"+this.btn).text(this.file_upload_start);
	    }
   },
   lastCheck: function(){
   		return true;
   }
};