# HuiCMF 2.0 后台管理系统

1. 默认使用单页面（page）非iframe开发
2. 后台UI默认使用Pear Admin Layui 4.0版本
3. Layui默认使用最新版：2.9.3版本

## 后台开发说明

### 1、控制器或者方法鉴权

控制器或者方法鉴权，统一使用中间件的方法。

如果不需要鉴权（不需要登录、不需要权限控制，可以在控制器中增加以下代码：）

```php
/**
 * 不需要登录的方法
 * @var string[]
 */
protected $noNeedLogin = [];

/**
 * 不需要权限的方法
 *
 * @var string[]
 */
protected $noNeedAuth = [];
```

在对应的数组中增加相应的方法名称即可。

### 2、开发后台功能

默认后台模块统一放置在了插件里（plugin），为了方便统一更新功能，在插件里可以执行升级操作。

但是升级操作默认会拉取最新admin插件代码并覆盖，所以推荐不要修改admin插件的目录结构。

如果需要开发后台功能模块，例如可以在admin插件的app/admin/目录下新建目录，例如：app/admin/controller/TestController.php
并定义index()方法，此时访问：/admin/test/index 默认就能访问到对应模块和方法。

> 注意：

以上开发形式，默认是所有用户都能访问到的，需要添加控制权限，只能后台登录后才能访问。

方法很简单，只需要在中间件配置中增加对应的admin中间件控制即可。
例如：

找到文件：config/middieware.php，在代码中增加：

```php
'admin' => [
        \plugin\admin\app\middleware\AccessControl::class,
        \plugin\admin\app\middleware\SystemLogControl::class,
        \plugin\admin\app\middleware\Lang::class,
    ]
````

（默认已加哦）

页面方法权限控制鉴权，请参考【1、控制器或者方法鉴权】

### 3、模板

> 注意：
>
> 在ui模板中不要出现相同的id，不要使用window.****=function()；
>
> 更换数据库配置，尤其是数据库名，一定要重启webman服务。

1、在页面中使用，一定要区分id值，不能和其他页面有重复的id值

例如数据表格页：

```html

<table class="layui-hide" id="ruleTable" lay-filter="ruleTable"></table>
```

```javascript
var ruleInit = treeTable.render({
  elem: '#ruleTable',

});
```

```html

<script type="text/html" id="optionTpl">
    <a class="layui-btn layui-btn-xs layui-btn-normal" data-open="/app/admin/rule/add?id={{d.id}}"
       data-title="添加下级" data-reload="1" permission="app.admin.rule.add">添加下级</a>
    <a class="layui-btn layui-btn-xs layui-btn-success" data-open="/app/admin/rule/edit?id={{d.id}}"
       data-title="编辑" data-reload="1">编辑</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" data-confirm="/app/admin/rule/delete"
       data-data="id={{d.id}}" data-title="确定要删除吗？" data-reload="1">删除</a>
</script>
```

```javascript
[
  {fixed: "right", title: "操作", width: 190, align: "center", toolbar: "#optionTpl"}
]
```

以上代码中：js中的 `elem`值和html中的`id`值一致，且其值不能和其他页面中有相同的id值，否则页面无法展示会显示空白。
最好的命名规则为：当前规则名称+Table，例如上面的：ruleTable，意为：角色表格。

