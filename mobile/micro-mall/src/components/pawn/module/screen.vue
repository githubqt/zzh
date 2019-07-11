<template>
    <section class="screen-container screen-modal" v-show="isShow"  @click.self="closeModal">
		<section class="screen-content-box" v-show="isShow">
			<section class="screen-content-scroll">
				<!-- 品牌 -->
	            <div class="screen-content-con" v-show="screenType=== 'Brand'">
	            	<div class="screen-brand-left">
	            		<div class="screen-brand-list" v-for="(value, key) in mbrandBrand" :key="key" :id="'brand'+key">
	            			<div class="screen-brand-header">{{key}}</div>
	            			<yd-cell-item v-for="(item, index) in value" :key="index"
	            						  :class="{'sort-on':item.isCheck}" @click.native="selectBrand(item, index, mbrandBrand)">
				                <span slot="left" :id="item.id">{{item.name}}</span>
				                <yd-icon size=".3rem" color="#fff" name="checkoff" slot="right"></yd-icon>
				            </yd-cell-item>
	            		</div>
	            	</div>
		            <!-- 锚点 -->
		            <ul class="screen-brand-right">
		            	<li v-for="(value, key) in mbrandBrand" :key="key">
		            		<a :href="'#brand'+key">{{key}}</a>
		            	</li>
		            </ul>
	            </div>
	            <!-- 品类 -->
	            <div class="screen-content-con" v-show="screenType=== 'Category'">
	            	<yd-flexbox>
			            <ul class="screen-category-menu">
			            	<li v-for="(item, index) in menuList" :key="index">
				        		<a class="screen-category-list" :class="{'screen-category-active':index===menuIndex}"
				        		   :id="item.id" @click="relationClick(item, index)" ref="menuRef">
				        			{{item.name}}
				        		</a>
				        	</li>
			            </ul>
			            <yd-flexbox-item class="screen-category-content">
			            	<div v-for="(item, key) in mbrandCategory" :key="key">
				            	<yd-cell-item v-for="(list, index) in item.child" :key="index"
            				  	:class="{'sort-on': list.isCheck}" @click.native="selectCategory(list, index, mbrandCategory)">
					                <span slot="left">{{list.name}}</span>
					                <yd-icon size=".3rem" color="#fff" name="checkoff" slot="right"></yd-icon>
					            </yd-cell-item>
				            </div>
			            </yd-flexbox-item>
			        </yd-flexbox>
	            </div>

	    	</section>

	    </section>
	</section>
</template>

<script>
import Qs from 'qs'
export default {
	name: 'Store',
	data() {
        return {
        	isShow: false,
        	sortStatus: 0,
        	mbrandBrand: {},
        	menuList: [],
        	menuIndex: 0,
        	mbrandCategory: []
        }
    },
    props: ['isScreenShow', 'screenType'],
    created() {

    },
    mounted: function () {
        this.$nextTick(function () {
            this.isShow = this.isScreenShow
        	this.leftMenuGet();
        	this.mbrandBrandGet();
        })
    },
    methods: {
    	closeModal() {
    		this.screenIndex = 0;
            this.isShow = false
        	let mo = function(e){ e.preventDefault() };
            document.removeEventListener("touchmove",mo,false);
            document.body.style.overflowX = '';//出现滚动条
            document.body.style.overflowY = '';//出现滚动条
            document.body.style.overflow = '';//出现滚动条
            this.$emit('setScreenId', {})
    	},
    	mbrandBrandGet() {
    		let _this = this,
    			_data = Qs.stringify({ is_hit: 0 }),
    			_resultData = localStorage.getItem('loanListData');

    		_this.$http.post('/api/v1/Brand/list', _data).then(function (response) {
            	if (response.data.errno === '0') {
                    let _arr = {};
                    response.data.result.forEach(function (item, index) {
                    	let array = _arr[item['first_letter']] || [];
					    array.push(item);
					    _arr[item['first_letter']] = array;
					});
                    _this.mbrandBrand = _arr;
            	}else {
            		_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
            	}
            }).catch(function (error) {
            	_this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
    	},
    	selectBrand(list, index, lists) {
            let _this = this;
            for (var value in lists) {
                var child = lists[value]
                child.forEach(function (k, j) {
                    _this.$set(k, 'isCheck', false)
                })
            }
            _this.$set(list, 'isCheck', true)
            _this.$emit('setScreenId', {'type': _this.screenType, 'id': list.id, 'name': list.name})
            _this.closeModal()
    	},
    	selectCategory(list, index, lists) {
    		let _this = this;
            lists.forEach(function (ele, i) {
                let child = ele.child
                child.forEach(function (k, j) {
                    _this.$set(k, 'isCheck', false)
                })
            })
            _this.$set(list, 'isCheck', true)
            _this.$emit('setScreenId', {'type': _this.screenType, 'id': list.id, 'name': list.name})
            _this.closeModal()
    	},
    	leftMenuGet() {
    		let _this = this,
    			_data = Qs.stringify({ pid: 0 });

    		_this.$http.post('/api/v1/Category/list', _data).then(function (response) {
            	if (response.data.errno === '0') {
                    _this.menuList = response.data.result;
                    _this.relationClick(_this.menuList[0], 0)
            	}else {
            		_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
            	}
            }).catch(function (error) {
            	_this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
    	},
    	relationClick(item, index) {

    		let  _this = this;
          	let  _id = item.id;
    		let  _data = Qs.stringify({ id: _id });

    		_this.menuIndex = index;

    		_this.$http.post('/api/v1/Category/child', _data).then(function (response) {
                if (response.data.errno === '0') {
                    _this.mbrandCategory = response.data.result.child;
                }else {
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function (error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
    	}
    },
    watch: {
        isScreenShow: function (val) {
            this.isShow = val
        },
        screenType: function (val) {
            this.screenType = val
            let mo = function(e){ e.preventDefault() };
	        document.body.style.overflow='hidden';
	        document.addEventListener("touchmove",mo,false);//禁止页面滑动
        }
    }
}
</script>

<style scoped>
@import "../../../assets/css/components/pawn/module/screen";
</style>
