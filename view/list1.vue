<template>
	<view class="">
		<view class="flex" style="">
			<view class="  p2" style="background-color: #fff;width: 100%;">
				<scroll-view scroll-y="true" class="scroll-Y">
					<uni-card>
						<template v-slot:title>
							<view>
								<uni-row>
									<uni-col :xs="9" :md="10">
										<uni-row style="margin-top:10px;">
											<uni-col :xs="10" :md="10">
												<uni-easyinput v-model="uid" placeholder="请输入用户id">
												</uni-easyinput>
											</uni-col>
											<uni-col :xs="5" :md="8"></uni-col>
											<uni-col :xs="10" :md="10" style="margin-left:10px;">
												<uni-easyinput v-model="tel" placeholder="请输入手机号">
												</uni-easyinput>
											</uni-col>
										</uni-row>
									</uni-col>
									<uni-col :xs="10" :md="4">
										<view class="button_space" style="padding-top: 10px;">
											<button @click="on_cha" type="primary" size="mini">查询</button>
											<button @click="on_cha_a" type="warn" size="mini"
												style="margin-left: 10px;">重置</button> 
										</view>
									</uni-col>
								</uni-row>
							</view>
						</template>
						<uni-table border stripe type="selection" :type="'2'" @selection-change="selectionData">
							<uni-tr>
							       <!-- 列表导航 -->
								<uni-th align="center" width="130px">管理</uni-th>
							</uni-tr>
							<uni-tr v-for="(vo,index) in list" :key="index">
							    	       <!-- 列表内容 -->
						  <!--  	<uni-td>-->
						  <!--			<view class="flex j-c">-->
								<!--		<image class="q_y" :src="vo.face" style="width: 80px;height: 80px;" mode="">-->
								<!--		</image>-->
								<!--	</view>  -->
								<!--</uni-td>-->
								<uni-td :class="vo.status==1 ? 'feng' : 'aa'">
									<view class="flex j-c">
									{{vo.status==0? '已拒绝':vo.status==1? '待审核':vo.status=='-1'? '已删除' :'正常' }}
									</view>
								</uni-td>
								<uni-td>
									<view class="flex j-c">
										<view> 
											<view class="" v-if="vo.status!=2">
												<button @click="on_tongguo(index)" type="primary" size="mini"
													style="background-color: #32CD32;">审核通过</button>

											</view>
											<view class="" v-if="vo.status==1">
												<button @click="on_refuse(index)"  type="primary"
													size="mini">拒绝通过</button>
											</view>
											<view class="">
												<button @click="on_del(index)"
													style="background-color: red;width: 88px;" type="primary"
													size="mini">删除</button>
											</view>
										</view>
									</view> 
								</uni-td>
							</uni-tr>
						</uni-table>
						<template v-slot:actions>
							<view style="width: 100%;">
								<uni-pagination @change="getPage" show-icon="true" :total="total" current="1"
									style="float: left;margin-bottom: 10px;"></uni-pagination>
							</view>
						</template>
					</uni-card>
				</scroll-view>
			</view>
		</view>

		<uni-popup ref="alertDialog" type="dialog">
			<uni-popup-beizhu cancelText="取消" confirmText="确认" lei="0" :name="name" :keya="keya" @confirm="onxiugai">
			</uni-popup-beizhu>
		</uni-popup>

	</view>
</template>

<script>
	export default {
		components: { 
		},
		data() {
			return {
				list: [],
				last_page: 0,
				total: 0,
				page: 1,
				name: '',
				keya: '',
				uid: '',
				tel: ''
			}
		},
		onLoad() {
			this.init();
		},
		methods: {
 
			on_refuse(index){
				this.id = this.list[index]['id'];
				this.index=index;
				this.name = '请输入拒绝的理由';
				this.keya = 'aa';
				this.$refs.alertDialog.open()
			},

 
			onxiugai(val) {
				var that = this;
				var that = this;
				let requestParams = {
					user_id: uni.getStorageSync('user_id'),
					token: uni.getStorageSync('token'),
					refuse_text: val,
					id: this.id,
					status:0,
				};
				this.Htpp.post('<!-- 拒绝接口 -->', 'app', {
					token: false,
					datalist: requestParams
				}).then(ret => {
					uni.showToast({
						title: ret.data.msg,
						icon: 'none'
					})
					if (ret.data.status == 200) {
						this.list[that.index]['status'] = 0;
						that.$refs.alertDialog.close()
					}
				})
		 
			},
 

			getPage(id) {

				this.page = id.current;
				this.init()
			},
			on_del(index) {
				var that = this;
				var id = this.list[index]['id'];
				uni.showModal({
					title: '提示',
					content: '确认删除吗？',
					success: function(res) {
						if (res.confirm) {
							that.ajax_del(id);
						}
					}
				});

			},
			ajax_del(id){
				var that = this;
				let requestParams = {
					user_id: uni.getStorageSync('user_id'),
					token: uni.getStorageSync('token'),
					id: id, 
				}; 
				this.Htpp.post('<!-- 删除接口 -->', 'app', {
					token: false,
					datalist: requestParams 
				}).then(ret => { 
					uni.showToast({
						title:ret.data.msg,
						icon:'none'
					})
					if (ret.data.status == 200) {
					   that.list = that.list.filter(t => t.id != id);
					}
				}) 
				
			},
			ajax_adopt(id,index){
				var that = this;
				let requestParams = {
					user_id: uni.getStorageSync('user_id'),
					token: uni.getStorageSync('token'),
					id: id, 
				}; 
				this.Htpp.post('<!-- 通过审核接口 -->', 'app', {
					token: false,
					datalist: requestParams 
				}).then(ret => { 
					uni.showToast({
						title:ret.data.msg,
						icon:'none'
					})
					if (ret.data.status == 200) {
					  that.list[index]['status'] =2;  
					}
				}) 
			},
			
			on_tongguo(index) {
					var that = this;
				var id = this.list[index]['id'];
				uni.showModal({
					title: '提示',
					content: '确认审核通过吗？',
					success: function(res) {
						if (res.confirm) {
							that.ajax_adopt(id,index);
						}
					}
				});

			},
			on_cha() {
				this.init();
			},
			on_cha_a() {
				this.uid = '';
				this.tel = '';
				this.init();
			},
			init() { 
				var that = this;
				let requestParams = {
					user_id: uni.getStorageSync('user_id'),
					token: uni.getStorageSync('token'),
					page: this.page,
					status: this.status,
				};
				this.Htpp.post('<!-- init接口 -->', 'app', {
					token: false,
					datalist: requestParams
				}).then(ret => { 
					this.list = ret.data.data.list.list;
					this.total = ret.data.data.list.total;
				}) 
			},
 
			on_tab(index) {
				this.tabindex = index;
			},
 

		}
	}
</script>

<style>
	page {
		background-color: #F2F5FF;
	}

	.feng {
		color: red;
	}
</style>
