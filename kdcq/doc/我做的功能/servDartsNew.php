<?php
/**
 * Created by PhpStorm.
 * User: kdcq101
 * Date: 2019/6/11
 * Time: 下午2:00
 */

namespace Main;


use Main\server\dataAlliance;

class servDarts
{
    /**
     * @param $role
     * @param int $type
     * @param int $topNum
     * @return array
     */
    public static function getRankByRedis($role, $type = 1, $topNum = 0)
    {
        $Redis = servRedis::getRedisMain();
        $redisDartsKey = servRedis::ROLE_DARTS_SCORE . $role['sid'];
        $redisAllianceDartsKey = servRedis::ALLIANCE_DARTS_SCORE . $role['sid'];
        $myRank = [];
        $topList = [];
        switch ($type) {
            case 1: // 个人
//                $rankList = $Redis->hGetAll($redisDartsKey);
//                foreach ($rankList as $rid => $item) {
//                    $rankList[$rid] = json_decode($item, true);
//                }
//                uasort($rankList, 'self::cmp');
//                $rank = 1;
//                foreach ($rankList as $rid => $item) {
//                    if ($rid == $role['rid']) {
//                        $myRank = [
//                            'rank' => $rank,
//                            'rid' => $rid,
//                            'score' => $item['score']
//                        ];
//                        if ($rank > $topNum) break;
//                    }
//                    if ($rank <= $topNum) {
//                        $topList[] = [
//                            'rank' => $rank,
//                            'rid' => $rid,
//                            'score' => $item['score']
//                        ];
//                    } else if (!empty($myRank)) {
//                        break;
//                    }
//                    ++$rank;
//                }


                // 获取前十
                $rankList = $Redis->zRevRange($redisDartsKey, 0, $topNum - 1, true);
                if (empty($rankList)) {
                    return ['myRank' => $myRank, 'rankTopTen' => $topList];
                }

                $rank = 1;
                foreach ($rankList as $rid => $score) {
                    $topList[] = [
                        'rank' => $rank++,
                        'rid' => $rid,
                        'score' => intval($score)
                    ];
                }

                // 获取自己
                $iRank = $Redis->zRevRank($redisDartsKey, $role['rid']) + 1;
                $iScore = intval($Redis->zScore($redisDartsKey, $role['rid']));
                $myRank = [
                    'rank' => $iRank,
                    'rid' => $role['rid'],
                    'score' => $iScore
                ];

                break;
            case 2: // 商会
                $allianceRankList = $Redis->hGetAll($redisAllianceDartsKey);
                if (!$allianceRankList) {
                    $myRank = $topList = [];
                    break;
                }
                $rank = 1;
                $allianceIds = [];
                foreach ($allianceRankList as $aid => $score) {
                    if ($aid == $role['allianceId']) {
                        $allianceIds[] = $aid;
                        $myRank = [
                            'rank' => $rank,
                            'rid' => $aid,
                            'score' => intval($score)
                        ];
                        if ($rank > $topNum) break;
                    }
                    if ($rank <= $topNum) {
                        $allianceIds[] = $aid;
                        $topList[] = [
                            'rank' => $rank,
                            'rid' => $aid,
                            'score' => intval($score)
                        ];
                    } else if (!empty($myRank) || $role['allianceId'] == 0) {
                        break;
                    }
                    ++$rank;
                }

                // 处理商会名称
                $allianceInfo = dataAlliance::fetchAll(['id in (' . implode(',', $allianceIds) . ')'], 'id, name', null, 'id');
                if (!empty($myRank)) {
                    $myRank['rname'] = $allianceInfo[$role['allianceId']]['name'] ?? '';
                }
                foreach ($topList as &$item) {
                    $item['rname'] = $allianceInfo[$item['rid']]['name'] ?? '';
                }
                break;
            default:
                break;
        }
        return ['myRank' => $myRank, 'rankTopTen' => $topList];
    }

    private static function cmp($a, $b)
    {
        if ($a['score'] == $b['score']) {
            return 0;
        }
        return ($a['score'] > $b['score']) ? -1 : 1;
    }
}