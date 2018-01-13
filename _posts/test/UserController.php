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

    public
    static function getGroupOrderList($activity_id, $activity_type, $page, $keywords = '', $status = 0)
    {
        $where = [
            'activity_id' => $activity_id,
            'is_del' => 0,
//            'status' => ['neq', 10]
        ];
        $page_size = 15;
        $offset = ($page - 1) * $page_size;

        if ($keywords) {
            //orderid  name
            $where['orderid|name'] = ['like', '%' . $keywords . '%'];
        }

        $order_status = ['全部', '待支付', '待领取', '完成', '拼团中', '已成团', '已付全款', 10 => '已退款'];
        $purchase_status = ['拼团中', '已成团'];
        if ($status) {
            if ($status == 4) {
                //拼团中
                $where['purchase_status'] = 0;
            } elseif ($status == 5) {
                $where['purchase_status'] = 1;
            } else if ($status == 6) {
                $where['price'] = ['exp', ' = paid'];
            } else {
                $where['status'] = $status;
            }
        }

        $db = Db::name('activity_order');
        $data = $db->where($where)->limit($offset, $page_size)->order('pid desc')->group('pid,id')->select();

        $data_info = [];
        $data_extra = [];
        $new_data = [];
        $count = 0;
        if ($data) {
            foreach ($data as $k => $v) {
                $data_info[$k] = [
                    'orderid' => $v['orderid'],
                    'status' => $status ? $order_status[$status] : $purchase_status[$v['purchase_status']],
                    'name' => $v['name'],
                    'phone_no' => $v['phone_no'],
                    'price' => $v['price'],
                    'paid' => $v['paid'],
                    'pid' => $v['pid'],
                    'is_tuanzhang' => $v['is_tuanzhang'],
                    'debt' => $v['price'] - $v['paid'],
                    'desc' => $v['desc'],
                    'id' => $v['id']
                ];
            }
            $count = $db->where($where)->count();

            foreach ($data_info as $v) {
                if ($v['is_tuanzhang']) {//如果是团长，找到下面的小弟
                    foreach ($data_info as $v1) {
                        if ($v['pid'] == $v1['pid']) {//找到了
                            if (!array_key_exists('children', $v)) {//用一个数组保存小弟
                                $v['children'] = [];
                            }
                            if ($v1['is_tuanzhang'] != 1) {//排除自己
                                $v['children'][] = $v1;
                            }
                        }
                    }
                    $new_data[] = $v;
                }
            }

            /*foreach ($new_data as &$v1) {
                if (!array_key_exists('children',$v1)) {
                    $v1['children'] = [];
                }
            }
            unset($v1);*/
            /* foreach ($data_info as $v) {
                 if ($v['is_tuanzhang']) {
                     foreach ($data_info as $v1) {
                         if ($v['pid'] == $v1['pid']) {
                             $data_extra[$v['pid']][] = $v1;
                         }
                     }
                 }
             }
             $keys = array_keys($data_extra);
             rsort($keys);

             foreach ($keys as $v) {
                 array_push($new_data, $data_extra[$v]);
             }
             unset($data_extra, $data_info);*/

        }
        $res['data_list'] = $new_data;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_size;
        $res['page_data']['page'] = $page;

        return msg(0, 'success', $res);
    }

    //点亮图标设置
    public function add_light($params)
    {
        if (empty($params['activity_id']) || empty($params['name']) || empty($params['status']) || empty($params['share_range']) || empty($params['pic_num']) || empty($params['gift_pic'])) {
            return msg(1, '参数错误');
        }

        $activity_id = $params['activity_id'];

        $light = [
            'activity_id' => $activity_id,                //活动id
            'name' => $params['name'],
            'status' => $params['status'],                //是否启用
            'share_range' => $params['share_range'],      //分享范围 0朋友圈 1好友、群 2朋友圈、好友、群
            'pic_num' => $params['pic_num'],              //点亮图标个数
            'gift_pic' => $params['gift_pic'],         //礼物图片
            'createtime' => time(),
        ];

        Db::startTrans();//开启事务
        try {
            $exist = Db::name('activity_setting_icon')->where('activity_id', $activity_id)->find();
            $old_icons = [];
            $new_icons = [];
            if (!$exist) {
                $insert_id = Db::name('activity_setting_icon')->insertGetId($light);
                if (!$insert_id) throw new Exception('图标设置失败');
            } else {
                Db::name('activity_setting_icon')->where('activity_id', $activity_id)->update($light);
                $insert_id = $exist['id'];
                //找到所有旧图标
                $old_img = Db::name('activity_setting_img')->where('activity_id', $activity_id)->select();
                $old_icons = array_column($old_img, 'icons', 'icon_id');//[[],[],[]]老的
            }
            if (!empty($params['icons'])) {
                $all_data = [];
                $icons = json_decode($params['icons'], true);
                foreach ($icons as $k => $v) {
                    $i = json_encode(['dark' => $v['dark'], 'light' => $v['light']]);
                    if (in_array($i, $old_icons)) {
                        continue;
                    }
                    $new_icons[] = $i;//新的[[],[],[],[]]
                    $arr = [
                        'icons' => $i,
                        'sort' => $v['sort'],
                        'activity_id' => $activity_id,
                        'icon_id' => $insert_id
                    ];
                    $all_data[] = $arr;
                }
                $diff2 = array_diff($old_icons, $new_icons);//delete
                Db::name('activity_setting_img')->insertAll($all_data);//批量添加
                //删除在老的但不在新的数据
                foreach ($diff2 as $v) {
                    Db::name('activity_setting_img')->where('icons', $v)->delete();
                }
            }

            Db::commit();//事务提交
            return msg(0, '图标设置成功');
        } catch (\Exception $e) {
            Db::rollback();//事务回滚
            return msg(1, $e->getMessage());
        }
    }

    //点亮图标设置
    public function add_light1($params)
    {
        if (empty($params['activity_id']) || empty($params['name']) || empty($params['status']) || empty($params['share_range']) || empty($params['pic_num']) || empty($params['gift_pic'])) {
            return msg(1, '参数错误');
        }

        $activity_id = $params['activity_id'];

        $light = [
            'activity_id' => $activity_id,                //活动id
            'name' => $params['name'],
            'status' => $params['status'],                //是否启用
            'share_range' => $params['share_range'],      //分享范围 0朋友圈 1好友、群 2朋友圈、好友、群
            'pic_num' => $params['pic_num'],              //点亮图标个数
            'gift_pic' => $params['gift_pic'],         //礼物图片
            'createtime' => time(),
        ];
        $new_count = $params['pic_num'];//3
        Db::startTrans();//开启事务
        try {
            $exist = Db::name('activity_setting_icon')->where('activity_id', $activity_id)->find();
            $old_icons = [];
            $old_ids = [];
            if (!$exist) {
                $insert_id = Db::name('activity_setting_icon')->insertGetId($light);
                if (!$insert_id) throw new Exception('图标设置失败');
            } else {
                Db::name('activity_setting_icon')->where('activity_id', $activity_id)->update($light);
                $insert_id = $exist['id'];
                //找到所有旧图标
                $old_data = Db::name('activity_setting_img')->where('activity_id', $activity_id)->select();
                $old_icons = array_column($old_data, 'icons');
                $old_ids = array_column($old_data, 'id');
            }
            if (!empty($params['icons'])) {
//                $new_icon = json_decode($params['icons'], true);
                $new_icon = $params['icons'];
                $count = count($old_ids);//5
                if (in_array($new_icon, $old_icons)) {
                    if ($count > $new_count) {
                        $del_ids = array_splice($old_ids, $new_count);
                        Db::name('activity_setting_img')->where(['id' => ['in', $del_ids]])->delete();
                    }
                    if ($count < $new_count) {//3,5
                        $idx = $count;
                        for ($i = 1; $i <= $new_count - $count; $i++) {
                            $arr[] = [
                                'icons' => $new_icon,
                                'sort' => $idx++,
                                'activity_id' => $activity_id,
                                'icon_id' => $insert_id
                            ];
                        }
                        Db::name('activity_setting_img')->insertAll($arr);
                    }
                } else {
                    for ($i = 1; $i <= $new_count; $i++) {
                        $arr[] = [
                            'icons' => $new_icon,
                            'sort' => $i,
                            'activity_id' => $activity_id,
                            'icon_id' => $insert_id
                        ];
                    }
                    Db::name('activity_setting_img')->where('activity_id', $activity_id)->delete();
                    Db::name('activity_setting_img')->insertAll($arr);
                }
            }

            Db::commit();//事务提交
            return msg(0, '图标设置成功');
        } catch (\Exception $e) {
            Db::rollback();//事务回滚
            return msg(1, $e->getMessage());
        }
    }
}