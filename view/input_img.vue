					<view class="flex  pb2">
						<view class="flex">
							<view class="pb2 c_7 f18 f28 " style="width: 150px;">
								名称
							</view>
							<view>
								<choose :count="count" :imgList="imgList" :lei='3' :tab_i="tab_i" :mp4="mp4"
									@changes="fileChange">
								</choose>
								<compress ref="compress" :quality="quality"> </compress>
							</view>
						</view>
					</view>