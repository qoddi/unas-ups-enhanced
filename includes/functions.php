<?php

function checkServiceExist($serviceName) {
  // 检查服务是否存在的命令
  $checkServiceExistCommand = "systemctl status $serviceName > /dev/null 2>&1";
  // 执行命令检查服务是否存在
  exec($checkServiceExistCommand, $output, $returnVar);
  return $returnVar == 0;
}

function checkServiceStatus($serviceName) {
  // 检查服务是否存在的命令
  $checkServiceRunningCommand = "systemctl status $serviceName | grep 'running'";
  // 执行命令检查服务是否运行
  exec($checkServiceRunningCommand, $output, $returnVar);
  return $returnVar == 0;
}
?>