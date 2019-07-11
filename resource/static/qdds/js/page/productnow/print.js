
//选择打印机
function moreprint(obj){
try{
    LODOP=getLodop(); 
    LODOP.PRINT_INIT("");
    LODOP.ADD_PRINT_RECT(0,0,0,0,0,0);
    
    $(obj).linkbutton('disable');  
    
    var firstPage = 0;
    // 条码样式规格60mm*40mm
	$(".print-body").each(function(){
        var id = $( this ) . attr( 'data-id' );
        var num = $( this ) . attr( 'data-num' );
        var self_code = $( this ) . attr( 'data-self_code' );
        var halou = $( this ) . attr( 'data-name' );
        var name = halou.substring(0,30);
        var sale_price = $( this ) . attr( 'data-sale_price' ); 
        var phone = $( this ) . attr( 'data-phone' );
        var company = $( this ) . attr( 'data-company' );
        var pm_url =    $( this ) . attr( 'data-url' );
        for ( var i = 1; i <= num; i++ ) {
        	
            if ( firstPage != 0 ) {
                LODOP . NEWPAGEA();
            } else {
                firstPage = 1;
            }

            //字体
            LODOP . SET_PRINT_STYLE( "FontName", "微软雅黑" );
            //尺寸
            LODOP . SET_PRINT_PAGESIZE( 1, "60mm", "40mm", "" );
           
            //商品名称 
            LODOP . SET_PRINT_STYLE( "FontSize", 8 ); 
            LODOP . ADD_PRINT_TEXT( "6mm", "2mm", "33mm", "14mm", name );
           
            //销售价
            LODOP . SET_PRINT_STYLE( "FontSize", 12);
            LODOP . ADD_PRINT_TEXT( "18mm", "2mm", "33mm", "6mm", "￥" + sale_price );  
			LODOP . SET_PRINT_STYLE( "Bold", 1 );			
            
            //条码
            LODOP . SET_PRINT_STYLE( "FontSize", 7 );
            LODOP . ADD_PRINT_BARCODE( "24mm", '2mm', "33mm", "14mm", check_code(self_code), self_code );
            
            //二维码
            LODOP . SET_PRINT_STYLE( "FontSize", 8 );
            LODOP . ADD_PRINT_BARCODE( "4mm", '36mm', "30mm", "30mm", 'QRCode', pm_url );            
            
            //电话 
            LODOP . SET_PRINT_STYLE( "FontSize", 7); 
            LODOP . ADD_PRINT_TEXT( "28mm", "34mm", "26mm", "6mm", '电话'+phone );
            
            //公司名称 
            LODOP . SET_PRINT_STYLE( "FontSize", 7 ); 
            LODOP . ADD_PRINT_TEXT( "32mm", "34mm", "28mm", "6mm", company );                     
            
        } 
	});
	
    //LODOP.SET_PRINT_MODE("CATCH_PRINT_STATUS",true);
	if (LODOP.CVERSION) { 
	    LODOP.On_Return=function(TaskID,Value){console.info(Value);};
	    LODOP.PRINTA();
	    return;
	} else {
		if(LODOP==true){
	    LODOP.PRINTA();
		}
	  return;
	}
	
	 }catch(c){
		  
	 }
}


//条码检测                
function check_code(a) {
    var b = "Code93";
    a = a + "";
    if (a.length === 18) {
        b = "EAN128C";
    }
    if (a.length === 17) {
        b = "Code93";
    }
    if (a.length === 16) {
        b = "128Auto";
    }
    if (a.length === 15) {
        b = "Code93";
    }
    if (a.length === 13 || a.length === 14) {
        b = "128A";
    }
    return b;
}


