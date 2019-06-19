<?php
/**
 * Created by PhpStorm.
 * User: kdcq101
 * Date: 2019/6/11
 * Time: 下午2:58
 */

namespace Main\api\darts;


use Main\api\ctrl_;
use Main\api\servActivity;
use Main\servActivitySys;
use Main\servDarts;
use Main\servEnum;
use Main\servGlobal;
use Main\servLang;
use Main\servRole;
use Admin\server\data;

class ctrlTopTenRank extends ctrl_
{

    public function _DO_()
    {
        if (!isset($this->vars['id']) || ($activityId = intval($this->vars['id'])) <= 0) {
            servGlobal::outPutErrMsg(servEnum::RESPONSE_ERROR, servEnum::MSG_KEY_PARAM_ERR, 'id');
            exit;
        }
        if (!isset($this->vars['type']) || ($type = intval($this->vars['type'])) <= 0) {
            servGlobal::outPutErrMsg(servEnum::RESPONSE_ERROR, servEnum::MSG_KEY_PARAM_ERR, 'type');
            exit;
        }
        // 活动有效性
        servActivity::checkActivity($activityId, servActivitySys::TYPE_DARTS_RANK, 0, true);

        $role = servRole::globalRoleInfo();
        $out = servDarts::getRankByRedis($role, $type, 10);
        if ($type == 1) {    // 没有商会时,返回空. 个人榜单时,没有积分就是未上榜
            $out['myRank']['rname'] = $role['rname'];

            if (!$out['myRank']) {
                $out['myRank'] = [
                    'rank' => servLang::getTipContent('notInTheList'),
                    'rid' => $role['rid'],
                    'rname' => $role['rname'],
                    'score' => 0,
                ];
            }
        }

        // 获取前十rid, 一次查询获取[rid => rname, ...], 把rname加入$out['rankTopTen']
        if ($type === 1) {
            if (empty($out['rankTopTen'])) {
                // todo  打印日志并返回
            }

            $ridTopTen = array_column($out['rankTopTen'], 'rid');
            if (empty($ridTopTen)) {
                // todo 返回错误
            }

            $db = data::db();
            $sql = 'SELECT rid, rname FROM role WHERE rid IN (' . implode(',', $ridTopTen) . ')';
            $rInfo = $db->run($sql)->fetchAll('rid', 'rname');
            if (empty($rInfo)) {
                // todo 返回错误
            }

            foreach ($out['rankTopTen'] as &$v) {
                $rid = $v['rid'];
                $v['rname'] = $rInfo[$rid];
            }
            unset($v);
        }

        servGlobal::outPutSuccessResponse($out);
    }

}