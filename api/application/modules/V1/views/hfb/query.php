<?php
header ( 'Content-type:text/html;charset=utf-8' );
include_once '../func/HFBConfig.php';
?>


<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!--[if lt IE 10]>
<script>alert("为了更好的体验，不支持IE10以下的浏览器。请选择google chrome 或者 firefox 浏览器。"); location.href="http://www.hefupal.com";</script>
<![endif]-->
<link rel="stylesheet"
	href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet"
	href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
<script src="http://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="http://cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<title>易势支付-商户demo</title>
<style type="text/css">
body {
	padding-top: 70px;
}
</style>

</head>
<body>
	<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container">
			<a class="navbar-brand" href="index.html"><strong>首页</strong></a> <a
				class="navbar-brand"><strong>查询（<s>ie1-9</s>）
			</strong></a>
		</div>
	</nav>

	<div class="container-fluid">
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
				<form role="form" action="queryhandler.php" method="post"
					name="payForm">
					<div class="form-group">
						<label for="tranSerialNumY">原交易流水号</label> <input type="text"
							class="form-control" name="tranSerialNumY" id="tranSerialNumY"
							required />
					</div>
					<div class="form-group">
						<label for="remark">备注</label> <input type="text"
							class="form-control" name="remark" id="remark" />
					</div>
					<div class="form-group">
						<label for="YUL1">预留字段1</label> <input type="text"
							class="form-control" name="YUL1" id="YUL1">
					</div>
					<button type="submit" class="btn btn-primary">提交</button>
				</form>
			</div>
		</div>
	</div>


</body>
</html>