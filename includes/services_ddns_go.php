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

if($action == "getConfig") {
  // 判断服务状态
  $enable = false;
  // 判断DDNS GO服务是否已经安装
  if(checkServiceExist("ddns-go")) {
    // DDNS GO服务已经安装，判断是否运行
    $enable = checkServiceStatus("ddns-go");
  }
  // 读取配置文件中的配置
  $configFile = '/unas/apps/ddns-go/config/config.json';
  if(file_exists($configFile)) {
    $jsonString = file_get_contents($configFile);
    $configData = json_decode($jsonString, true);
    $configData['enable'] = $enable;
    echo json_encode($configData);
  } else {
    echo json_encode(array(
      'enable' => $enable,
      'configDir' => $configDir,
      'port' => 9876,
      'updateInterval' => 300,
      'comparisonInterval' => 6,
      'skipVerifyCert' => false,
      'noWeb' => false
    ));
  }
} if($action == "manage") {
  // 保存配置并启动或者停止服务
  // 是否启用ddns-go服务
  $enable = false;
  if (property_exists($jsonObj, "enable")) {
    $enable = $jsonObj->enable;
  }
  // ddns-go的配置文件目录
  $configDir = "/unas/apps/ddns-go/config";
  if (property_exists($jsonObj, 'configDir')) {
    $configDir = $jsonObj->configDir;
  }
  // ddns-go的端口，默认9876
  $port = 9876;
  if (property_exists($jsonObj, 'port')) {
    $port = $jsonObj->port;
  }
  // ddns-go的更新间隔，默认300秒
  $updateInterval = 300;
  if (property_exists($jsonObj, 'updateInterval')) {
    $updateInterval = $jsonObj->updateInterval;
  }
  // ddns-go的比较间隔，默认间隔6，即ddns-go每检查6次跟ddns服务商比对一次
  $comparisonInterval = 6;
  if (property_exists($jsonObj, 'comparisonInterval')) {
    $comparisonInterval = $jsonObj->comparisonInterval;
  }
  // ddns-go的跳过证书验证，默认false，即不跳过
  $skipVerifyCert = false;
  if (property_exists($jsonObj, 'skipVerifyCert')) {
    $skipVerifyCert = $jsonObj->skipVerifyCert;
  }
  // ddns-go的是否不启动web，默认false，即启动web
  $noWeb = $jsonObj->noWeb?: false;
  if (property_exists($jsonObj, 'noWeb')) {
    $noWeb = $jsonObj->noWeb;
  }
  $configData = array(
    'configDir' => $configDir,
    'port' => $port,
    'updateInterval' => $updateInterval,
    'comparisonInterval' => $comparisonInterval,
    'skipVerifyCert' => $skipVerifyCert,
    'noWeb' => $noWeb
  );
  // ddns-go的自定义DNS
  if (property_exists($jsonObj, 'dns')) {
    $dns = $jsonObj->dns;
    $configData['dns'] = $dns;
  }
  // 将配置换成JSON格式
  $configJson = json_encode($configData);
  // 配置文件
  $configFile = '/unas/apps/ddns-go/config/config.json';
  // if(file_exists($configFile)) {
  //   // 如果配置文件存在，和修改文件权限和所有者
  //   if (!chown($configFile, "www-data") || !chmod($configFile, "644")) {
  //     echo json_encode(array(
  //       'err' => 1,
  //       'msg' => 'Failed to change config file permissions and ownership'
  //     ));
  //     return;
  //   }
  // }
  // 将JSON数据写入文件
  $result = file_put_contents($configFile, $configJson);
  if($result == false) {
    // 配置写入文件失败
    echo json_encode(array(
      'err' => 1,
      'msg' => 'Failed to save configuration'
    ));
    return;
  }

  // ddns-go的程序文件
  $appFile = "/unas/apps/ddns-go/sbin/ddns-go";
  // 修改ddns-go的权限和所有者
  // if (!chown($appFile, "www-data") || !chmod($appFile, "755")) {
  //   echo json_encode(array(
  //     'err' => 1,
  //     'msg' => 'Failed to change app file permissions and ownership'
  //   ));
  //   return;
  // }

  // ddns-go的卸载命令
  $unInstallServiceCommand = "sudo $appFile -s uninstall";
  if($enable) {
    $skipVerifyCertStr = $skipVerifyCert ? "true" : "false";
    $noWebStr = $noWeb ? "true" : "false";
    // ddns-go的安装命令
    $startServiceCommand = "sudo $appFile -s install -l :$port -f $updateInterval -cacheTimes $comparisonInterval -c $configDir/ddns-go-config.yaml";
    if($skipVerifyCert) {
      $startServiceCommand = $startServiceCommand." -skipVerify";
    }
    if($noWeb) {
      $startServiceCommand = $startServiceCommand." -noweb";
    }
    if (isset($dns) && !empty($dns)) {
      $startServiceCommand = $startServiceCommand." -dns $dns";
    }
    error_log("安装命令为：".$startServiceCommand);

    // 判断DDNS GO服务是否已经安装
    if(checkServiceExist("ddns-go")) {
      // DDNS GO服务已经安装，则执行卸载后再安装
      error_log("service already exists, uninstalling...");
      $result = exec($unInstallServiceCommand." && ".$startServiceCommand);
      error_log("服务重新安装，结果为：".$result);
    } else {
      // DDNS GO服务未安装，则执行安装
      $result = exec($startServiceCommand);
      error_log("服务安装，结果为：".$result);
    }
  } else {
    // 判断DDNS GO服务是否已经安装
    if(checkServiceExist("ddns-go")) {
      // DDNS GO服务已经安装，则执行卸载
      $result = exec($unInstallServiceCommand);
      error_log("服务卸载，结果为：".$result);
    }
  }
  echo json_encode(array(
    'err' => 0
  ));
} if($action == "checkport") {

}
?>