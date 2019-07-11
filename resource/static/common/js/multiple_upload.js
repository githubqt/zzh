/**
 * @author zhaoyu
 * @date 2016-12-09
 */

var QddMulitipleUpload = {
	uploader2:null,
	urlSwf : "/common/js/webuploader/0.1.5/Uploader.swf",					//swf地址
   	urlUpload: '/index.php?c=Upload&a=image&classify=brand',	//请求方式
   	filePicker: 'filePicker-2',	//焦点按钮id
   	filePicker2: 'filePicker2',
   	accept: {
				title: 'Files',
				extensions: 'gif,jpg,jpeg,bmp,png,pdf,xls,xlsx,doc,docx',
				mimeTypes: 'image/*;application/*'
	},
	file_upload_err_support:"您取消了更新!",
	file_upload_init_fail:"安装失败!",
	file_upload_init_success:"安装已成功，请刷新！",
	file_upload_btn_click:"点击选择文件",
	file_upload_add:"继续添加",
	file_upload_cancel:"取消上传",
	file_upload_error_maxfile:"上传文件的大小超过了HTML表单中MAX_FILE_SIZE选项指定的值",
	file_upload_pause:"上传暂停",
	file_upload_error_fail:"上传失败",
	file_upload_nolook_now:"预览中",
	file_upload_nolook:"预览出错",
	file_upload_ready:"选中%d张图片，共%s。",
	file_upload_confirm:"已成功上传%d张照片至XX相册，%d张照片上传失败，<a class='retry' href='#'>重新上传</a>失败图片或<a class='ignore' href='#'>忽略</a>",
	file_upload_status:"共%d张（%s），已上传%d张",
	file_upload_failnum:"，失败%d张",
	file_upload_pause:"上传暂停",
	file_upload_start:"上传开始",
	file_upload_success:"上传成功",
	fileNumLimit: 300,
    fileSizeLimit: 200 * 1024 * 1024,    // 200 M
    fileSingleSizeLimit: 50 * 1024 * 1024,    // 50 M
	wrap: $('.uploader-list-container'),
	queue: null,
	statusBar: null,  // 状态栏，包括进度和控制按钮
	info: null,  		 // 文件总体选择信息。
	upload:  null,	// 上传按钮
	placeHolder: null, // 没选择文件之前的内容。
	progress: null,
	state: 'pedding',								// 可能有pedding, ready, uploading, confirm, done
	fileCount: 0,									// 添加的文件数量
	fileSize: 0,									// 添加的文件总大小
	ratio: window.devicePixelRatio || 1,			// 优化retina, 在retina下这个值是2
	thumbnailWidth: 110 ,					// 缩略图大小
	thumbnailHeight:  110 ,
	percentages:{},									 // 所有文件的进度信息，key为file id
	isSupportBase64: function(){						// 判断浏览器是否支持图片的base64
		var data = new Image();
        var support = true;
        data.onload = data.onerror = function() {
            if( this.width != 1 || this.height != 1 ) {
                support = false;
            }
        }
        data.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
        return support;
	},
	flashVersion: function(){						 // 检测是否已经安装flash，检测flash的版本
		var version;

        try {
            version = navigator.plugins[ 'Shockwave Flash' ];
            version = version.description;
        } catch ( ex ) {
            try {
                version = new ActiveXObject('ShockwaveFlash.ShockwaveFlash')
                        .GetVariable('$version');
            } catch ( ex2 ) {
                version = '0.0';
            }
        }
        version = version.match( /\d+/g );
        return parseFloat( version[ 0 ] + '.' + version[ 1 ], 10 );
	},
	supportTransition: function(){
		var s = document.createElement('p').style,
            r = 'transition' in s ||
                    'WebkitTransition' in s ||
                    'MozTransition' in s ||
                    'msTransition' in s ||
                    'OTransition' in s;
        s = null;
        return r;
	},
	init: function(){
	
	   this.queue = $( '<ul class="filelist"></ul>' ).appendTo( '.queueList' );
	   this.statusBar =  $('.uploader-list-container').find( '.statusBar' );  // 状态栏，包括进度和控制按钮
	   this.info = $('.uploader-list-container').find( '.statusBar' ).find( '.info' ); 		 // 文件总体选择信息。
	   this.upload = $('.uploader-list-container').find( '.uploadBtn' );	// 上传按钮
	   this.placeHolder = $('.uploader-list-container').find( '.placeholder' ); // 没选择文件之前的内容。
	   this.progress = $('.uploader-list-container').find( '.statusBar' ).find( '.progress' ).hide();
	   this.thumbnailWidth  = this.thumbnailWidth * this.ratio;					// 缩略图大小
	   this.thumbnailHeight   = this.thumbnailHeight * this.ratio; 
	   var file_upload_err_support = this.file_upload_err_support;
	   var file_upload_init_fail = this.file_upload_init_fail;
	   var file_upload_err_support = this.file_upload_err_support;
	   if ( !WebUploader.Uploader.support('flash') && WebUploader.browser.ie ) {
            // flash 安装了但是版本过低。
            if (this.flashVersion) {
                (function(container) {
                    window['expressinstallcallback'] = function( state ) {
                        switch(state) {
                            case 'Download.Cancelled':
                                $.messager.alert('提示',file_upload_err_support)
                                break;

                            case 'Download.Failed':
                                $.messager.alert('提示',file_upload_init_fail)
                                break;

                            default:
                                $.messager.alert('提示',file_upload_init_fail);
                                break;
                        }
                        delete window['expressinstallcallback'];
                    };

                    var swf = 'expressInstall.swf';
                    // insert flash object
                    var html = '<object type="application/' +
                            'x-shockwave-flash" data="' +  swf + '" ';

                    if (WebUploader.browser.ie) {
                        html += 'classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ';
                    }

                    html += 'width="100%" height="100%" style="outline:0">'  +
                        '<param name="movie" value="' + swf + '" />' +
                        '<param name="wmode" value="transparent" />' +
                        '<param name="allowscriptaccess" value="always" />' +
                    '</object>';

                    container.html(html);

                })(this.wrap);

            // 压根就没有安转。
            } else {
                this.wrap.html('<a href="http://www.adobe.com/go/getflashplayer" target="_blank" border="0"><img alt="get flash player" src="http://www.adobe.com/macromedia/style_guide/images/160x41_Get_Flash_Player.jpg" /></a>');
            }
            return;
        } else if (!WebUploader.Uploader.support()) {
            $.messager.alert('提示', file_upload_err_support );
            return;
        }
        var filePicker = this.filePicker;
        var urlSwf = this.urlSwf;
        var urlUpload = this.urlUpload;
        var accept = this.accept;
		var fileNumLimit = this.fileNumLimit;
		var fileSizeLimit = this.fileSizeLimit;
		var fileSingleSizeLimit = this.fileSingleSizeLimit;
		var self = this;
		var filePicker2 = this.filePicker2;
		var file_upload_btn_click = this.file_upload_btn_click;
		var file_upload_add = this.file_upload_add;
        this.uploader2 = WebUploader.create({
            pick: {
                id: '#'+filePicker,
                label: file_upload_btn_click
            },
            swf: urlSwf,
            server: urlUpload,
            // 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
            disableGlobalDnd: true,
            accept:accept,
            fileNumLimit: fileNumLimit,
            fileSizeLimit: fileSizeLimit,    // 200 M
            fileSingleSizeLimit: fileSingleSizeLimit    // 50 M
        }); 
        
       
        
        this.uploader2.addButton({
            id: '#'+filePicker2,
            label: file_upload_add
        });
        
        this.uploader2.on('ready', function() {
           self.ready();
        });
        
        this.uploader2.on( 'dndAccept', function( items ) {
    		self.dndAccept(items);  
        });
		this.uploader2.on('dialogOpen', function() {
            self.dialogOpen();
        });
        
        //拦截队列
		this.uploader2.onFileQueued = function(file){
			self.onFileQueued(file);
		}
		//拦截进程等待
		this.uploader2.onUploadProgress = function(file,percentage){
			self.onUploadProgress(file,percentage);
		};
		
		this.uploader2.onFileDequeued = function( file ) {
            self.onFileDequeued(file);
        };
        
		this.uploader2.on("uploadSuccess",function(file,response){
			self.uploadSuccess(file,response);
		});
	
	
		this.uploader2.on("all",function(type){
			self.all(type);
		});
		
        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
		

        this.upload.on('click', function() {
            if ( $(this).hasClass( 'disabled' ) ) {
                return false;
            }

            if ( self.state === 'ready' ) {
                self.uploader2.upload();
            } else if ( self.state === 'paused' ) {
                 self.uploader2.upload();
            } else if ( self.state === 'uploading' ) {
                self.uploader2.stop();
            }
        });

        this.info.on( 'click', '.retry', function() {
             self.uploader2.retry();
        } );

        this.info.on( 'click', '.ignore', function() {
            $.messager.alert('提示', 'todo' );
        } );

        this.upload.addClass( 'state-' + this.state );
        this.updateTotalProgress();  
	},
	run: function(){
		this.init();
	},
	dndAccept: function( items ) { // 拖拽时不接受 js, txt 文件。
		var denied = false,
            len = items.length,
            i = 0,
            // 修改js类型
            unAllowed = 'text/plain;application/javascript ';

        for ( ; i < len; i++ ) {
            // 如果在列表里面
            if ( ~unAllowed.indexOf( items[ i ].type ) ) {
                denied = true;
                break;
            }
        }

        return !denied;  
	},
	dialogOpen: function(){
		 console.log('here');
	},
    removeFile: function(file){  // 负责view的销毁
    	
        var $li = $('#'+file.id);
		delete this.percentages[ file.id ];
        this.updateTotalProgress();
        $li.off().find('.file-panel').off().end().remove();
     },
	  // 当有文件添加进来时执行，负责view的创建
    addFile: function ( file ) {
    		var file_upload_cancel = this.file_upload_cancel;
    		var file_upload_error_maxfile = this.file_upload_error_maxfile;
    		var file_upload_pause = this.file_upload_pause;
    		var file_upload_error_fail = this.file_upload_error_fail;
    		var file_upload_nolook_now = this.file_upload_nolook_now;
    		var file_upload_nolook = this.file_upload_nolook;
    		var self = this;
    		console.log(file)
            var $li = $( '<li id="' + file.id + '">' +
                    '<p class="title">' + file.name + '</p>' +
                    '<p class="imgWrap"></p>'+
                    '<p class="progress"><span></span></p>' +
                    '</li>' ),
                $btns = $('<div class="file-panel" style="height: 30px;">' +
                    '<span class="cancel">'+ file_upload_cancel +'</span>' +
                    '</div>').appendTo( $li ),
	                $prgress = $li.find('p.progress span'),
	                $wrap = $li.find( 'p.imgWrap' ),
	                $info = $('<p class="error"></p>'),
				
                showError = function( code ) {
                    switch( code ) {
                        case 'exceed_size':
                            text = file_upload_error_maxfile;
                            break;

                        case 'interrupt':
                            text = file_upload_pause;
                            break;

                        default:
                            text = file_upload_error_fail;
                            break;
                    }

                    $info.text( text ).appendTo( $li );
                };

            if ( file.getStatus() === 'invalid' ) {
                this.showError( file.statusText );
            } else {
                // @todo lazyload
                $wrap.text( file_upload_nolook_now );
                this.uploader2.makeThumb( file, function( error, src ) {
                    var img;
					
                    if ( error ) {
                        $wrap.text( file_upload_nolook );
                        return;
                    }
	
                    if( self.isSupportBase64 ) {
                    	console.log('<img src="'+src+'">')
                        img = $('<img src="'+src+'">');
                        $wrap.empty().append( img );
                       
                    }
                    
               	}, self.thumbnailWidth, self.thumbnailHeight );

	            self.percentages[ file.id ] = [ file.size, 0 ];
	            file.rotation = 0;
	     } 
	     
    	 file.on('statuschange', function( cur, prev ) {
                if ( prev === 'progress' ) {
                    $prgress.hide().width(0);
                } else if ( prev === 'queued' ) {
                    $li.off( 'mouseenter mouseleave' );
                    //$btns.remove();
                }
                // 成功
                if ( cur === 'error' || cur === 'invalid' ) {
                    self.showError( file.statusText );
                    self.percentages[ file.id ][ 1 ] = 1;
                } else if ( cur === 'interrupt' ) {
                    self.showError( 'interrupt' );
                } else if ( cur === 'queued' ) {
                    self.percentages[ file.id ][ 1 ] = 0;
                } else if ( cur === 'progress' ) {
                    self.info.remove();
                    $prgress.css('display', 'block');
                } else if ( cur === 'complete' ) {
                    $li.append( '<span class="success"></span>' );
                }

                $li.removeClass( 'state-' + prev ).addClass( 'state-' + cur );
            });


            $btns.on( 'click', 'span', function() {
                var index = $(this).index(),
                   	deg;
				
                switch ( index ) {
                    case 0:
                        self.uploader2.removeFile( file );
                        return;

                    case 1:
                        file.rotation += 90;
                        break;

                    case 2:
                        file.rotation -= 90;
                        break;
                }

                if ( self.supportTransition ) {
                    deg = 'rotate(' + file.rotation + 'deg)';
                    $wrap.css({
                        '-webkit-transform': deg,
                        '-mos-transform': deg,
                        '-o-transform': deg,
                        'transform': deg
                    });
                } else {
                    $wrap.css( 'filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation='+ (~~((file.rotation/90)%4 + 4)%4) +')');
                }


            });

            $li.appendTo( this.queue );
     },
     
     updateTotalProgress:  function () {
            var loaded = 0,
                total = 0,
                spans = this.progress.children(),
                percent;
       		
            $.each( this.percentages, function( k, v ) {
                total += v[ 0 ];
                loaded += v[ 0 ] * v[ 1 ];
            } );

            percent = total ? loaded / total : 0;
            spans.eq( 0 ).text( Math.round( isNaN(percent)?1:percent * 100 ) + '%' );
            spans.eq( 1 ).css( 'width', Math.round( isNaN(percent)?1:percent * 100 ) + '%' );
            this.updateStatus();
     },
     updateStatus: function () {
            var text = '', stats;
            var file_upload_ready = this.file_upload_ready;

            if ( this.state === 'ready' ) {
            	text = sprintf(file_upload_ready,this.fileCount,WebUploader.formatSize( this.fileSize ));
                //text = '选中' + fileCount + '张图片，共' +WebUploader.formatSize( fileSize ) + '。';
            } else if ( this.state === 'confirm' ) {
                stats = this.uploader2.getStats();
                if ( stats.uploadFailNum ) {
                    text = sprintf(this.file_upload_confirm,stats.successNum,stats.uploadFailNum);
                    //text = '已成功上传' + stats.successNum+ '张照片至XX相册，'+
                     //   stats.uploadFailNum + '张照片上传失败，<a class="retry" href="#">重新上传</a>失败图片或<a class="ignore" href="#">忽略</a>'
                }

            } else {
                stats = this.uploader2.getStats();
                text =  sprintf(this.file_upload_status,this.fileCount, WebUploader.formatSize( this.fileSize ),stats.successNum);
               // text = '共' + fileCount + '张（' +
                //        WebUploader.formatSize( fileSize )  +
                  //      '），已上传' + stats.successNum + '张';

                if ( stats.uploadFailNum ) {
                	text +=  sprintf(this.file_upload_failnum,stats.uploadFailNum);
                    //text += '，失败' + stats.uploadFailNum + '张';
                }
            }
            
            this.info.html( text );
     },
     setState: function ( val ) {
            var file, stats;
			
            if ( val === this.state ) {
                return;
            }

            this.upload.removeClass( 'state-' + this.state );
            this.upload.addClass( 'state-' + val );
            this.state = val;

            switch ( this.state ) {
                case 'pedding':
                    this.placeHolder.removeClass( 'element-invisible' );
                    this.queue.hide();
                    $(this.statusBar).addClass( 'element-invisible' );
                    this.uploader2.refresh();
                    break;

                case 'ready':
                    this.placeHolder.addClass( 'element-invisible' );
                    $( '#'+this.filePicker2 ).removeClass( 'element-invisible');
                    this.queue.show();
                    $(this.statusBar).removeClass('element-invisible');
                    this.uploader2.refresh();
                    break;

                case 'uploading':
                    $( '#' + this.filePicker2).addClass( 'element-invisible' );
                    this.progress.show();
                    this.upload.text( this.file_upload_pause );
                    break;

                case 'paused':
                    this.progress.show();
                    this.upload.text( this.file_upload_pause );
                    break;

                case 'confirm':
                    this.progress.hide();
                    $( '#'+this.filePicker2 ).removeClass( 'element-invisible' );
                    this.upload.text( this.file_upload_start );

                    stats =  this.uploader2.getStats();
                    if ( stats.successNum && !stats.uploadFailNum ) {
                        this.setState( 'finish' );
                        return;
                    }
                    break;
                case 'finish':
                    stats = this.uploader2.getStats();
                    if ( stats.successNum ) {
                        $.messager.alert('提示', this.file_upload_success );
                    } else {
                        // 没有成功的图片，重设
                        this.state = 'done';
                        location.reload();
                    }
                    break;
            }

            this.updateStatus();
      },
      onUploadProgress: function( file, percentage ) {
            var $li = $('#'+file.id),
                $percent = $li.find('.progress span');

            $percent.css( 'width', this.percentage * 100 + '%' );
            this.percentages[ file.id ][ 1 ] = this.percentage;
            this.updateTotalProgress();
      },
      onFileQueued: function( file ) {
            this.fileCount++;
            this.fileSize += file.size;

            if ( this.fileCount === 1 ) {
                this.placeHolder.addClass( 'element-invisible' );
                $(this.statusBar).show();
            }

            this.addFile( file );
            this.setState( 'ready' );
            this.updateTotalProgress();
     },
     onFileDequeued: function( file ) {
            this.fileCount--;
            this.fileSize -= file.size;
			
            if ( !this.fileCount ) {
                this.setState( 'pedding' );
            }

            this.removeFile( file );
            this.updateTotalProgress();

     },
     uploadSuccess:  function( file ,response) {
		if(response.code == 200){
			var hiddenHtml = "<input type='hidden' class='uploadimglist' name='items[]' value='"+response.data+"'/>";
			$('#'+file.id ).append(hiddenHtml);
		}
	 },
	 all: function( type ) {
	        var stats;
	        switch( type ) {
	            case 'uploadFinished':
	                this.setState( 'confirm' );
	                break;
	
	            case 'startUpload':
	                this.setState( 'uploading' );
	                break;
	
	            case 'stopUpload':
	                this.setState( 'paused' );
	                break;
	
	        }
     },	
     ready: function(){
         //window.uploader2 = this.uploader2;
     },
	 onError: function( code ) {
            $.messager.alert('提示', 'Eroor: ' + code );
     }
};

           
    
        
        