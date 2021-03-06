<h1 align="center"> laravel-statistics </h1>

<p align=""><code>shanjing laravel-statistics</code>是一个基于<a href="https://laravel.com/" target="_blank"> laravel </a>开发而成的统计工具，只需很少的代码即可快速构建出一个功能完善的统计模块。开箱即用，对后端开发者非常友好。</p>


### 功能特性

- [x] 简洁优雅 API
- [x] 当缺失对应日期的数据时，自动补充 0 作为默认数据
- [x] json 格式存储数据，易于扩展字段, 存储更优雅

### 环境
- PHP > 7.4
- Laravel 8.*
- MySQL 8.*


### 安装

> 如果安装过程中出现`composer`下载过慢或安装失败的情况，请运行命令`composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/`把`composer`镜像更换为阿里云镜像。

首先需要安装`laravel`框架，如已安装可以跳过此步骤。如果您是第一次使用`laravel`，请务必先阅读文档 [安装 《Laravel中文文档》](https://learnku.com/docs/laravel/8.x/installation/9354) ！
```bash
composer create-project --prefer-dist laravel/laravel 项目名称 7.*
# 或
composer create-project --prefer-dist laravel/laravel 项目名称
```

安装完`laravel`之后需要修改`.env`文件，设置数据库连接设置正确

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database
DB_USERNAME=root
DB_PASSWORD=
```

安装`laravel-statistics`
```bash
cd {项目名称}
$ composer require shanjing/laravel-statistics -vvv
```

然后运行下面的命令来发布资源：

```bash
php artisan laravel-statistics:publish
```

在该命令会生成配置文件`config/statistics.php`，可以在里面修改数据库连接、以及表名，建议都是用默认配置不修改。
```

// 指定数据库链接.
'connection' => '',

// 指定表名，如果后期修改数据库表名，这里也需要修改成对应的表名.
'statistics_table' => 'laravel_statistics',
```
然后运行下面的命令完成安装：
- 注意： 由于运行安装是针对具体的迁移文件运行的， 迁移文件名不可以修改。
    - 文件："2021_09_22_083561_create_statistics_table.php"，
    - 目录：项目目录/database/migrations/

```bash
php artisan laravel-statistics:install
```

### 使用
上述步骤操作完成之后就可以使用统计功能了

- 使用统计表统计数据和读取数据

读取数据
```php
// 今天淘宝销量、销售额
// period   year | month | week | day
// orderBy  desc | asc
app('statistics')
->get("taobao", ['gmv', 'order_num'])
->period('day')
->orderBy("desc")
->exec();

// 20210901~20210921 淘宝销量、销售额
app('statistics')
->get("taobao", ['gmv', 'order_num'])
->occurredBetween([20210901, 20210921])
->period('day')
->orderBy("desc") 
->exec();
```

存储数据
```php
// 更新数据
// $arr： 会已 json 的格式存储在 data 列
// taobao： 存储在 key 列
// occurredAt: 存储在 occurred_at 列
$arr = ['gmv'=>'value', 'order_num'=>'value'];
app('statistics')
->save("taobao", $arr)
->occurredAt(20210921)
->exec();
```

- 使用已有的 model 展示实时数据

1）向需要统计功能的 model 引入 statistics 模块
```php
<?php
  
namespace App\Models\Order;
  
use Illuminate\Database\Eloquent\Model;
use Shanjing\LaravelStatistics\Traits\Statistics;
  
class Order extends Model
{ 
    use Statistics;
  
    protected $table = '';
  
    protected $connection = '';
}
```
2）在调用的 controller 里，调用
```php
// period   year | month | week | day
// occurredBetween 时间范围
// orderBy  desc | asc
Order::period("day")
   ->occurredBetween(["20210718", "20210921"])
   ->selectRaw('count(id) as total') // 支持 select 语句
   ->selectRaw('SUM(`price`) as gmv') // 支持 select 语句
   ->orderBy('desc')
   ->summary();
```
### Contributors

This project exists thanks to all the people who contribute. [[Contribute](CONTRIBUTING.md)].

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/shanjing/laravel-statistics/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/shanjing/laravel-statistics/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT