import Vue from 'vue'
import Router from 'vue-router'

import Machine from '@/components/machine/log'

import Home from '@/components/home/main'
import SnapUp from '@/components/home/snapUp'
import GroupUp from '@/components/home/groupUp'
import GroupDetails from '@/components/home/groupDetails'
import GroupPrivDetails from '@/components/home/groupPrivDetails'
import Bidding from '@/components/home/bidding'
import Product from '@/components/home/product'
import Record from '@/components/home/record'
import Copywriter from '@/components/home/copywriter'
import Marginorder from '@/components/home/marginorder'


import Brand from '@/components/brand/main'

import Store from '@/components/store/main'
import Details from '@/components/store/details'
import newDetail from '@/components/store/newDetail'
import Order from '@/components/store/order'
import GroupOrder from '@/components/store/groupOrder'
import Cart from '@/components/store/module/cart'
import Success from '@/common/success'
import OrderInfo from '@/components/store/module/orderInfo'
import Promote from '@/components/store/promote'
import Tencentmap from '@/components/store/tencentmap'


import Pawn from '@/components/pawn/main'
import HousingLoan from '@/components/pawn/housingLoan'
import CarLoan from '@/components/pawn/carLoan'
import CivilLoan from '@/components/pawn/civilLoan'


import User from '@/components/user/main'
import UserInfo from '@/components/user/info'
import Site from '@/components/user/module/site'
import Create from '@/components/user/module/create'
import Collect from '@/components/user/module/collect'
import About from '@/components/user/about'
import Contact from '@/components/user/contact'
import Suggestions from '@/components/user/suggestions'
import OrderList from '@/components/user/orderList'
import AuctionList from '@/components/user/auctionList'
import MyCoupons from '@/components/user/myCoupons'
import Coupons from '@/components/user/coupons' // 新版优惠券
import CouponsCommodity from '@/components/user/coupon/commodity' // 商品优惠券详情
import CouponsShop from '@/components/user/coupon/shop' // 店铺优惠券详情
import AddMobile from '@/components/user/addMobile'
import CouponsCenter from '@/components/user/couponsCenter'
import CourierInfo from '@/components/user/module/courierInfo'
import AfterSale from '@/components/user/module/afterSale'
import UorderInfo from '@/components/user/module/orderInfo'
import Complainthome from '@/components/user/complainthome'
import Suggest from '@/components/user/suggest'
import SgSucceed from '@/components/user/sgSucceed'



import LoginV2 from '@/components/login/main'
import Login from '@/components/login/login'
import Register from '@/components/login/register'
import Find from '@/components/login/find'
import Writer from '@/components/login/writer'
import G from '@/main.js'

import Invitation from '@/components/invitation/main'
import HelpRegister from '@/components/invitation/helpRegister'
import Delivery from '@/components/delivery/main'
import InviteList from '@/components/user/invite/list'
import InviteDetail from '@/components/user/invite/detail'


/** 
 * 商户回收模块  
 */
// 主页
import Recycling from '@/components/recycling/recycling';
// 登录
import RecyclingLogin from '@/components/recycling/auth/login';
// 创建回收订单
import CreateRecyclingOrder from '@/components/recycling/order/create';
// 选择品牌页面
import RecyclingBrand from '@/components/recycling/order/brand';
// 选择分类页面
import RecyclingCategory from '@/components/recycling/order/category';
import RecyclingCategoryThree from '@/components/recycling/order/categoryThree';
// 回收订单列表
import RecyclingOrderList from '@/components/recycling/order/list';
// 回收订单详细
import RecyclingOrderDetail from '@/components/recycling/order/detail';

// 播放视频
import Play from '@/common/play';




Vue.use(Router)



const WebPath = {
    getRootPath: function() {
        //获取主机地址之后的目录，如： uimcardprj/share/meun.jsp
        var pathName = window.document.location.pathname;
        var pathURL = window.location.host;
        var n = (pathURL.split('.')).length - 1;
        //获取带"/"的项目名，如：/uimcardprj
        var projectName = pathName.substring(0, pathName.substr(1).indexOf('/') + 1);
        if (projectName != "" && n < 3) {
            return (projectName) + "/";
        } else {
            return "/mobile/";
        }
    }
}

export default new Router({
    mode: 'history',
    base: WebPath.getRootPath(),
    scrollBehavior(to, from, savedPosition) {
        //分享app url
        postShareUrlToMiniMessage();
        if (savedPosition) {
            return savedPosition;
        } else {
            return {
                x: 0,
                y: 0
            }
        }
    },
    routes: [
        {
            path: '/',
            name: 'Machine',
            component: Machine
        },
        // {
        //     path: '/',
        //     name: 'Home',
        //     component: Home
        // },
        // {
        //     path: '/brand',
        //     name: 'Brand',
        //     component: Brand
        // },
        // {
        //     path: '/store',
        //     name: 'Store',
        //     component: Store
        // },
        // {
        //     path: '/pawn',
        //     name: 'Pawn',
        //     component: Pawn
        // },
        // {
        //     path: '/user',
        //     name: 'User',
        //     component: User
        // },
        // {
        //     path: '/login',
        //     name: 'Login',
        //     component: Login
        // },
        // {
        //     path: '/find',
        //     name: 'Find',
        //     component: Find
        // },
        // {
        //     path: '/register',
        //     name: 'Register',
        //     component: Register
        // },
        // {
        //     path: '/site',
        //     name: 'Site',
        //     component: Site
        // },
        // {
        //     path: '/create',
        //     name: 'Create',
        //     component: Create
        // },
        // {
        //     path: '/collect',
        //     name: 'Collect',
        //     component: Collect
        // },
        // {
        //     path: '/contact',
        //     name: 'Contact',
        //     component: Contact
        // },
        // {
        //     path: '/about',
        //     name: 'About',
        //     component: About
        // },
        // {
        //     path: '/suggestions',
        //     name: 'Suggestions',
        //     component: Suggestions
        // },
        // {
        //     path: '/myCoupons',
        //     name: 'MyCoupons',
        //     component: MyCoupons
        // },
        // {
        //     path: '/coupons',
        //     name: 'Coupons',
        //     component: Coupons
        // },
        // {
        //     path: '/couponCommodity',
        //     name: 'CouponsCommodity',
        //     component: CouponsCommodity
        // },
        // {
        //     path: '/couponShop',
        //     name: 'CouponsShop',
        //     component: CouponsShop
        // },
        // {
        //     path: '/addMobile',
        //     name: 'AddMobile',
        //     component: AddMobile
        // },
        // {
        //     path: '/couponsCenter',
        //     name: 'CouponsCenter',
        //     component: CouponsCenter
        // },
        // {
        //     path: '/housingLoan',
        //     name: 'HousingLoan',
        //     component: HousingLoan
        // },
        // {
        //     path: '/carLoan',
        //     name: 'CarLoan',
        //     component: CarLoan
        // },
        // {
        //     path: '/civilLoan',
        //     name: 'CivilLoan',
        //     component: CivilLoan
        // },
        // {
        //     path: '/details',
        //     name: 'Details',
        //     component: Details
        // },
        // {
        //     path: '/newDetail',
        //     name: 'newDetail',
        //     component: newDetail
        // },
        // {
        //     path: '/snapUp',
        //     name: 'SnapUp',
        //     component: SnapUp
        // },
        // {
        //     path: '/groupUp',
        //     name: 'GroupUp',
        //     component: GroupUp
        // },
        // {
        //     path: '/groupDetails',
        //     name: 'GroupDetails',
        //     component: GroupDetails
        // },
        // {
        //     path: '/groupPrivDetails',
        //     name: 'GroupPrivDetails',
        //     component: GroupPrivDetails
        // },
        // {
        //     path: '/bidding',
        //     name: 'Bidding',
        //     component: Bidding
        // },
        // {
        //     path: '/product',
        //     name: 'Product',
        //     component: Product
        // },
        // {
        //     path: '/record',
        //     name: 'Record',
        //     component: Record
        // },
        // {
        //     path: '/copywriter',
        //     name: 'Copywriter',
        //     component: Copywriter
        // },
        // {
        //     path: '/marginorder',
        //     name: 'Marginorder',
        //     component: Marginorder
        // },
        // {
        //     path: '/order',
        //     name: 'Order',
        //     component: Order
        // },
        // {
        //     path: '/groupOrder',
        //     name: 'GroupOrder',
        //     component: GroupOrder
        // },
        // {
        //     path: '/orderList',
        //     name: 'OrderList',
        //     component: OrderList
        // },
        // {
        //     path: '/auctionList',
        //     name: 'AuctionList',
        //     component: AuctionList
        // },
        // {
        //     path: '/courierInfo',
        //     name: 'CourierInfo',
        //     component: CourierInfo
        // },
        // {
        //     path: '/afterSale',
        //     name: 'AfterSale',
        //     component: AfterSale
        // },
        // {
        //     path: '/cart',
        //     name: 'Cart',
        //     component: Cart
        // },
        // {
        //     path: '/userinfo',
        //     name: 'UserInfo',
        //     component: UserInfo
        // },
        // {
        //     path: '/success',
        //     name: 'Success',
        //     component: Success
        // },
        // {
        //     path: '/orderInfo',
        //     name: 'OrderInfo',
        //     component: OrderInfo
        // },
        // {
        //     path: '/uOrderInfo',
        //     name: 'UorderInfo',
        //     component: UorderInfo
        // },
        // {
        //     path: '/helpRegister',
        //     name: 'HelpRegister',
        //     component: HelpRegister
        // },
        // {
        //     path: '/invitation',
        //     name: 'Invitation',
        //     component: Invitation
        // },
        // {
        //     path: '/delivery',
        //     name: 'Delivery',
        //     component: Delivery
        // },
        // {
        //     path: '/writer',
        //     name: 'Writer',
        //     component: Writer
        // },
        // {
        //     path: '/complainthome',
        //     name: 'Complainthome',
        //     component: Complainthome
        // },
        // {
        //     path: '/suggest',
        //     name: 'Suggest',
        //     component: Suggest
        // },
        // {
        //     path: '/sgSucceed',
        //     name: 'SgSucceed',
        //     component: SgSucceed
        // },
        // {
        //     path: '/promote',
        //     name: 'Promote',
        //     component: Promote
        // },
        // {
        //     path: '/tencentmap',
        //     name: 'Tencentmap',
        //     component: Tencentmap
        // },
        // {
        //     path: '/inviteList',
        //     name: 'InviteList',
        //     component: InviteList
        // },
        // {
        //     path: '/inviteDetail',
        //     name: 'InviteDetail',
        //     component: InviteDetail
        // }, {
        //     path: '/recycling',
        //     name: 'Recycling',
        //     component: Recycling
        // }, {
        //     path: '/recyclingLogin',
        //     name: 'RecyclingLogin',
        //     component: RecyclingLogin
        // },
        // {
        //     path: '/recyclingOrderList',
        //     name: 'RecyclingOrderList',
        //     component: RecyclingOrderList
        // }, {
        //     path: '/recyclingOrderDetail',
        //     name: 'RecyclingOrderDetail',
        //     component: RecyclingOrderDetail
        // }, {
        //     path: '/createRecycling',
        //     name: 'CreateRecyclingOrder',
        //     component: CreateRecyclingOrder
        // }, {
        //     path: '/recyclingBrand',
        //     name: 'RecyclingBrand',
        //     component: RecyclingBrand
        // }, {
        //     path: '/recyclingCategory',
        //     name: 'RecyclingCategory',
        //     component: RecyclingCategory
        // }, {
        //     path: '/recyclingCategoryThree',
        //     name: 'RecyclingCategoryThree',
        //     component: RecyclingCategoryThree
        // },
        // {
        //     path: '/loginV2',
        //     name: 'LoginV2',
        //     component: LoginV2
        // },
        // {
        //     path: '/play',
        //     name: 'Play',
        //     component: Play
        // },
    ]
})