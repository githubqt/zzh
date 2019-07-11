<template>
    <yd-flexbox class="category-flex-box">
        <ul class="category-brand-menu">
            <li v-for="(item, index) in menuList" :key="index">
                <a class="category-brand-list" :class="{'category-brand-active':index == nowIndex}" :id="item.id" @click="relationClick(index,item.id)" ref="menuRef">
            			{{item.name}}
            		</a>
            </li>
        </ul>
        <yd-flexbox-item class="category-brand-content">
            <div class="category-brand-banner">
                <img :src="mbrandContent.logo_url" v-lazy="mbrandContent.logo_url" :onerror="errorImg" :alt="mbrandContent.name">
            </div>
            <div v-for="(list, index) in mbrandContent.child" :key="index" :id="list.id">
                <div class="category-brand-title">
                    <span style="white-space:pre;"></span><span class="line"></span>
                    <span style="white-space:pre;"></span><span class="txt">{{list.name}}</span>
                    <span style="white-space:pre;"></span><span class="line"></span>
                </div>
                <yd-grids-group :rows="3">
                    <yd-grids-item v-for="(item, index) in list.child" :key="index" @click.native="storeGo(item.id)">
                        <div slot="else" style="text-align: center; padding: .12rem">
                            <img :src="item.logo_url" v-lazy="item.logo_url" :onerror="errorImg" class="category-content-image">
                            <span>{{item.name}}</span>
                        </div>
                    </yd-grids-item>
                </yd-grids-group>
            </div>
        </yd-flexbox-item>
    </yd-flexbox>
</template>

<script>
import Qs from "qs";
export default {
  name: "mBrand",
  data() {
    return {
      menuList: Array,
      nowIndex: 0,
      mbrandContent: {},
      errorImg: 'this.src="' + require("../../../assets/img/err.jpg") + '"'
    };
  },
  created() {
    this.leftMenuGet();
  },
  mounted() {},
  methods: {
    leftMenuGet() {
      let _this = this,
        _data = Qs.stringify({
          pid: 0
        });
      _this.$dialog.loading.open("很快加载好了");
      _this.$http
        .post("/api/v1/Category/list", _data)
        .then(function(response) {
          if (response.data.errno === "0") {
            _this.menuList = response.data.result;
            _this.$nextTick(function() {
              _this.relationClick(0);
              _this.$nextTick(function() {
                _this.$dialog.loading.close();
              });
            });
          } else {
            _this.$dialog.loading.close();
            _this.$dialog.toast({
              mes: response.data.errmsg,
              timeout: 1500,
              icon: "error"
            });
          }
        })
        .catch(function(error) {
          _this.$dialog.loading.close();
          _this.$dialog.toast({
            mes: error,
            timeout: 1500,
            icon: "error"
          });
        });
    },
    relationClick(index, id) {
      let _this = this,
        _id = id || _this.$refs.menuRef[index].id,
        _data = Qs.stringify({
          id: _id
        });
      _this.nowIndex = index;
      _this.$dialog.loading.open("很快加载好了");
      _this.$http
        .post("/api/v1/Category/child", _data)
        .then(function(response) {
          if (response.data.errno === "0") {
            _this.mbrandContent = response.data.result;
            _this.$nextTick(function() {
              _this.$dialog.loading.close();
            });
          } else {
            _this.$dialog.loading.close();
            _this.$dialog.toast({
              mes: response.data.errmsg,
              timeout: 1500,
              icon: "error"
            });
          }
        })
        .catch(function(error) {
          _this.$dialog.loading.close();
          _this.$dialog.toast({
            mes: error,
            timeout: 1500,
            icon: "error"
          });
        });
    },
    storeGo(id) {
      console.log(id);
      let _this = this,
        _data = Qs.stringify({
          category_id: id,
          page: 1,
          rows: 10
        });
      _this.$http
        .post("/api/v1/Product/list", _data)
        .then(function(response) {
          if (response.data.errno === "0") {
            if (response.data.result.total > 0) {
              _this.$router.push({
                name: "Store",
                query: {
                  category_id: id
                }
              });
            } else {
              _this.$dialog.alert({
                mes: "本分类目前无商品"
              });
            }
          } else {
            _this.$dialog.alert({
              mes: response.data.errmsg
            });
          }
        })
        .catch(function(error) {
          _this.$dialog.alert({
            mes: error
          });
        });
    }
  }
};
</script>

<style>
@import "../../../assets/css/components/brand/module/category";
</style>
