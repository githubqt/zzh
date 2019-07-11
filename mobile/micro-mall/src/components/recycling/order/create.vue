<template>
    <yd-layout class="create-recycling-container">
        <yd-navbar slot="navbar" :title="req.id?'重新发布':'我要卖'">
            <router-link slot="left" to="/recycling">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </router-link>
            <div slot="right" @click="onSubmitForm" class="submit-form">提交</div>
        </yd-navbar>
        <div v-show="initialize.isLoaded" class="form-section">
            <yd-cell-group>
                <yd-cell-item arrow @click.native="showCategory">
                    <span slot="left" required>分类</span>
                    <yd-input slot="right" class="placeholder-right" v-model="category" readonly
                              placeholder="请选择分类"></yd-input>
                </yd-cell-item>
                <yd-cell-item arrow @click.native="showBrand">
                    <span slot="left" required>品牌</span>
                    <yd-input slot="right" class="placeholder-right" v-model="brand" readonly
                              placeholder="请选择品牌"></yd-input>
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">材质</span>
                    <yd-input slot="right" class="placeholder-right" v-model="material" placeholder="请输入材质"></yd-input>
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left" required>使用时间</span>
                    <yd-input slot="right" class="placeholder-right" v-model="havetime"
                              placeholder="请输入使用时间"></yd-input>
                </yd-cell-item>
            </yd-cell-group>
            <!-- 包体瑕疵 start -->
            <yd-cell-group title="包体瑕疵">
                <yd-cell-item>
                    <yd-checkbox-group v-model="flaw" slot="left" class="multiple-check">
                        <yd-checkbox :val="i" v-for="(item,i) in initialize.flaw" :key="i">{{item}}</yd-checkbox>
                    </yd-checkbox-group>
                </yd-cell-item>
            </yd-cell-group>
            <!-- 包体瑕疵 end -->
            <!-- 附件 start -->
            <yd-cell-group title="附件">
                <yd-cell-item>
                    <yd-checkbox-group v-model="enclosure" slot="left" class="multiple-check">
                        <yd-checkbox :val="i" v-for="(item,i) in initialize.enclosure" :key="i">{{item}}</yd-checkbox>
                    </yd-checkbox-group>
                </yd-cell-item>
            </yd-cell-group>
            <!-- 附件 end -->
            <!-- 尺寸 start -->
            <yd-cell-group>
                <yd-cell-item class="item-no-border">
                    <span slot="left" required>尺寸</span>
                    <yd-input slot="right" class="placeholder-right" v-model="size" placeholder="请输入尺寸"></yd-input>
                </yd-cell-item>
                <yd-cell-item class="item-tips">
                    <div slot="right">*包：底部长度，表：表盘直径，首饰：圈号或长度</div>
                </yd-cell-item>
            </yd-cell-group>
            <!-- 尺寸 end  -->
            <yd-cell-group title="描述内容">
                <yd-cell-item>
                    <yd-textarea slot="right" v-model="note" placeholder="请描述商品信息，如有破损请特别说明，以免影响估价的准确性（60字以内）"
                                 maxlength="60"></yd-textarea>
                </yd-cell-item>
            </yd-cell-group>
            <!-- 上传图片 start -->
            <yd-cell-group title="上传照片" class="upload-img upload-required">
                <yd-cell-item class="item-no-border upload-img-tips">
                    <div slot="left">请尽量上传清晰详细的照片，估价将更精准</div>
                </yd-cell-item>
                <yd-cell-item class="img-picker">
                    <div class="img-position" slot="left">
                        <div class="img-position-item" v-for="(item,i) in initialize.position" :key="i"
                             @click="choosePosition(item,i)">
                            <!-- <img src="http://static.ydcss.com/uploads/ydui/1.jpg" alt=""> -->
                            <croppa ref="position" class="img-croppa" :disable-drag-and-drop="true"
                                    :disable-click-to-choose="false" :disable-drag-to-move="true"
                                    :disable-scroll-to-zoom="true" :disable-pinch-to-zoom="true"
                                    :disable-rotation="true" :file-size-limit="20971520" :initial-image="item.initial"
                                    :input-attrs="{capture: showCamera}" accept="image/*"
                                    @file-size-exceed="onFileSizeExceed" @file-choose="handleCroppaFileChoose"
                                    @image-remove="handlePositionImageRemove(i)">
                                <img :src="item.cover" slot="placeholder" alt="">
                            </croppa>
                            <div class="img-position-desc">
                                {{item.name}}
                            </div>
                        </div>
                        <!-- 占位格 不要删除哟 -->
                        <div class="img-position-item item-placeholeder"></div>
                    </div>
                </yd-cell-item>
            </yd-cell-group>
            <!-- 上传图片 end  -->
            <!-- 补充图片 start -->
            <yd-cell-group title="添加更多照片" class="upload-img no-border">
                <yd-cell-item class="item-no-border upload-img-tips">
                    <div slot="left">更多磨损部位请补充图片内上传</div>
                </yd-cell-item>
                <yd-cell-item class="img-picker">
                    <div class="img-position" slot="left">
                        <div class="img-position-item" v-for="(item,i) in extrasItem" :key="i">
                            <croppa v-if="item" placeholder="NO IMAGE" ref="extras" class="img-croppa" :disabled="true"
                                    :initial-image="item.auth_url" @image-remove="handleExtrasImageRemove(i)">
                            </croppa>
                        </div>
                        <div class="img-position-item">
                            <img src="/static/imgs/plus.png" slot="placeholder" alt="" @click="chooseMultiFile">
                            <input type="file" accept="image/*" name="file" multiple class="file-hide"
                                   :capture="showCamera" ref="file" @change="handkeFileChange">
                        </div>
                        <!-- 占位格 不要删除哟 -->
                        <div class="img-position-item" style="height:1px"></div>
                    </div>
                </yd-cell-item>
            </yd-cell-group>
            <!-- 补充图片 end  -->
        </div>
    </yd-layout>
</template>
<script>
    import {
        adminLogin,
        adminLogout,
        getAdminState
    } from "../../../../tool/login";
    import Api from "../../../../tool/supplier";
    import RecyclingForm from "../../../../tool/recyclingForm";
    import {
        forEach
    } from "lodash";
    import Qs from 'qs'

    export default {
        data() {
            return {
                brand: '',
                category: '',
                num: '',
                material: '',
                havetime: '',
                flaw: [],
                enclosure: [],
                size: '',
                note: '',
                position: [],
                positionItem: {},
                positionIndex: 0,
                positionUpload: true,
                extrasItem: [],
                initialize: {
                    isLoaded: false,
                    flaw: [],
                    enclosure: [],
                    position: []
                },
                req: {
                    pid: 0
                },
                showCamera: false,
                chunkSize: 524288,
                chunks: {}
            }
        },
        created() {
            this.req = this.$route.query;
            this.editStatus();
            this.loginState = getAdminState();
            if (!this.loginState.token) {
                this.$router.replace('/recyclingLogin');
            } else {
                this.initFormData();
            }
        },
        mounted() {
        },
        methods: {
            editStatus() {
                let e = RecyclingForm.isEdit();
                if (!this.req.id && e) {
                    RecyclingForm.clearStorage();
                    RecyclingForm.clearEdit();
                }
            },
            // 文件超出size大小
            onFileSizeExceed(file) {
                this.positionUpload = false;
                this.$dialog.loading.close();
                this.$dialog.toast({
                    mes: '图片大小不能超过20MB',
                    timeout: 1500,
                    icon: 'error'
                });
            },
            // 选择图片
            handleCroppaFileChoose(file) {
                this.$nextTick(() => {
                    this.savePosition(file);
                });
            },
            /**
             * 选择品牌
             */
            showBrand() {
                this.$router.push('/recyclingBrand');
            },
            /**
             * 显示分类
             */
            showCategory() {
                this.$router.push(`/recyclingCategory?pid=${this.req.pid}`);
            },
            /**
             * 初始化表单数据
             */
            initFormData() {
                const _this = this;
                _this.$dialog.loading.open("正在加载...");
                _this.$http
                    .all([
                        _this.getOptions()
                    ])
                    .then(
                        _this.$http.spread(function (options) {
                            let o = options.data.result;
                            _this.$dialog.loading.close();
                            if (options.data.errno === '0') {
                                _this.initialize.flaw = o.flaw;
                                _this.initialize.enclosure = o.enclosure;
                                _this.initialize.isLoaded = true;
                                _this.$nextTick(() => {
                                    _this.initLocalStorage(o);
                                });
                            } else {
                                _this.$dialog.toast({
                                    mes: '服务暂不可用',
                                    timeout: 1500,
                                    icon: 'error'
                                });
                            }
                        })
                    ).catch((err) => {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({
                        mes: '服务暂不可用',
                        timeout: 1500,
                        icon: 'error'
                    });
                })
            },
            /**
             * 获取表单自定义选项
             */
            getOptions() {
                let storage = RecyclingForm.getStorage();
                let _data = Qs.stringify({
                    brand_id: storage.brand_id,
                });
                return this.$http.post(Api.options, _data);
            },
            /**
             * 初始表单输入历史
             */
            initLocalStorage(response) {
                const state = getAdminState();
                let data = RecyclingForm.getStorage();
                if (data) {
                    this.brand = data.brand_name || '';
                    this.category = data.category_name || '';
                    this.material = data.material || '';
                    this.havetime = data.havetime || '';
                    this.size = data.size || '';
                    this.note = data.note || '';
                    // 附件
                    this.flaw = data.flaw_ids || [];
                    this.enclosure = data.enclosure_ids || [];
                    // 图片处理
                    this.position = data.position || [];
                    // 处理封面图片
                    forEach(response.position, (item, index) => {
                        item.initial = '';
                        if (this.position[index]) {
                            item.initial = this.position[index].auth_url;
                            // item.initial = 'http://bpic.588ku.com/master_pic/00/09/72/05565e49ba26f6d.jpg!r650/fw/800';
                        }
                    });
                    // 处理多图
                    this.extrasItem = data.extras || [];
                    // 测试
                    // forEach(this.extrasItem,(item,i)=>{
                    //     item.auth_url = 'http://bpic.588ku.com/master_pic/00/09/72/05565e49ba26f6d.jpg!r650/fw/800';
                    // });
                }
                this.initialize.position = response.position;
            },
            /**
             * 保存位置照片
             */
            async savePosition(file) {
                const _this = this;
                _this.$dialog.loading.open('正在上传...');
                // setTimeout(() => {
                if (_this.positionUpload) {
                    //判断文件大小,进行切片
                    if (file.size >= _this.chunkSize) {
                        let data = JSON.stringify({
                            name: file.name,
                            size: file.size,
                            mime_type: file.type,
                            phase: 'start',
                            filetype: '5',
                            timestamp: Date.parse(new Date())
                        });
                        let res = await _this.chunkUpload(data);
                        if (res.status === 'success') {
                            let end_offset = res.data.end_offset;
                            const num = Math.ceil(file.size / res.data.end_offset);
                            for (let i = 0; i < num; i++) {
                                let form = new FormData();
                                form.append('phase', 'upload');
                                form.append('session_id', res.data.session_id);
                                form.append('start_offset', i * end_offset);
                                //切片
                                let blob = _this.sliceChunk(file, i * end_offset, end_offset + i * end_offset);
                                form.append('chunk', blob);
                                //上传
                                let r = await _this.chunkUpload(form);
                                if (i === num - 1) {
                                    let d = await _this.chunkUpload({
                                        'phase': 'finish',
                                        'session_id': res.data.session_id,
                                    });
                                    _this.$dialog.loading.close();
                                    if (d.status === 'success') {
                                        let $local = RecyclingForm.getStorage('position') || [];
                                        $local[_this.positionIndex] = d.data;
                                        RecyclingForm.setStorage('position', $local);
                                        _this.$dialog.toast({
                                            mes: '上传成功',
                                            icon: "success"
                                        });
                                    } else {
                                        _this.$dialog.toast({
                                            mes: '上传失败',
                                            icon: "error"
                                        });
                                    }
                                }
                            }
                        } else {
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({
                                mes: '上传失败',
                                icon: "error"
                            });
                        }
                    } else {
                        //上传图片咯
                        let formData = new FormData();
                        formData.append("file", file, "client-camera-photo.png");
                        formData.append("filetype", '5');
                        _this.upload(formData).then((d) => {
                            _this.$dialog.loading.close();
                            if (d) {
                                let $local = RecyclingForm.getStorage('position') || [];
                                $local[_this.positionIndex] = d;
                                RecyclingForm.setStorage('position', $local);
                                _this.$dialog.toast({
                                    mes: '上传成功',
                                    icon: "success"
                                });
                            } else {
                                _this.$dialog.toast({
                                    mes: '上传失败',
                                    icon: "error"
                                });
                            }
                        });
                    }
                }
                // },1000);
            },
            /**
             * 选择位置照片
             */
            choosePosition(item, index) {
                this.positionItem = item;
                this.positionIndex = index;
            },
            /**
             * 移除上传图片
             */
            handlePositionImageRemove(i) {
                let $local = RecyclingForm.getStorage('position') || [];
                if ($local[i]) {
                    $local[i] = {};
                }
                RecyclingForm.setStorage('position', $local);
            },
            /**
             * 同步上传
             */
            async upload(formData) {
                try {
                    const _this = this;
                    const d = await _this.$http({
                        url: "/file/mobile_img.php",
                        method: "POST",
                        data: formData,
                        headers: {
                            "Content-Type": "multipart/form-data"
                        }
                    });
                    if (d.data.errno === 0) {
                        return d.data.data;
                    } else {
                        throw d.data.errmsg;
                    }
                } catch (err) {
                    return false;
                }
            },
            /**
             *  切片上传
             */
            async chunkUpload(data) {
                try {
                    const _this = this;
                    let d = await _this.$http({
                        url: "/file/chunk_upload.php",
                        method: "POST",
                        data: data,
                        headers: {
                            "Content-Type": "multipart/form-data"
                        }
                    });
                    return d.data;
                } catch (err) {
                    return false;
                }
            },
            sliceChunk(file, start, end) {
                return file.slice(start, end);
            },
            /**
             * 选择更多照片
             */
            chooseMultiFile() {
                this.$refs.file.click();
            },
            /**
             * 处理上传更多照片
             */
            async handkeFileChange(e) {
                const _this = this;
                var curFiles = this.$refs.file.files;
                let hasUploaded = 0;
                let hasUnUploaded = 0;
                let uploadedLength = curFiles.length;
                if (uploadedLength > 0) {
                    _this.$dialog.loading.open('正在上传...');
                    try {
                        for (let j = 0; j < uploadedLength; j++) {
                            //判断文件大小,进行切片
                            let file = curFiles[j];
                            if (file.size >= _this.chunkSize) {
                                let data = JSON.stringify({
                                    name: file.name,
                                    size: file.size,
                                    mime_type: file.type,
                                    phase: 'start',
                                    filetype: '5',
                                    timestamp: Date.parse(new Date())
                                });
                                let res = await _this.chunkUpload(data);
                                if (res.status === 'success') {
                                    let end_offset = res.data.end_offset;
                                    const num = Math.ceil(file.size / res.data.end_offset);
                                    for (let i = 0; i < num; i++) {
                                        let form = new FormData();
                                        form.append('phase', 'upload');
                                        form.append('session_id', res.data.session_id);
                                        form.append('start_offset', i * end_offset);
                                        //切片
                                        let blob = _this.sliceChunk(file, i * end_offset, end_offset + i * end_offset);
                                        form.append('chunk', blob);
                                        //上传
                                        let r = await _this.chunkUpload(form);
                                        if (i === num - 1) {
                                            let d = await _this.chunkUpload({
                                                'phase': 'finish',
                                                'session_id': res.data.session_id,
                                            });
                                            if (d.status === 'success') {
                                                let $extras = RecyclingForm.getStorage('extras') || [];
                                                $extras.push(d.data);
                                                // d.auth_url = 'http://bpic.588ku.com/master_pic/00/09/72/05565e49ba26f6d.jpg!r650/fw/800';
                                                RecyclingForm.setStorage('extras', $extras);
                                                _this.extrasItem.push(d.data);
                                                hasUploaded++;
                                            } else {
                                                hasUnUploaded++;
                                            }
                                        }
                                    }
                                } else {
                                    hasUnUploaded++;
                                }
                            } else {
                                //上传图片咯
                                let formData = new FormData();
                                formData.append("file", curFiles[j], "client-camera-photo.png");
                                formData.append("filetype", '5');
                                let d = await _this.upload(formData);
                                if (d) {
                                    let $extras = RecyclingForm.getStorage('extras') || [];
                                    $extras.push(d);
                                    // d.auth_url = 'http://bpic.588ku.com/master_pic/00/09/72/05565e49ba26f6d.jpg!r650/fw/800';
                                    RecyclingForm.setStorage('extras', $extras);
                                    _this.extrasItem.push(d);
                                    hasUploaded++;
                                } else {
                                    hasUnUploaded++;
                                }
                            }
                        }
                        //上传完成
                        if (uploadedLength === hasUploaded + hasUnUploaded) {
                            let msg = `上传 ${hasUploaded} 张成功`;
                            if (hasUnUploaded >= 1) {
                                msg += `，${hasUnUploaded} 张失败`;
                            }
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({
                                mes: msg,
                                icon: "success",
                                timeout: 3000
                            });
                        }
                    } catch (err) {
                        _this.$dialog.toast({
                            mes: `上传出错，请重试`,
                            icon: "error",
                            timeout: 1500
                        });
                    }
                }
            },
            /**
             * 移除更多照片
             */
            handleExtrasImageRemove(i) {
                let newExtras = [];
                for (let j = 0; j < this.extrasItem.length; j++) {
                    if (this.extrasItem[j] && i != j) {
                        newExtras.push(this.extrasItem[j]);
                    }
                }
                this.extrasItem = [];
                setTimeout(() => {
                    this.extrasItem = newExtras;
                    RecyclingForm.setStorage('extras', this.extrasItem);
                }, 100);
            },
            /**
             * 提交表单
             */
            onSubmitForm() {
                const _this = this;
                try {
                    let data = RecyclingForm.getStorage();
                    if (!data.brand_id) {
                        throw "请选择品牌";
                    }
                    if (!data.category_id) {
                        throw "请选择分类";
                    }
                    if (!data.material) {
                        // throw "请输入材质";
                        data.material = '--';
                    }
                    if (!data.havetime) {
                        throw "请输入使用时间";
                    }
                    if (!data.flaw_ids || data.flaw_ids.length === 0) {
                        // throw "请选择包体瑕疵";
                    }
                    if (!data.enclosure_ids || data.enclosure_ids.length === 0) {
                        // throw "请选择附件";
                    }
                    if (!data.size) {
                        throw "请输入尺寸";
                    }
                    if (!data.note) {
                        data.note = '无';
                    }
                    forEach(this.initialize.position, (item, i) => {
                        if (!data.position || !data.position[i] || !data.position[i].url) {
                            throw `请上传${this.initialize.position[i].name}图片`;
                        }
                        data.position[i].id = this.initialize.position[i].id;
                    });
                    data.img_m = data.position;
                    data.img_s = data.extras;
                    //编辑ID
                    data.id = _this.req.id || 0;
                    _this.$dialog.loading.open("正在处理...");
                    let _data = Qs.stringify(data);
                    this.$http.post(Api.order.create, _data).then((res) => {
                        let d = res.data;
                        _this.$dialog.loading.close();
                        if (parseInt(d.errno) === 0) {
                            RecyclingForm.clearStorage();
                            _this.$dialog.toast({
                                mes: '提交成功',
                                timeout: 1500,
                                icon: 'success',
                                callback: () => {
                                    _this.$router.replace('/recyclingOrderList');
                                }
                            });
                        } else {
                            if (parseInt(d.errno) === 40015) {
                                _this.$dialog.alert({
                                    mes: '登录过期，请重新登录',
                                    callback: () => {
                                        adminLogout();
                                        _this.$router.replace('/recyclingLogin');
                                    }
                                });
                            } else {
                                throw d.errmsg;
                            }
                        }
                    }).catch((err) => {
                        _this.$dialog.loading.close();
                        _this.$dialog.toast({
                            mes: '提交失败',
                            timeout: 1500,
                            icon: 'error'
                        });
                    });
                } catch (err) {
                    this.$dialog.toast({
                        mes: err,
                        icon: "error"
                    });
                }
            }
        },
        watch: {
            material: function (val, old) {
                RecyclingForm.setStorage('material', val);
            },
            havetime: function (val, old) {
                RecyclingForm.setStorage('havetime', val);
            },
            size: function (val, old) {
                RecyclingForm.setStorage('size', val);
            },
            flaw: function (val, old) {
                RecyclingForm.setStorage('flaw_ids', val);
            },
            enclosure: function (val, old) {
                RecyclingForm.setStorage('enclosure_ids', val);
            },
            note: function (val, old) {
                RecyclingForm.setStorage('note', val);
            }
        }
    }
</script>

