<template>
	<view class="" style="position: relative;" v-if="is_init">
		<view class="but_top" >
			<view class="flex j-b">
				<view class="flex j-c a-c" style="width: 180rpx;height: 100rpx;">
					<text>添加/编辑</text>
				</view>
				<view @click="on_close" class="flex j-c a-c" style="width: 120rpx;height: 100rpx;">
					<text>关闭</text>
				</view>
			</view>
		</view> 
		<view class="pt3 pb3" >
			<view class="flex"  >
				<view class="p2" style="width: 100%;padding-right: 60px;padding-left: 20px;">
				    输入框 
				</view>
			</view>
			
			
		</view>
			<button type="default" @click="on_save" class="sub_btn">保存</button>
		<view class="zu_but" :style="'width: ' + windowWidth +'px' ">
			<view class="flex j-f">
				<view class="flex j-c a-c" style="width: 150rpx;height: 100rpx;">
					<button type="default" @click="on_save" class="sub_btn">保存</button>
				</view>
				<view class="flex j-c a-c" style="width: 150rpx;height: 100rpx;">
					<button type="default" @click="on_close" class="sub_btn"
						style="background-color: #c0c0c0;">取消</button>
				</view>
			</view>
		</view>

	</view>
</template>

<script>
 
	import mEditor from '@/components/mEditor/mEditor.vue'
	import $http from '@/common/requestConfig';
	import popup from '@/uni_modules/uni-popup/components/uni-popup/popup.js'
	import choose from "@/common/image/choose.vue"
	import compress from "@/common/image/compress.vue"
	export default {
		components: {
			mEditor,
			choose,
			compress, 
		},
		name: "uniPopupDialog",
		mixins: [popup],
		emits: ['confirm', 'close'],
		props: { 
			windowHeight: {
				type: Number,
				default () {
					return 0;
				}
			},
			windowWidth: {
				type: Number,
				default () {
					return 0;
				}
			},
			id: {
				type: Number,
				default () {
					return 0;
				}
			},
			img_url: {
				type: String,
				default () {
					return '';
				}
			},
		},
		data() {
			return {
				find: { 
				    find变量
				},
				count: 图片数量,
				maxwh: 500,
				is_init: false,
				tab_i: 0,
				mp4: '',
				quality: 1,
				imgList: [], 
			}
		},
		mounted() {
		 	this.is_post=false;
			if (this.id > 0) {
				this.init();
			} else {
				this.is_init = true;
			}
			// this.goods_nav();
		}, 
		methods: { 
		     方法变量
		    //查询详情
			init() {
				var that = this;
				let requestParams = {
					id: this.id,
				};
				this.Htpp.post('插件别名.插件控制器/banmi_finds_views', 'app', {
					token: false,
					datalist: requestParams
				}).then(ret => {
					this.img_url=ret.data.img_url;
					this.url=ret.data.url;
					if (ret.data.status == 200) {
						this.find = ret.data.data.find;
						init图片
						
						
						
				// //单图
				// 		this.imgList = [ret.data.img_url + this.find.变量]
				// //多图		
				// 		var img_list = ret.data.data.find.变量
				// 		var jsonObject = JSON.parse(img_list);
				// 		var imgs = '';
				// 		for (var i = 0; i < jsonObject.length; i++) {
				// 			jsonObject[i]['pic'] = ret.data.img_url + jsonObject[i]['pic']
				// 			imgs += jsonObject[i]['pic'] + ',';
				// 		}
				// 		imgs = imgs.substring(0, imgs.lastIndexOf(','));
				// 		var splitAdd = imgs.split(",");
				// 		this.imgList = splitAdd;
						
						this.is_init = true;

					}
				})
			},
			on_save() {
				验证条件 
		     	if (this.is_post) {
					 return
				}
				uni.showLoading({
					title: '正在提交中...'
				})
				this.is_post = true;
				提交验证
			}, 
			on_post() {
				var that = this; 
				if (this.img_list) {
					var arr = this.img_list1.concat(this.img_list);
				} else {
					var arr = this.img_list1;
				} 
				var img_list = new Array();
				for (var i = 0; i < arr.length; i++) {
					var pic = arr[i]['url'].replace(this.img_url, "");
					var map = {
						pic: pic,
					};
					img_list.push(map);
				}
				
				
				let requestParams = { 
					提交变量
					id: this.id,
					img_list: JSON.stringify(img_list)//多图

				};
				this.Htpp.post('插件别名.插件控制器/banmi_adds_add', 'app', {
					token: true,
					datalist: requestParams
				}).then(ret => {
					uni.showToast({
						title: ret.data.msg,
						icon: 'none'
					})
					this.is_post=false;
					if (ret.data.status == 200) {
						that.$emit('img_ok')
						that.popup.close()
					}
				})
			},
			on_Upload() {
				var that = this;
				var img_list = new Array();
				var img_list1 = new Array();
				for (var i = 0; i < this.imgList.length; i++) {
					var is = this.imgList[i].indexOf('attachment/') != -1
					if (!is) { //如果不包含attachment/ 证明需要上传
						var map = {
							path: this.imgList[i],
							id: i,
						};
						img_list.push(map);
					} else {
						var map1 = {
							path: this.imgList[i],
							url: this.imgList[i],
							id: i,
						};
						img_list1.push(map1);
					}
				}
				this.img_list1 = img_list1;
				if (img_list.length == 0) {
					that.on_post();
					return
				}
				$http.qnFileUpload({
					files: img_list, // 必填 临时文件路径 格式: [{path: "图片地址"}]
					load: true,
					maxSize: 300000,
					typeo: '',//local本地 为空用户设置
					onEachUpdate: res => {},
					onProgressUpdate: res => {}
				}).then(res => {
					that.img_list = res;
					图片值
					that.on_post();
				});
			},
			fileChange(e, index, videow, videoh) {
				this.imgList = e; 
			},
			on_close() {
				this.popup.close()
			}, 
			
			
		}
	}
</script>

<style>
     .but_top{
         background-color: #F8F8F8;width: 100%;height: 100rpx; top: 0;
            left: 0;
            z-index: 999;
            position: sticky
     }
	.zu_but {
		position: fixed;
		height: 100rpx;
		z-index: 999;
		background-color: #F8F8F8;
		bottom: 228rpx;
	}

	.option_box {
		width: 35rpx;
		height: 35rpx;
		border: 1rpx solid #999999;
		border-radius: 5px;
		margin-right: 10rpx;
		// background-color: #FF852A;
		display: flex;
		justify-content: center;
		align-items: center;

		image {
			width: 20rpx;
			height: 20rpx;
		}
	}

	.option_box_active {
		background: linear-gradient(-30deg, #ff7029 0%, #faa307 100%);
		border: 1rpx solid #faa307 !important;
	}

	.sub_btn_add {
		margin-left: 30rpx;
		width: 150rpx;
		height: 60rpx;
		background: linear-gradient(-30deg, #A9A9A9 0%, #A9A9A9 100%);
		border-radius: 44rpx;

		font-size: 30rpx;
		font-weight: 700;
		color: #ffffff;

		line-height: 60rpx;
	}

	.sub_btn {

		height: 70rpx;
		background: #0071FE;
		border-radius: 10rpx;
		font-size: 30rpx;
		color: #ffffff;
		line-height: 70rpx;
	}
    .bm_Inputbox{
		width: 600px;
		padding: 10px 0 10px 0;
		font-size: 14px;
	}
	.bm_Inputbox_name{
		width: 150px;
	}
	button::after {
		border: none;
	}
</style>
