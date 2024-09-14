

swiftadmin开发框架thinkphp版本号格式为年月日- 例如v20231118

swiftadmin框架分为两个版本，一个为thinkphp核心版本，目前只做单纯的BUG和漏洞修复，没有开发助手和插件中心；

另外一个版本为webman核心版本，采用cli模式运行，效率更高，适合做物联网 聊天IM应用 或者商城需要队列等使用；

如何选择；

1、如果你只是单纯的搭建一个微信公众号应用，或者其他小程序相关无并发需求，可采用thinkphp版本进行开发

2、如果你需要对接websocket IM等高并发场景，并且需要CICD、Jenkins等运维部署，建议选择webman核心版本

如有疑问可以访问swiftadmin.net或者加入QQ群68221484进行反馈！！

安全设置：

1、安装完毕后，请删除后台ceshi管理员账号

2、请更改admin.php为其他任意命名避免后台泄露

3、如果你是Linux用户，请运行根目录下的security.sh文件，【sh security.sh】