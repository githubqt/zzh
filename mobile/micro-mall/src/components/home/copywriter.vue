<template>
    <section class="record-container">
        <yd-navbar title="" class="fixed-header">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>


	<section class="record-bittorrent"  >
	<hr>
		<yd-flexbox >
            <yd-flexbox-item >
                <yd-button size="large"  type="hollow" style="font-weight: bold;" bgcolor="#fff" >拍卖竞拍保证金须知</yd-button>
            </yd-flexbox-item>
        </yd-flexbox>

		<div style="text-align: center;">最后修订时间：2018年7月21日 </div>

	     <div style=" font-size: 13px; padding: 20px;"><em>本站是指域名为“zhahehe.COM”的网站，是由北京扎呵呵科技有限公司独立开发、运营的电子商务平台。

本站在此特别提醒用户认真阅读、理解本《用户协议》（以下简称“本协议”）各项条款，特别是其中所涉及的免除、限制本公司责任的条款、争议解决和法律适用等。本协议包括协议正文及所有本站已经发布的或将来可能发布或更新的各类规则，所有规则为本协议不可分割的组成部分，与本协议正文具有同等法律效力。请用户谨慎阅读并选择接受或不接受本协议内容及其所属各类规则（未成年不适合本用户协议）。用户在本站注册、登陆或使用本站服务，即表示用户完全接受本协议的全部条款，并同意接受其约束。

本站有权根据实际情况和具体需要修订本协议各项条款/各类规则，并在本站相关系统板块进行公示，修订发布一经公布后立即生效，无需另行单独通知。如果您继续使用本站的服务，即表示您接收新的协议条款且愿意遵守本站的各项制度规定。如果您不同意修订后的协议内容，您应立即停止使用本站服务。</em>

	<div style="font-weight: bolder;  margin-top: 10px;">一、保证金的缴纳</div>

<em><div>保证金是用户参加竞拍的凭证，如用户有意参加相关商品竞拍活动，则须缴纳参与竞拍活动的保证金。每个拍品的竞拍只需要缴纳一次保证金。
请在拍卖详情页点击“交保证金报名”按钮交纳保证金，报名前请先完成登陆。
。</div>


<div style="font-weight: bolder;  margin-top: 10px;">二、 保证金的处理</div>
<div>1.保证金的返还，以下两种情况下，将原路退还用户保证金：</div>
<div>1）用户未成功竞拍拍品；</div>
<div>2）用户竞拍成功后，商家主动关闭交易或未履约发货或因商家其他原因导致交易未完成的。
竞拍结束后，系统会在活动后的24个小时之内将款项内退还到支付账号中，如您缴纳的竞拍保证金符合退还条件，但是系统未予退还，您可以在小程序商城页面中点击我的-联系我们拨打我们的客服电话，我们会尽快帮您处理。
</div>

<div>2.保证金冲抵成交款</div>
<div>1）用户竞拍成功后，用户缴纳的保证金将自动转化为部分成交款，用户在我的拍品内支付余款即可。</div>


<div style="font-weight: bolder;  margin-top: 10px;">三、其他</div>
<div>由于扎呵呵平台的绝当品普遍为孤品（即：每件商品只有一件），故存在拍卖同时在线下门店被售出的情况，此时扎呵呵商城将为用户退还保证金，此情况不构成违约。</div>


</em>

</div>

        <hr/>


        </section>

     </section>

</template>


<script>
import Qs from 'qs'
export default {
    name: 'Home',
    components: {},
    data() {
        return {

        }
    },
    created() { },
    methods: {
        backGo() {  window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/') },
      }
}

</script>
<style>

.record-bittorrent {
    position: relative;
    margin-top: 1rem;
    background-color: #fff;
}



.corner-mark {
    position: absolute;
    top: 0;
    right: -.06rem;
    width: .4rem;
    height: .4rem;
    color: #fff;
    font-size: 13px;
    text-align: center;
    border-radius: 50%;
    line-height: .4rem;
    background-color: #DAB461;
}

.record-parameter-content {
    padding-left: 0;
}
.record-parameter-content div[class^="yd-flexbox"] {
    min-height: .5rem;
    min-width: .1rem;
    text-align: left;
    padding-left: .5rem;
    justify-content: flex-start;
    font-size: 14px;
}


.record-parameter-content .yd-flexbox-left {
    color: #000;

}
.record-parameter-content .yd-flexbox-left .label {
    position: relative;
}
.record-parameter-content .yd-flexbox-left .label:after {
    position: absolute;
    top: 0;
    right: -.6em;
}

.record-parameter-content:after {
    border-bottom: .1rem dotted #d9d9d9 !important;
}



.record-seckill-txt {
    width: 100%;
    color: #525252;
    font-size: .3rem;
    padding: .12rem .24rem;
    overflow: hidden;
}




</style>