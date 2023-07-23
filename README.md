# 兼容likeadmin用户的webman中间件

## 目的

复用likeadmin的基础功能和手机端uniapp源码，提高开发效率；

新增功能使用常驻内存的webman来实现。



## 插件安装

`composer require ledc/likeadmin`



## Nginx伪静态

```conf
location ~ ^/(like|gateway)
{
  proxy_set_header X-Real-IP $remote_addr;
  proxy_set_header Host $host;
  proxy_set_header X-Forwarded-Proto $scheme;
  proxy_http_version 1.1;
  proxy_set_header Connection "";
  if (!-f $request_filename){
    proxy_pass http://127.0.0.1:8787;
  }
}
```



## 原理

配置Nginx伪静态后，所有请求地址以`like`或`gateway`开头的接口，都由webman来处理。

自定义`like`或`gateway`：1.修改nginx伪静态；2.修改`config/plugin/ledc/likeadmin/middleware.php`



## 其他

仓库地址：https://gitee.com/ledc/webman_plugin_likeadmin

Webman plugin ledc/likeadmin；

> 类库在`\\David\\LikeAdmin`命名空间

