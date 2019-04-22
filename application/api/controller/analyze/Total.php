<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-4-19
 * Time: 14:52
 */

namespace ticket\api\controller\analyze;


use Think\Db;
use ticket\common\controller\Api;

class Total extends Api {

    public function index(){
        $data['member']=Db::field('COUNT(*) total,CONCAT(\'today\') AS `type`')
            ->name('member_web web')
            ->where('DATE_FORMAT(web.`add_time`,\'%Y-%m-%d\')=DATE_FORMAT(NOW(),\'%Y-%m-%d\')')
            ->union(function ($query){
                $query->field('COUNT(*) total,CONCAT(\'all\')')->name('member_web');
            })->select();

        $data['ticket']=Db::name('order_body')->field('COUNT(*) total,CONCAT(\'today\') AS `type`')
            ->where('add_time is not null')
            ->union(function ($query){
                $query->field('COUNT(*) ,CONCAT(\'all\') AS `type`')
                    ->where('DATE_FORMAT(add_time,\'%Y-%m-%d\')=DATE_FORMAT(NOW(),\'%Y-%m-%d\')')->name('order_body');
            })->select();

        $data['earn']=Db::name('order_body')->field('SUM(pay_money) AS total,CONCAT(\'all\') AS `type`')->where('state in (1,5)')
            ->union(function ($query){
                $query->name('order_body')->field('IFNULL(SUM(pay_money),0),CONCAT(\'today\')')
                    ->where('state in (1,5) AND DATE_FORMAT(pay_time,\'%Y-%m-%d\')=DATE_FORMAT(NOW(),\'%Y-%m-%d\')');
            })->fetchSql(false)->select();
        $this->ajaxReturn(0,$data);
    }

    public static function tempNumber($type = 'sql',$beginDate='',$afterDate='',$countSql='') {
        switch ($type) {
            case 'sql':
                $tableName=Db::getTable('temp_number');
                return 'SELECT n.number + n10.number * 10 + n100.number * 100 AS id 
                          FROM ' . $tableName . ' n
                            CROSS JOIN ' . $tableName . ' AS n10
                            CROSS JOIN ' . $tableName . ' AS n100';
                break;
            case 'date':
                $crossSql=self::tempNumber();
                return Db::table("($crossSql) as numlist")
                    ->field('ADDDATE(\''.$beginDate.'\',numlist.id) as date')
                    ->where('ADDDATE(\''.$beginDate.'\', numlist.id) <= DATE_FORMAT(\''.$afterDate.'\',\'%Y-%m-%d\')')
                    ->fetchSql()->select();
                break;
            case 'count':
                $dateSql=self::tempNumber('date',$beginDate,$afterDate);
                return Db::table("($dateSql) as temp")
                    ->field('temp.date,COALESCE(count.total,0) AS value')
                    ->join("($countSql) as count",'temp.date=count.date','left')
                    ->order('temp.date')->select();
                break;
        }
    }
}