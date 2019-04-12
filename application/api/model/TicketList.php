<?php
/**
 * User: xiebing
 * Date: 2019-3-7
 * Time: 17:46
 */

namespace ticket\api\model;

use ticket\common\model\Common;

class TicketList extends Common {
    protected $pk = 'id';

    const field = 'a.id,b.name as type_name,a.name,a.intro,a.original,a.present,a.hot,a.recom,a.top,a.shelves,a.thumb,a.know_id,a.sales';

    public function getList($where = array()) {
        $data = TicketList::alias('a')->field(self::field)
            ->join('ticket_type b', 'a.type=b.uniqid', 'left')->order('a.top,a.recom,a.hot')
            ->where($where)->paginate(input('post.limit', null))->each(function ($item, $key) {
            $thumb = self::getCommonImg(array('_hash' => $item['thumb']), 'url');
            $item->thumbHash = $item['thumb'];
            $item->thumb = $thumb['url'];
            $item->todayPrice = TicketList::getPrice($item['id'], date('Y-m-d', time()));
            $item->know = TicketKnow::field($this->noField, true)->where('uniqid', $item['know_id'])->find();
        });
        return $data ? $data->toArray() : array();
    }

    public function getFind($where = array()) {
        $data = TicketList::alias('a')->field(self::field)->join('ticket_type b', 'a.type=b.uniqid', 'left')
            ->order('a.top,a.recom,a.hot')->cache(true,60)->where($where)->find();
        if (!empty($data['thumb'])) {
            $thumbImg = self::getCommonImg(array('_hash' => $data['thumb']), 'url');
            $data['thumbHash'] = $data['thumb'];
            $data['thumb'] = $thumbImg['url'];
        }
        $data['todayPrice'] = TicketList::getPrice($data['id'],date('Y-m-d',time()));
        $data['know'] = TicketKnow::field($this->noField, true)->where('uniqid', $data['know_id'])->find();
        unset($data['know_id']);
        return $data->toArray();
    }

    public function getAll($where = array()) {
        $data = TicketList::alias('a')->field(self::field)
            ->join('ticket_type b', 'a.type=b.uniqid', 'left')
            ->order('a.top,a.recom,a.hot')
            ->cache(true,60)->where($where)->all()->each(function ($item, $key) {
            $thumb = self::getCommonImg(array('_hash' => $item['thumb']), 'url');
            $item->thumbHash = $item['thumb'];
            $item->thumb = $thumb['url'];
            $item->todayPrice = TicketList::getPrice($item['id'],date('Y-m-d',time()));
            $item->know = TicketKnow::field($this->noField, true)->where('uniqid', $item['know_id'])->find();
        });
        return $data ? $data->toArray() : array();
    }

    public static function getPrice($ticketId, $start, $end = '') {
        $where[] = ['ticket', '=', $ticketId];
        if (!empty($start) && !empty($end)) {
            $where[] = ['date', '>=', date('Ymd', strtotime($start))];
            $where[] = ['date', '<=', date('Ymd', strtotime($end))];
            $data = TicketPrice::field('(cost+profit) as money,DATE_FORMAT(`date`,\'%Y-%m-%d\') as `date`')
                ->cache(true,60)->where($where)->select()->toArray();
            while ($start) {
                if (strtotime($start) > strtotime($end)) {
                    $start = false;
                } elseif (!in_array($start, array_column($data, 'date'))) {
                    $ticketDetail = TicketList::cache(true)->find($ticketId);
                    $data[] = array(
                        'date' => $start,
                        'money' => $ticketDetail['present']
                    );
                } else {
                    $start = date('Y-m-d', strtotime($start) + 86400);
                }
            }
        } elseif (!empty($start) && empty($end)) {
            $where[] = ['date', '=', date('Ymd', strtotime($start))];
            $data = TicketPrice::where($where)->value('(cost+profit) as money');
            if (empty($data)) {
                $data = TicketList::cache(true)->where('id', $ticketId)->value('present');
            }
        }
        return !empty($data) ? $data : '';
    }

}
