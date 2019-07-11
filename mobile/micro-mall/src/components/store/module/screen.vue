<template>
	<section class="store-screen-container" :class="{'screen-modal':screenIndex===1 && filterStatus}" @click.self="closeModal">
		<!-- 排序 -->
		<div class="screen-header-order">
			<yd-flexbox class="screen-header-order-item">
				<!-- 综合排序 -->
				<yd-flexbox-item><div :class="zhOrder && 'active'" @click="showZhOrder">综合</div></yd-flexbox-item>
				<!-- 销量排序 -->
				<yd-flexbox-item><div :class="saleOrder && 'active'" @click="tapSaleOrder">销量</div></yd-flexbox-item>
				<!-- 价格排序 -->
				<yd-flexbox-item><div :class="priceOrder &&  'active'" @click="tapPriceOrder"><span >价格</span>
					<yd-icon custom name="sort-double-down" size="0.24rem"></yd-icon></div>
				</yd-flexbox-item>
				<!-- 新品排序 -->
				<yd-flexbox-item><div :class="newOrder &&  'active'" @click="tapNewOrder">新品</div></yd-flexbox-item>
				<!-- 筛选 -->
				<div class="more-search" @click="showFilter">
					<yd-icon custom name="shaixuan" size="0.3rem"></yd-icon> 筛选</div>


				<!-- 综合排序 显示内容 -->
				<div class="zh-order" v-show="zhOrder">
					<yd-flexbox v-for="(item, index) in sortList" :key="index" class="sort-list" :class="{'sort-on':sortStatus===index}" @click.native="selectSort(index, item.sort, item.order)">
						<yd-flexbox-item>{{item.name}}</yd-flexbox-item>
						<yd-icon size=".3rem" color="#fff" name="checkoff"></yd-icon>
					</yd-flexbox>
				</div>
			</yd-flexbox>
			<!-- 搜索 -->
			<div class="store-screen-header-box" v-show="filterStatus">
				<a class="store-screen-header-item" :class="{'sort-on':currentIndex===0}" @click="openBrand">
					品牌 <span v-show="brandData.length!==0">({{brandData.length}})</span>
				</a>
				<a class="store-screen-header-item" :class="{'sort-on':currentIndex===1}" @click="openCateg">
					品类 <span v-show="categoryData.length!==0">({{categoryData.length}})</span>
				</a>
				<!-- 网点 -->
				<a class="store-screen-header-item" :class="{'sort-on':currentIndex===2}" @click="openSort">网点</a>
				<!-- 搜索内容区域 -->
				<section class="store-screen-content-box" v-show="screenIndex===1">
					<section class="screen-content-scroll">
						<!-- 品牌 -->
						<div class="store-screen-content-con" v-show="currentIndex===0">
							<div class="store-screen-brand-left">
								<div class="store-screen-brand-list" v-for="(value, key) in mbrandBrand" :key="key" :id="'brand'+key">
									<div class="screen-brand-header">{{key}}</div>
									<yd-cell-item type="checkbox" v-for="(item, index) in value" :key="index">
										<span slot="left">{{item.name}}</span>
										<input slot="right" type="checkbox" :value="item.id" v-model="brandData" />
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
						<div class="store-screen-content-con" v-show="currentIndex===1">
							<yd-flexbox>
								<ul class="screen-category-menu">
									<li v-for="(item, index) in menuList" :key="index">
										<a class="screen-category-list" :class="{'screen-category-active':index===menuIndex}" :id="item.id" @click="relationClick(index,item.id)" ref="menuRef">
									        			{{item.name}}
									        		</a>
									</li>
								</ul>
								<yd-flexbox-item class="store-screen-category-content">
									<div v-for="(item, key) in mbrandCategory" :key="key">
										<yd-cell-item type="checkbox" v-for="(list, index) in item.child" :key="index">
											<span slot="left">{{list.name}}</span>
											<input slot="right" type="checkbox" :value="list.id" v-model="categoryData" />
										</yd-cell-item>
									</div>
								</yd-flexbox-item>
							</yd-flexbox>
						</div>
						<!-- 网点 -->
						<div class="store-screen-content-con" v-show="currentIndex===2">
							<yd-flexbox  >
								<ul class="screen-category-menu">
									<li>
										<a class="screen-category-list screen-category-active" >全部</a>
									</li>
									<li v-for="(info, index) in  ManyoutletsData" :key="index" >
										<a class="screen-category-list" :class="{'screen-category-active':index===ProvinceIndex}" :id="info.province_id" @click="provinceClick(index,info.province_id)" ref="Province">{{info.province_txt}}</a>
									</li>

									<li>
										<a class="screen-category-list"  @click="provinceClick(80,-90)">其它</a>
									</li>
								</ul>
								<yd-flexbox-item class="store-screen-category-content p2" >
									<!-- 城市 -->
									<div class="network-city" v-show="ProvinceIndex==''">
										<yd-flexbox>
											<yd-flexbox-item   >
												<b >全部</b> ({{cityNum}} 家网点)
											</yd-flexbox-item>
											<yd-flexbox-item class="btn-right">
												<!--<yd-button type="hollow">查看全部</yd-button>-->
											</yd-flexbox-item>
										</yd-flexbox>
									</div>
									<!-- 各区网点 -->
									<div class="network-area" v-for="(info, index) in  cityData" :key="index">
										<div v-for="(data, i) in  info.list" :key="i">
										<div class="area-title" >
											<yd-flexbox   >
												<yd-flexbox-item >
													<b>{{data.area_txt}}</b>
												</yd-flexbox-item>
												<yd-flexbox-item class="btn-right">
													 <!--<yd-button type="hollow"  >查看{{data.area_txt}}网点</yd-button>-->
												</yd-flexbox-item>
											</yd-flexbox>
										</div>
										<div class="area-item clearfix" >
											<div class="area-item-name active"   v-for="(da, int) in  data.data" :key="int" @click="showMap(da)">{{da.name}}<em v-show="da.province_id != -90">（{{da.count}}件商品）</em></div>

										</div>

										</div>
									</div>


								</yd-flexbox-item>
							</yd-flexbox>
						</div>
					</section>
					<div class="screen-content-btn" v-show="currentIndex !==2">
						<yd-button type="hollow" @click.native="clearScreen">清除</yd-button>
						<yd-button bgcolor="#dab461" color="#fff" style="border: .5px solid #dab461;" @click.native="saveScreen">确定</yd-button>
					</div>
				</section>
			</div>
		</div>
	</section>
</template>

<script>
	import Qs from 'qs'
	export default {
		name: 'Store',
		props: ['brandId', 'categoryId'],
		data() {
			return {
				searchVal: '',
				screenIndex: 0,
				currentIndex: 3,
				sortList: [{
						name: '综合排序',
						sort: '',
						order: ''
					},
					{
						name: '最近更新',
						sort: 'now_at',
						order: 'DESC'
					},
					{
						name: '销量排序',
						sort: 'sale_num',
						order: 'DESC'
					},
					{
						name: '价格由低至高',
						sort: 'sale_price',
						order: 'ASC'
					},
					{
						name: '价格由高至低',
						sort: 'sale_price',
						order: 'DESC'
					},
				],
				sortStatus: 0,
				brandData: [],
				categoryData: [],
				mbrandBrand: {},
				menuList: [],
				cityData: '',
                cityNum:'',
                ManyoutletsData:'',
				menuIndex: 0,
                ProvinceIndex:0,
				mbrandCategory: [],
				filterStatus: false, // 筛选项状态
				zhOrder: false, //综合状态
				saleOrder: false, // 销量状态
				priceOrder: false, //价格状态
				newOrder: false,//新品状态

			}
		},
		watch: {
			brandId(val, oldVal) {
				if (val) {
					this.$set(this.brandData, 0, val)
				}
			},
			categoryId(val, oldVal) {
				if (val) {
					this.$set(this.categoryData, 0, val)
				}
			}
		},
		created() {
			this.leftMenuGet();
			this.mbrandBrandGet();
			this.Manyoutlets();

		},
		methods: {
			closeModal() {
				this.screenIndex = 0;
				this.currentIndex = 3;
			},
			openBrand() {
				this.screenIndex = 1;
				this.currentIndex = 0;
			},
			openCateg() {
				this.screenIndex = 1;
				this.currentIndex = 1;
			},
			openSort() {
				this.screenIndex = 1;
				this.currentIndex = 2;
			},
			selectSort(index, sort, order) {
				let _this = this;
				_this.sortStatus = index;
				_this.screenIndex = 0;
				_this.currentIndex = 3;
				_this.$emit('screenSort', {
					sort: sort,
					order: order
				});
				_this.$emit('screenId', {
					brandId: _this.brandData.join(','),
					categoryId: _this.categoryData.join(',')
				});
			},
			mbrandBrandGet() {
				let _this = this,
					_data = Qs.stringify({
						is_hit: 0
					}),
					_resultData = localStorage.getItem('loanListData');
				_this.$http.post('/api/v1/Brand/list', _data).then(function(response) {
					if (response.data.errno === '0') {
						let _arr = {};
						response.data.result.forEach(function(item, index) {
							let array = _arr[item['first_letter']] || [];
							array.push(item);
							_arr[item['first_letter']] = array;
						});
						_this.mbrandBrand = _arr;
					} else {
						_this.$dialog.toast({
							mes: response.data.errmsg,
							timeout: 1500,
							icon: 'error'
						});
					}
				}).catch(function(error) {
					_this.$dialog.toast({
						mes: error,
						timeout: 1500,
						icon: 'error'
					});
				});
			},
			leftMenuGet() {
				let _this = this,
					_data = Qs.stringify({
						pid: 0
					});
				_this.$http.post('/api/v1/Category/list', _data).then(function(response) {
					if (response.data.errno === '0') {
						_this.menuList = response.data.result;
						_this.$nextTick(function() {
							_this.relationClick(0)
						});
					} else {
						_this.$dialog.toast({
							mes: response.data.errmsg,
							timeout: 1500,
							icon: 'error'
						});
					}
				}).catch(function(error) {
					_this.$dialog.toast({
						mes: error,
						timeout: 1500,
						icon: 'error'
					});
				});
			},
			relationClick(index, id) {
				let _this = this,
					_id = id || _this.$refs.menuRef[index].id,
					_data = Qs.stringify({
						id: _id
					});
				_this.menuIndex = index;
				_this.$http.post('/api/v1/Category/child', _data).then(function(response) {
					if (response.data.errno === '0') {
						_this.mbrandCategory = response.data.result.child;
					} else {
						_this.$dialog.toast({
							mes: response.data.errmsg,
							timeout: 1500,
							icon: 'error'
						});
					}
				}).catch(function(error) {
					_this.$dialog.toast({
						mes: error,
						timeout: 1500,
						icon: 'error'
					});
				});
			},
			clearScreen() {
				let _this = this;
				if (_this.currentIndex === 0) {
					_this.brandData = [];
					_this.$emit('clearId', {
						brandId: '',
						categoryId: _this.categoryData.join(',')
					});
				} else if (_this.currentIndex === 1) {
					_this.categoryData = [];
					_this.$emit('clearId', {
						brandId: _this.brandData.join(','),
						categoryId: ''
					});
				}
				if (_this.brandData.length === 0 && _this.categoryData.length === 0) {
					_this.screenIndex = 0;
					_this.currentIndex = 3;
				}
			},
			saveScreen() {
				let _this = this;
				_this.$emit('screenId', {
					brandId: _this.brandData.join(','),
					categoryId: _this.categoryData.join(',')
				});
				_this.screenIndex = 0;
				_this.currentIndex = 3;
			},
			//显示、隐藏筛选项
			showFilter() {
				const _this = this;
				_this.filterStatus = !_this.filterStatus;
			},

			//显示、隐藏综合排序
			showZhOrder() {
				const _this = this;
				_this.zhOrder = !_this.zhOrder;
			},

			//新品排序
			tapNewOrder() {
				const _this = this;
				_this.newOrder = !_this.newOrder;
				if(_this.newOrder){
                    _this.priceOrder = false;
                    _this.saleOrder = false;
                    this.selectSort(0,'now_at','DESC');
				}else{
                    this.selectSort();
				}

			},

			//价格排序
			tapPriceOrder() {
				const _this = this;
				_this.priceOrder = !_this.priceOrder;
				if(_this.priceOrder ){
                    _this.newOrder = false;
                    _this.saleOrder = false;
                    this.selectSort(3,'sale_price','DESC');
                }else {
                    this.selectSort(4,'sale_price','ASC');
                }


			},

			//销量排序
			tapSaleOrder() {
				const _this = this;
				_this.saleOrder = !_this.saleOrder;
				if(_this.saleOrder){
                    _this.priceOrder = false;
                    _this.newOrder = false;
                    this.selectSort(2,'sale_num','DESC');
				}else {
                    this.selectSort();
				}

			},
            Manyoutlets(){
			 let _this	=  this,
                _data = Qs.stringify({
                    id: 0
                });
                _this.$http.post('/api/v1/Multipoint/list',_data).then(function(response) {
                    if (response.data.errno === '0') {
                        _this.ManyoutletsData = response.data.result;
                        _this.cityNum  = _this.ManyoutletsData[0].num;
                        _this.$nextTick(function() {
                            _this.provinceClick(0,'0');
                        });
                    }

                }).catch(function(error) {
                    _this.$dialog.loading.close();
                });

			},

            provinceClick(index,id){
                let _this = this,
                    _id = id || _this.$refs.Province[index].province_id,
                    _data = Qs.stringify({
                        id: _id
                    });
                _this.ProvinceIndex = index;

                _this.$http.post('/api/v1/Multipoint/content',_data).then(function(response) {
                    if (response.data.errno === '0') {
                        _this.cityData = response.data.result;
                    }

                }).catch(function(error) {
                    _this.$dialog.loading.close();
                });

			},
            showMap(data){

                this.$emit('multipointId', {
                    multipointId: data.id,
                });

			}
		}
	}
</script>

<style>

</style>
