<?php
/**
 * Airwallex API接口封装
 * @author    Andy <tech-agent2@ipasspay.com>
 * @copyright Ipasspay Inc
 */
namespace Qeebey;

class Airwallex {
	/**
	 * [$client_id string 客户端ID]
	 * @access private
	 * @var string
	 */
	private $client_id    = 'HiyopjFNStGytsspMadLag';

	/**
	 * [$api_key API KEY]
	 * @access private
	 * @var string
	 */
	private $api_key      = '26599d4e9cba3c6e01ceb65fc52a71e3a9287a2df99efadbf8e0c3dd2e205e76cfb5792be69b3f5eeded10b8cb35da13';
	
	/**
	 * [$api_url 接口地址]
	 * @access private
	 * @var string
	 */
	private $api_uri      = 'https://api-demo.airwallex.com/api/v1/';

	/**
	 * [$file_api_url description]
	 * @var string
	 */
	private $file_api_uri = 'https://files-demo.airwallex.com/api/v1/'; 

	/**
	 * [$header 请求头]
	 * @access private
	 * @var array
	 */
	private $header       = [];


	/**
	 * [__construct 架构函数]
	 * 允许client_id api_key api_url 传入
	 * @param array $cfg [description]
	 */
	public function __construct ($cfg = [] ) {
		if ( isset($cfg['client_id']) ) {
			$this->client_id    =    $cfg['client_id'];
		}

		if ( isset($cfg['api_key']) ) {
			$this->api_key      =    $cfg['api_key'];
		}

		if ( isset($cfg['api_url']) ) {
			$this->api_uri      =    $cfg['api_url'];
		}

		if ( isset($cfg['file_api_url']) ) {
			$this->file_api_url =    $cfg['file_api_url'];
		}

		if ( !$this->client_id || !$this->api_key ) {
			throw new \Exception("client_id and api_key are required", 1);
		}

		$this->header = [
			"Content-Type:text/json",
			"Charset:utf8",
			"referer:$this->api_uri",
			"user-agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36"
		];
	}

	/**
	 * 授权，获取token
	 * @access public
	 * @method POST
	 * @param  array  $params optional
	 * @return JSON   $token
	 */
	public function authentication_login ( $params = [] ) {
		$uri    =  $this->api_uri . 'authentication/login';
		$header =  [
			"x-api-key: $this->api_key",
			"x-client-id: $this->client_id"
		];
		return $this->http_request($uri , 'POST' , array_merge($header , $this->header) );
	}

	/**
	 * 设置token
	 * @access public
	 * @method HEAD
	 * @param  string $token required
	 * @return object        current object
	 */
	public function set_token( $token = '' ) {
		try {
			if ( '' === $token ) {
				throw new \Exception('token is required', 1);
			}
			$this->header[] =  "Authorization:Bearer " . $token;
			return $this;
		} catch ( \Exception $e ) {
			return $this->return(0,$e->getMessage());
		}
	}

	/**
	 * 设置作为关联账户，后续操作将以此为主体
	 * @access public
	 * @method HEAD
	 * @param  string $account_id 账号ID required
	 * @return object             current object
	 */
	public function as_connected_account( $account_id = '' ) {
		try {
			if ( '' === $account_id ) {
				throw new \Exception('Connected account id is required', 1);
			}
			$this->header[] =  "x-on-behalf-of:" . $account_id;
			return $this;
		} catch ( \Exception $e ) {
			return $this->return(0,$e->getMessage());
		}
	}

	/**
	 * 获取当前账户余额
	 * @access public
	 * @method GET
	 * @param  array  $params 查询参数，optional
	 * @return json           [description]
	 */
	public function balances_current ( $params = [] ) {
		$uri    =    $this->api_uri . 'balances/current';
		return $this->http_request($uri , 'GET' , $this->header , $params);
	}

	/**
	 * [balances_history 获取账户余额历史记录]
	 *  @param  $params array 支持的键值和含义如下
	 *              currency       可选 string  三位字符货币编码,如USD
     *              from_post_at   可选 string  开始时间，ISO8601 format，如2017-04-01T03:52:34+0000
     *              page_num       可选 integer 页数，0-10000，如3
     *              page_size      可选 integer 每页条数，0-2000，默认100
     *              request_id     可选 string  request_id from clients for the transaction
     *              to_post_at     可选 string  结束时间，ISO8601 format，如2017-04-01T03:52:34+0000
	 * @param  array  $params [description]
	 * @return [type]         [description]
	 */
	public function balances_history ( $params = [] ) {
		$uri    =    $this->api_uri . 'balances/history';
		return $this->http_request($uri , 'GET' , $this->header , $params);
	}

	/**
	 * [files_upload 文件上传]
	 * @method  POST
	 * @param  [type] $file_path [文件路径]
	 * @return [type]            [description]
	 *  output 
	 * {
	 *     "created": 1553581921,
	 *     "file_id": "MWUyY2E4YTYtMzE0ZC00YWQxLWIyYjYtY2IyOTMxYTc0YjZhLHwsaG9uZ2tvbmcsfCxsaWNlbnNlLmpwZ18xNTUzNTgxOTIx",
	 *     "filename": "license.jpg",
	 *     "object_type": "file",
	 *     "size": 176927
	 * }
	 */
	public function files_upload ( $file_path , $note = 'file note' ) {
		try {
			$uri    =    $this->file_api_uri . 'files/upload';
			if ( !is_file($file_path) ) {
				throw new \Exception('File not exists:' . $file_path, 1); 
			}

			if ( !class_exists('\CURLFile') ) {
				throw new \Exception('CURLFile class required', 1); 
			}

			$this->header[] = "Content-Type: multipart/form-data";
			$mime_info = mime_content_type($file_path);
			$data['file'] = new \CURLFile(realpath($file_path) , $mime_info , 'file');
			$data['note'] = $note;
			return $this->http_request($uri,'POST' , $this->header , $data);
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * [http_request description]
	 * @param  string $url    [description]
	 * @param  string $method [description]
	 * @param  array  $header [description]
	 * @param  array  $data   [description]
	 * @return [type]         [description]
	 */
	public function http_request ( $url = '' , $method = 'GET' , $header = [], $data = [] ) {
		try {
			$ch = curl_init();
		    
		    ## POST数据
		    if ( $method == 'POST' ) {
		    	curl_setopt($ch, CURLOPT_POST, 1);

		    	if ( !empty($data) ) {
		    		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		    	}
		    }

		    ## GET带参数请求
		    if ( $method == 'GET' ) {
		    	if ( !empty($data) ) {
		    		$url .= '?' . http_build_query($data);
		    	}
		    }

		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

		    if( $error = curl_error($ch) ){
		        throw new \Exception($error, 1); 
		    }

		    $output = trim(curl_exec($ch));
	        $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	        curl_close($ch);
	        return $this->return($rescode,'request completed' , $output);
	    } catch ( \Exception $e ) {
	    	return $this->return(0,$e->getMessage());
	    }
	}

	/**
	 * [return description]
	 * @param  integer $code [description]
	 * @param  string  $msg  [description]
	 * @param  array   $data [description]
	 * @return [type]        [description]
	 */
	private function return($code = 200 , $msg = 'ok' , $data = [] ) {
		## 包装输出数据
        $output = [
        	'code' => $code,
        	'msg'  => $msg
        ];

        ## 如果传入的是JSON数据 解析成数组
        $output_josn =  json_decode($data , true);
 		if ( json_last_error() == JSON_ERROR_NONE ) {
 			$output['data'] = $output_josn;
 		}else{
 			$output['data'] = $data;
 		}
 		return $output;
	}
} //Class End
