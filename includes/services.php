<?php
include 'functions.php';

// 获取请求参数
$jsonData = file_get_contents("php://input");
// 解析JSON数据
$jsonObj = json_decode($jsonData);
// 现在可以使用$jsonObj访问传递的JSON数据中的属性或方法
// 获取token，通过token获取用户名
$token = $jsonObj->token;
if(empty($token)) {
  echo json_encode(array(
    'err' => 1,
    'msg' => 'Token is empty'
  ));
  return;
}
session_id($token);
// 强制禁止浏览器的隐式cookie中的sessionId
$_COOKIE = [ 'PHPSESSID' => '' ];
session_start([ // php7
    'cookie_lifetime' => 2000000000,
    'read_and_close'  => false,
]);
// 获取用户名
$userId = isset($_SESSION['uid']) && is_string($_SESSION['uid']) ? $_SESSION['uid'] : $_SESSION['username'];
if(!isset($userId)) {
  echo json_encode(array(
    'err' => 1,
    'msg' => 'User information not obtained'
  ));
  return;
}
// 获取要进行的操作
$action = $jsonObj->action;

//检查ups服务运行情况方法
if($action == "checkUpsServiceStatus") {
     // 判断服务状态
    $enable = false;
    if(checkServiceExist("apcupsd")) {
        $enable = checkServiceStatus("apcupsd");
    }




    echo json_encode(array( 
        'status' =>$enable
    ));
}


//从配置文件中读取信息方法
if($action == "getConfigParam") {
  $configFile = '/etc/apcupsd/apcupsd.conf';
  $search = 'UPSCABLE ether';
  $currentWorkMode = 1;
  $currentServer = '0.0.0.0';
  $content = file_get_contents($configFile);
  $pattern = '/^' . preg_quote($search, '/') . '.*/m';
  //匹配到了则说明是客户端模式，否则是服务端模式
  if(preg_match($pattern,$content)){
    $currentWorkMode = 2;
    $search = 'DEVICE';
    $pattern = '/^' . preg_quote($search, '/') . '.*/m';
    if(preg_match($pattern,$content,$matches)) {
      $currentServer = $matches[0];
    }
  }

  echo json_encode(array( 
    'status' =>true,
    'currentWorkMode' =>$currentWorkMode,
    'currentServer' =>$currentServer
));
   
    //if(preg_match($pattern,$content,$matches)){
     
    
    
  



}





//重启unas ups服务方法
if($action == "restartUpsService") {
  $result = false;

  // 服务已经安装，重启服务
  $restartServiceCommand = "sudo systemctl restart apcupsd";
  exec($restartServiceCommand, $output, $returnVar);
 echo json_encode(array( 
  'status' =>$returnVar == 0,
  'output' =>$output,
));
}

//设置工作模式方法
if($action == "setWorkMode") {
  $configFile = '/etc/apcupsd/apcupsd.conf';

  //获取工作模式 1--服务器模式 2--客户端模式
  $mode = $jsonObj->mode;
  $serverAddr = $jsonObj->serverAddr;


  if(!file_exists($configFile)) {
    echo json_encode(array( 
      'status' =>false,
      'msg' => 'config file not exist'
  ));
  }else{
    exec("sudo chown www-data:www-data $configFile");
  }
  $content = file_get_contents($configFile);
  
  if($mode == 1){
    //服务器模式
    $search = 'UPSCABLE';
    $replace = 'UPSCABLE usb';
    $pattern = '/^' . preg_quote($search, '/') . '.*/m';
    $content = preg_replace($pattern, $replace, $content, 1);

    $search = 'UPSTYPE';
    $replace = 'UPSTYPE usb';
    $pattern = '/^' . preg_quote($search, '/') . '.*/m';
    $content = preg_replace($pattern, $replace, $content, 1);

    $search = 'DEVICE';
    $replace = 'DEVICE ';
    $pattern = '/^' . preg_quote($search, '/') . '.*/m';
    $content = preg_replace($pattern, $replace, $content, 1);

    $search = 'NISIP';
    $replace = 'NISIP 0.0.0.0';
    $pattern = '/^' . preg_quote($search, '/') . '.*/m';
    $content = preg_replace($pattern, $replace, $content, 1);

  }else if($mode ==2){
    //客户端模式
    $search = 'UPSCABLE';
    $replace = 'UPSCABLE ether';
    $pattern = '/^' . preg_quote($search, '/') . '.*/m';
    $content = preg_replace($pattern, $replace, $content, 1);

    $search = 'UPSTYPE';
    $replace = 'UPSTYPE net';
    $pattern = '/^' . preg_quote($search, '/') . '.*/m';
    $content = preg_replace($pattern, $replace, $content, 1);

    $search = 'DEVICE';
    $replace = 'DEVICE'.' '.$serverAddr;
    $pattern = '/^' . preg_quote($search, '/') . '.*/m';
    $content = preg_replace($pattern, $replace, $content, 1);

    $search = 'NISIP';
    $replace = 'NISIP 127.0.0.1';
    $pattern = '/^' . preg_quote($search, '/') . '.*/m';
    $content = preg_replace($pattern, $replace, $content, 1);

  }

 


   
  // 写回文件
  $result =file_put_contents($configFile, $content);
  if($result == false) {
    // 配置写入文件失败
    echo json_encode(array(
      'status' =>false,
      'err' => 1,
      'msg' => 'Failed to save'.$result
    ));
  }else{
    echo json_encode(array( 
      'status' =>true,
      'msg' => 'change success',
     // 'data' => $content
  ));
  }
 
  


}




 

?>