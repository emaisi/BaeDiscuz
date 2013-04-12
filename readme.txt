安装步骤（请严格安装安装步骤，不然不能保证正常运行)

1.创建百度云存储Bucket，最少为30m，并修改Bucket的属性为公开读.
2. 将压缩包解压，并将upload/bcs/config.php文件中BAIDU_BCS_BUCKET修改为第1步创建的百度云存储Bucket名。
3. 将upload目录上传到百度BAE.
4 .查看百度mysql的数据库名，并点击设置，将数据库默认字符集编码修改为utf8 (utf8_general_ci)。
5. 启用百度cache(缓存), 最少30m. 如果之前已经启用百度cache并且安装过discuz,请停用后再次启用。
6. 打开http://xxx.duapp.com/install/index.php来开始安装，过程中需要提供第4步百度mysql数据库的名称
7. 删除install目录
8.进入到后台管理，点击全局--〉上传设置，填写"本地附件 URL 地址"为http://bcs.duapp.com/xxx/data/attachment。xxx为第一步创建的bucket名称。


