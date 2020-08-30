<?php 
	class VK {
		public $access_token;
		public $vk_user_id;

		public function __construct($vk_user_id = null, $access_token = null) {
			$this->vk_user_id 	= $vk_user_id;
			$this->access_token = $access_token;
		}


		public function getBigestImageByKey($images) {
			$image_url = null;
			array_walk($images, function($item, $key) use (&$image_url, &$image_size_a) {
				$image_size_b = (int)preg_replace('/(^photo_)/', '', $key);
				if ($image_size_a < $image_size_b) {
					$image_size_a = $image_size_b;
					$image_url = $item;
				}
			});
			return $image_url;
		}

		protected function getAllItemImages($items) {
			$images 	= [];
			foreach ($items as $item) {
				if (isset($item["attachments"]) && "photo" == $item["attachments"][0]["type"]) {
					$images[] = array_filter($item["attachments"][0]["photo"], function($item, $key) {
						return preg_match('/(^photo_)/', $key);
					}, ARRAY_FILTER_USE_BOTH);
				}
			}
			return $images;
		}

		public function getGroupInfo($domain) {
			$request_params = [
				'user_id' 		=> $this->vk_user_id,
				'fields' 		=> 'bdate',
				'v' 			=> '5.52',
				'access_token' 	=> $this->access_token,
				'domain' 		=> $domain,
				'extended'		=> 1
			];
			$groups = $this->createRequest('groups.get', $request_params);
			//echo "<pre>".$domain; print_r($groups); die();
			$info 	= array_filter($groups['response']['items'], function($item) use ($domain) {
				return $item['screen_name'] == $domain;
			});
			$info = array_values($info);
			return isset($info[0]) ? $info[0] : null;
		}

		/**
		 * Метод вернет записи со стены сообщества
		*/
		public function getWall($domain, $offset = null, $count = null) {
			$request_params = [
				'user_id' 		=> $this->vk_user_id,
				'fields' 		=> 'bdate',
				'v' 			=> '5.52',
				'access_token' 	=> $this->access_token,
				'domain' 		=> $domain
			]; 
			if ($count != null) {
				$request_params['count'] = $count;
			}
			if ($offset != null) {
				$request_params['offset'] = $offset;
			}
			$wall 		= $this->createRequest('wall.get', $request_params);
			return $wall;
		}


		protected function createRequest($method, $request_params) {
			$get_params = http_build_query($request_params);
			$response 	= json_decode(file_get_contents('https://api.vk.com/method/'.$method.'?'. $get_params), true);
			if (isset($response['error'])) {
				throw new Exception("Code: ".$response['error']["error_code"]." Message: ".$response['error']["error_msg"]);
			}
			return $response;
		}
	}
?>