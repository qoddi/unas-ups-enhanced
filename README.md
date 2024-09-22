# unas-ups-enhanced
适用于万由UANS的UPS增强程序，通过webui调整acpupsd配置文件实现网络UPS的支持



## 使用方法
将项目中所有文件放置到`/unas/apps/ups-enhanced`目录下，然后登录UNAS的web端，打开控制中心，即可找到应用
不论是客户端还是服务端使用前均需要安装unas官方ups应用
对于接了usb ups的机器，在页面选择服务端并点击应用即可，服务端应用后默认监听3551端口，可以telnet验证
对于未接usb ups的机器，在页面选择客户端，并输入服务端ip地址，端口3551不变，之后点击应用，然后可以前往unas ups应用查看是否连接成功




