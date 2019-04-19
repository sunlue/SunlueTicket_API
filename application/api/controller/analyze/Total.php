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