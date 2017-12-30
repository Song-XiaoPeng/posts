<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/29 0029
 * Time: 16:42
 */

namespace App\Http\Controllers;

use App\TEMPLAT;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * 为指定用户显示详情
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        DB::table('user');//返回一个查询构造器实例

        //2 查询构造器
        //使用 DB facade 的 table 方法开始查询
        //这个 table 方法针对查询表返回一个查询构造器实例，允许你在查询时链式调用更多约束，并使用 get 方法获取最终结果
        $res = DB::table('user')->get();//从数据表中获取所有的数据列 数组形式返回结果集，数组中每一个结果都是一个stdClass对象
        $res = DB::table('user')->first(); //获取单行 返回单个 StdClass 对象
        $res = DB::table('user')->value('id'); //默认只能从单条记录中取出单个值,直接返回字段的值
        $res = DB::table('user')->pluck('id');//获取一个包含单个字段值的集合
        $res = DB::table('user')->pluck('id', 'value');//指定自定义的键值字段：：id 值，name 键，若name有重复，默认后面值会覆盖前面的值 返回一个collection实例，protected的items属性里面保存有结果数组;可以直接遍历这个对象
        //2.1 聚合 count、 max、 min、 avg 和 sum 可以在创建查询后调用其中的任意一个方法
        $users = DB::table('users')->count();
        //2.2 指定一个 Select 子句
        DB::table('users')->select('id', 'value')->distinct()->get();//select 子句用来查询指定的字段
        //distinct方法允许你强制让查询返回不重复的结果
        //如果你已有一个查询构造器实例，并且希望在现有的 select 子句中加入一个字段，则可以使用 addSelect 方法
        $query = DB::table('user')->select('id', '');
        $res = $query->addSelect('value')->get(); //在现有的 select 子句中加入一个字段
        //2.3 原始表达式
        $users = DB::name('user')->select(DB::raw('count(*) as count')) //有时候你可能需要在查询中使用原始表达式。这些表达式将会被当作字符串注入到查询中，所以要小心避免造成 SQL 注入攻击
            ->where('') //要创建一个原始表达式，可以使用 DB::raw 方法
            ->groupBy()
            ->get();

        foreach ($res as $v) {
            echo $v;
        }
        //结果分块 如果你需要操作数千条数据库记录
        //这个方法每次只取出一小块结果，并会将每个块传递给一个 闭包 处理。这个方法对于编写数千条记录的 Artisan 命令 是非常有用的。例如，让我们把 users 表进行分块，每次操作 100 条数据：
        DB::table('user')->orderBy('id')->chunk(2, function ($users) { //You must specify an orderBy clause when using this function.
            dump($users);//Collection对象
            foreach ($users as $user) {
                dd($user);//stdClass对象
                echo $user->id . '<br>';
                return false;//可以从 闭包 中返回 false，以停止对后续分块的处理
            }
        });
        /*
         * Collection{#176:[]}
         * */
        dd($res);

        //1 原生sql
        $sql = "select * from user";
        $res = DB::select($sql);//数组形式返回结果集，数组中每一个结果都是一个stdClass对象
        dump($res);
        $sql1 = "insert into user values (1,'sone'),(2,'xiao'),(3,'peng')";
        $res = DB::insert($sql1);//
        dd($res);
        die;
        DB::update($sql);//更新已经存在于数据库的记录。该方法会返回此语句执行所影响的行数
        DB::delete('delete from users');// 删除的行数将会被返回
        DB::statement('drop table users');//有些数据操作没有返回值， 可以运行一般声明
        //监听查询事件
        //如果你希望能够监控到程序执行的每一条 SQL 语句，那么你可以使用 listen 方法。 该方法对查询日志和调试非常有用，你可以在 服务容器 中注册该方法：
        //AppServiceProvider


        dd($res);
        die;
        return view('home.profile', ['res' => $res]);
    }
}