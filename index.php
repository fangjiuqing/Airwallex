<?php
require_once 'vendor/autoload.php';
use Qeebey\Airwallex;

/***************************************************************************
 *	    																   *
 *                     													   *
 * 					                               						   *
 *                                              						   *
 ***************************************************************************/
$file_path = 'license.png';
$curlFile = new \CURLFile(realpath($file_path));
//$curlFile->setMimeType('image/pdf');
var_dump($curlFile->getMimeType());die;


$airwallex = new Airwallex();

$auth = $airwallex->authentication_login(); //output: eyJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI2MGE3YmQ2Ni0yNjY0LTQxYTctOWFhMS0zNmM1MWU0MzUyZTUiLCJzdWIiOiIxZTJjYThhNi0zMTRkLTRhZDEtYjJiNi1jYjI5MzFhNzRiNmEiLCJpYXQiOjE1NTM1MjQzMjUsImV4cCI6MTU1MzUyNjEyNX0.2rOAnUvoB3OQEVe8sGjASoqXXKPdzFAFNwT0USC9HMY

if ( $auth['code'] == 201 ) {
	$token = $auth['data']['token'];
	$airwallex->set_token($token);
	//p($token);die;
	// ## 当前账户
	// $balance = $airwallex->balances_current();
	// json($balance);

	// ## 账户历史
	// $history = $airwallex->balances_history(['currency' => 'USD']);
	// json($history);
	
	## 文件上传
	$file = 'license.png';
	$upload = $airwallex->files_upload($file , '测试license文件上传');
	json($upload);
	echo 897;
	// output 
	// {
	//     "created": 1553581921,
	//     "file_id": "MWUyY2E4YTYtMzE0ZC00YWQxLWIyYjYtY2IyOTMxYTc0YjZhLHwsaG9uZ2tvbmcsfCxsaWNlbnNlLmpwZ18xNTUzNTgxOTIx",
	//     "filename": "license.jpg",
	//     "object_type": "file",
	//     "size": 176927
	// }
}else{
	p($auth);
}


function p($data) {
	echo '<pre>';
	print_r($data);
}

function json ($data) {
	header('Content-Type:text/json');
	echo json_encode($data);
}