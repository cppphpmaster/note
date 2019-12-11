<?php
/**
 * Created by PhpStorm.
 * User: kdcq101
 * Date: 2019/6/10
 * Time: 下午9:35
 */

namespace Main\api\darts;


use Main\api\ctrl_;
use Main\api\servActivity;
use Main\confDartsReward;
use Main\confItem;
use Main\servActivitySys;
use Main\servDarts;
use Main\servEnum;
use Main\servGlobal;
use Main\servRedis;
use Main\servResource;
use Main\servRole;

class ctrlPlayDarts extends ctrl_
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
        if (!isset($this->vars['cid']) || ($cid = intval($this->vars['cid'])) <= 0) {
            servGlobal::outPutErrMsg(servEnum::RESPONSE_ERROR, servEnum::MSG_KEY_PARAM_ERR, 'cid');
            exit;
        }
        // 活动有效性
        $activity = servActivity::checkActivity($activityId, servActivitySys::TYPE_DARTS_RANK, 0, true);
        if ($activity['tmsEnd'] < time()) { //  特殊处理,如果在领取奖励范围时间内,玩家不能玩飞镖
            servGlobal::outPutErrMsg(servEnum::RESPONSE_ERROR, 'haveEnded');
            exit;
        }
        if (!isset(confItem::$data[$cid]) || !isset(confDartsReward::$data[$cid]) || confItem::$data[$cid]['type'] != servEnum::CID_TYPE_DARTS) {
            servGlobal::outPutErrMsg(servEnum::RESPONSE_ERROR, 'propConfError');
            exit;
        }

        // 计算权重道具
        $randRewards = confDartsReward::$data[$cid]['randomReward'];
        $weights = array_column($randRewards, 'weight');
        $k = rand(1, array_sum($weights));
        uasort($randRewards, 'self::cmp');
        $reward = [];
        foreach ($randRewards as $item) {
            if ($k <= $item['weight']) {
                unset($item['weight']);
                $reward = [$item];
                break;
            }
        }
        if (empty($reward)) {
            $item = array_pop($randRewards);
            unset($item['weight']);
            $reward = [$item];
        }
        $consume[$cid] = 1;
        $rewards = servGlobal::getInProps(array_merge([confDartsReward::$data[$cid]['fixedReward']], $reward));
        $role = servRole::globalRoleInfo();
        // 消耗道具并获得道具
        servResource::consumeAndReward($consume, $rewards, $role, $cid . '-' . 1, $success, $updateData, $propsUpdate);
        if (!$success) {
            servGlobal::outPutErrMsg(servEnum::RESPONSE_ERROR, 'propNotEnough');
            exit;
        }
        // 活动数值记录
        servActivity::dataRecord($role['rid'], $activity['type'], confDartsReward::$data[$cid]['score'], $role['allianceId']);

        // 处理redis
        $Redis = servRedis::getRedisMain();
        $redisDartsKey = servRedis::ROLE_DARTS_SCORE . $role['sid'];
        $redisAllianceDartsKey = servRedis::ALLIANCE_DARTS_SCORE . $role['sid'];
        $expireTime = ($activity['tmsEnd'] + SECONDS_DAY) - time();

//        if (!$Redis->exists($redisDartsKey)) {
//            $score = [
//                'rname' => $role['rname'],
//                'score' => confDartsReward::$data[$cid]['score']
//            ];
//            $Redis->hSet($redisDartsKey, $role['rid'], json_encode($score));
//            $Redis->expire($redisDartsKey, $expireTime);
//        } else {
//            $score = json_decode($Redis->hGet($redisDartsKey, $role['rid']), true) ?? ['rname' => $role['rname'], 'score' => 0];
//            $score['score'] += confDartsReward::$data[$cid]['score'];
//            $Redis->hSet($redisDartsKey, $role['rid'], json_encode($score));
//        }

        // 更新个人排行
        $myScore = intval($Redis->zIncrBy($redisDartsKey, confDartsReward::$data[$cid]['score'], $role['rid']));
        $Redis->expire($redisDartsKey, $expireTime);

        // 更新商会排行
        if ($role['allianceId'] > 0) {
            if (!$Redis->exists($redisAllianceDartsKey)) {
                $Redis->hSet($redisAllianceDartsKey, $role['allianceId'], intval(confDartsReward::$data[$cid]['score']));
                $Redis->expire($redisAllianceDartsKey, $expireTime);
            } else {
                $Redis->hIncrBy($redisAllianceDartsKey, $role['allianceId'], intval(confDartsReward::$data[$cid]['score']));
            }
        }

        // 返回数据
        $out['consume'] = servGlobal::getOutProps($consume);
        $out['reward'] = $reward;
        if ($propsUpdate) {
            $out['propsUpdate'] = $propsUpdate;
        }
        if ($updateData) {
            $out['roleUpdate'] = $updateData;
        }
        $out['score'] = $myScore;
        $out['rankScore'] = confDartsReward::$data[$cid]['score'];
        $out['activityScore'] = $rewards[servEnum::CID_DARTS_SCORE];
        $topTen = servDarts::getRankByRedis($role, $type, 10); // todo test
        $out['myRank'] = $topTen['myRank'];
        $out['rankTopTen'] = $topTen['rankTopTen'];
        servGlobal::outPutSuccessResponse($out);
    }

    private static function cmp($a, $b)
    {
        if ($a['weight'] == $b['weight']) {
            return 0;
        }
        return ($a['weight'] > $b['weight']) ? 1 : -1;
    }
}