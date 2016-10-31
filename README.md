# laravel-revisionable

## 安装
```
composer require runner/laravel-revisionable
```

## 使用

#### 执行 migration
```
php artisan migrate --path=vendor/runner/laravel-revisionable/migrations
```

#### 在 Model 中使用 trait
```
#file: App/Models/Article.php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Runner\Revisionable\Revisionable;


class Article extends Model
{
    use Revisionable;
    
    // 不记录修改的字段
    protected $revisionExceptFields = ['created_at'];

    // 固定记录的字段， $revisionExceptFields 与 $revisionOnlyFields并存时，只生效$revisionOnlyFields
    protected $revisionOnlyFields = [];

    // 是否启用
    protected $revisionEnabled = true;

    // 格式化字段值输出配置
    protected $revisionFormattedFieldValues = [
        'title' => 'string:<textarea>%s</textarea>',
    ];

    // 输出字段名别名
    protected $revisionAliasedFieldNames = [
        'title' => '文章标题',
    ];

    // 记录IP
    protected $revisionRecordIp = true;
}

```

#### 读取日志
```

namespace App\Http\Controllers;

use Runner\Revisionable\Revision;

class LogsController extends Controller
{


    public function index()
    {
        $revisions = Revision::with('user')->get();

        return view('logs.index', compact('revisions'));
    }

}

```