# zphp
框架依赖


##### 路由规则：

```
ip:port/$app/$module/$controller/$action
ip:port/$module/$controller/$action
ip:port/$controller/$action
ip:port/$controller
```
其余都读取配置文件中的参数

##### service调用方式
```
App::service("App#test")->test();
```
##### model调用方式

```
App::model("App#test")->test();
```

##### 多个数据库配置：

```
'mysql' => array(
        'default' => [
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => '******',
            'database' => 'zhttp',
            'asyn_max_count' => 2,
            'start_count' => 2,
        ],
        'read' => [
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => '******',
            'database' => 'zhttp',
            'asyn_max_count' => 5,
            'start_count' => 5,
        ]
    )
```
##### 数据库操作方式

```
Db::table("#DbName$tableName")
```

#### Db::task() 执行task

##### 每个task都可以单独创建一个数据库链接

```
project.syncDb = true
```

使用如下代码在task中使用数据库链接
```
public function setDb($db)

```

###### task中定义init 再载入task代码的时候被执行

###### task中定义before 在task执行方法前被执行

###### task中定义complete 在task执行方法后被执行