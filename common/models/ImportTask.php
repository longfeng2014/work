<?php
/**
 * 导入任务
 * @author Yang Qi <qi.yang@bangongyi.com>
 *
 */
namespace common\models;

use common\admin\models\Apps;
use common\admin\models\Department;
use common\admin\models\Staff;
use common\admin\models\StaffTag;
use common\admin\models\StaffArchives;
use common\admin\models\Extfield;
use common\base\Query;
use common\helpers\AddressHelper;
use common\helpers\Helper;
use common\librarys\Import;
use common\librarys\QyWechat;
use PHPExcel_Style_NumberFormat;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Json;

class ImportTask extends \common\base\ActiveRecord {
    // 名称路径数组
    public $nameArr = [];
    // 以we_id为key的数组
    public $weIdArr = [];
    // qywechat
    public $qyWechat;
    // log  只记录发生异常的
    public $log = [];
    //所有考勤id
    public $checkIdArr = [];
    //所有weUserId
    public $weUserIdArr = [];
    // 需要导入的字段，如果有增加字段，在此处写对应关系即可
    public static $excelField = [
        'we_name'           => '姓名',
        'english_name'      => '英文名',
        'we_userid'         => '帐号',
        'we_gender'         => '性别',
        'we_weixinid'       => '微信号',
        'we_mobile'         => '手机号',
        'we_email'          => '邮箱',

        'we_department'     => '所在部门',
        'we_position'       => '职位',
        'position_level_id' => '职级',
        'lead_id'           => '直接上级',
        'subordinate'       => '直接下属',
        'we_tag'            => '标签',
        'we_tel'            => '工作电话',
        'address'           => '工作地址',
        'check_id'          => '考勤机员工编号',
    ];

    // 人事信息需要导入的字段，如果有增加字段，在此处写对应关系即可
    public static $personnelField = [
        'we_name'           => '姓名',
        'we_userid'         => '帐号',
        'status'            => '人事状态',
        'entry_time'        => '入职时间',
        'positive_time'     => '转正时间',
        'quite_time'        => '离职时间',
        'quite_reason'      => '离职理由',
        'back_time'         => '复职时间',
        'work_time'         => '参加工作时间',
        'adjust_work_year'  => '调整工龄',
        'bank_card'         => '银行卡账户',
        'bank_name'         => '开户行',
        'first_contract_begin_date' => '首次合同开始时间',
        'first_contract_end_date'   => '首次合同结束时间',
        'now_contract_begin_date'   => '现合同开始时间',
        'now_contract_end_date'     => '现合同结束时间',
        'now_contract_period'       => '合同期限',
        'renewal_times'     => '续签次数',
        'hire_source'       => '招聘渠道',
        'recommended'       => '推荐企业/人',
        'idcode'            => '身份证号',
        'birthday_g'        => '农历生日',
        'birthday_l'        => '公历生日',
        'highest_degree'    => '最高学历',
        'graduation_school' => '毕业学校',
        'major'             => '专业',
        'nationality'       => '民族',
        'political_state'   => '政治面貌',
        'marriage_status'   => '婚姻状况',
        'emergency_name'    => '紧急联系人姓名',
        'emergency_mobile'  => '紧急联系人电话'
    ];

    /**
     * 添加导入任务
     */
    public function addTask($task) {
        //是否有通讯录权限
        $this->we_account_id   = $task['we_account_id'] ?: 0;
        $this->user_id         = $task['user_id'] ?: 0;
        $this->appstore_id     = $task['appstore_id'] ?: 0;
        $this->type            = $task['type'] ?: '';
        $this->status          = $task['status'] ?: 0;
        $this->create_time     = time();
        $this->finished_time   = intval($task['finished_time']) ?: 0;
        $this->filepath        = $task['filepath'] ?: '';
        $this->import_log_path = $task['import_log_path'] ?: '';
        if (is_array($task)) {
            $this->param = Json::encode($task['param']) ?: '';
        } else {
            $this->param = $task['param'] ?: '';
        }
        return $this->save() ? $this : false;
    }

    /**
     * 处理单个导入任务
     */
    public function processTask($taskid) {
        global $objPHPExcel;
        $objPHPExcel = null;
        if ($taskid) {
            $task = self::findOne($taskid);
            if (empty($task)) {
                return false;
            }
        } else {
            $task = $this;
        }
        $time = time();
        if ($task['status'] != 0) {
            echo 'task is execution 此导入任务已执行' . PHP_EOL . PHP_EOL;
            return false;
        } else {
            //开始任务
            $task->status = 1;
            $task->save();
        }
        $field   = [];
        $result  = '';
        $success = 0;
        $app     = Apps::find()->where(['we_account_id' => $task['we_account_id'], 'appstore_id' => [12,57], 'status' => 0])->one();
        //$privilege = Staff::getEditprivilege($task['we_account_id'], $app);
        if(!empty($app)){
            $this->qyWechat = new QyWechat(['bgy_app_id' => $app]);

            // 可能未安装通讯录应用
            if (empty($this->qyWechat)) {
                echo 'interface error 接口调用失败，可能未授权通讯录权限，或者可写部门' . PHP_EOL . PHP_EOL;
                $task->result        = '接口调用失败，可能未授权通讯录权限，或者可写部门';
                $task->status        = 3;
                $task->finished_time = time();
                $task->save();
                return false;
            }
        }

        $suffix = substr($task['filepath'], stripos($task['filepath'], '.') + 1);
        try {
            $list = (new Import())->fileData(Yii::getAlias('@siteroot') . $task['filepath'], $suffix);
        } catch (\Exception $e) {
            echo 'file read error 文件读取失败' . PHP_EOL . PHP_EOL;
            $task->result        = '文件读取失败';
            $task->status        = 3;
            $task->finished_time = time();
            $task->save();
            die;
        }
        echo Yii::getAlias('@siteroot') . $task['filepath'] . PHP_EOL . PHP_EOL;
        $count = count($list) - 1;
        $count = $count > 0 ? $count : 0;
        if ($count > 5000) {
            $task->result = '文件记录不能超过5000条';
            echo 'File record cannot be more than 5000' . PHP_EOL . PHP_EOL;
            $task->status = 3;
            $task->count  = $count;
            $task->save();
            return false;
        } else {
            //记录任务count
            $task->status = 1;
            $task->count  = $count;
            $task->save();
        }


        // 如果数据格式不对，停止导入
        if (!in_array('帐号', $list[0])) {
            $result = '数据字段不匹配，未找到“帐号”列';
            echo 'Data field does not match' . PHP_EOL . PHP_EOL;
        }

        if($task['type']=='import_staff' || $task['type']=='import_staff_personnel'){
            if (count($list[0]) < 5) {
                $result = '请按模板添加数据，字段不少于5个';
                echo 'the field is not less than 5' . PHP_EOL . PHP_EOL;
            }
        }

        if($task['type']=='import_staff'){
            $excelTitle = self::$excelField;
        }elseif($task['type']=='import_staff_personnel'){
            $excelTitle = self::$personnelField;
        }else{
            $extList = Extfield::find()->select('ext_9')
                               ->where(['we_account_id'=>$task['we_account_id'],'type'=>'staff_setting'])
                               ->asArray()->column();
            if(empty($extList[0])){
                $result = '不存在扩展字段、请先添加再进行对应数据导入';
                echo 'not exists extfield' . PHP_EOL . PHP_EOL;
            }else{
                //为转换表头提供数据
                $ext_9 = json_decode($extList[0]);
                foreach ($ext_9 as $key => $value) {
                    $tmpTitle[$value->id] = $value->title;
                }
            }
            $unshift = ['we_name'=>'姓名','we_userid'=>'帐号'];
            $excelTitle = array_merge($unshift,$tmpTitle);
        }


        //转换表头
        foreach ($list[0] as $index => $f) {
            foreach ($excelTitle as $findex => $ff) {
                if (trim($f) == $ff) {
                    $field[$findex] = $index;
                }
            }
        }

        // 表头转换失败
        if (empty($field)) {
            $result = '请按模板添加数据，未找到表头字段';
            echo 'the header fields not found' . PHP_EOL . PHP_EOL;
        }
        $this->log($list[0], '异常说明');


        if (!empty($result)) {
            $this->log([$result], '文件不符合要求');
            echo 'File does not meet the requirements' . PHP_EOL . PHP_EOL;
            $task->status          = 3;
            $task->finished_time   = time();
            $task->result          = $result;
            $task->import_log_path = $this->logExcel($task->we_account_id);
            $task->finished        = $count;
            $task->faild_count     = count($this->log);
            $task->save();
            return false;
        }

        $param  = $task['param'] ? Json::decode($task['param']) : [];
        //导入员工基本信息
        if($task['type'] == "import_staff"){
            //初始化已存在checkid , weUserId
            $this->getCheckIdArr($task['we_account_id']);
            $success = $this->handleStaff($task,$field,$list,$param);
        }elseif($task['type'] == "import_staff_personnel"){
            //导入员工人事信息
            $success = $this->handleStaffArchives($task,$field,$list,$param);
        }else{
            //导入员工扩展字段信息
            $tmpKeys = array_keys($tmpTitle);
            $success = $this->handleStaffExtfield($task,$field,$list,$param,$tmpKeys);
        }

        //任务结束
        $faild    = intval($count - $success);

        $logCount = (count($this->log) - 1) > 0 ? (count($this->log) - 1) : 0;
        if ($faild >= 0) {
            $result = "共 " . $count . " 条数据，导入成功 " . $success . "，失败 " . $faild . "，" . $result . '有' . $logCount . '条提示信息 ！';
        }
        $task->status          = 2;
        $task->finished_time   = time();
        $task->result          = $result;
        $task->import_log_path = $this->logExcel($task->we_account_id);
        $task->finished        = $count;
        $task->faild_count     = count($this->log) > 1 ? (count($this->log) - 1) : 0;
        echo 'execution time:' . (time() - $time) . 's' . PHP_EOL . PHP_EOL;
        return $task->save();
    }

    /**
     * [handleStaff 导入员工基本信息数据处理]
     * @param [type] $weAccountId [企业ID]
     * @param [type] $field       [表头信息]
     * @param [type] $list        [需导入的数据]
     */
    public function handleStaff($task,$field,$list,$param){
        $weAccountId = (int)$task['we_account_id'];
        $isInstall = AddressHelper::getIsInstallAddress($weAccountId);
        //职级数据
        $positionLevelList = Helper::arrayToAssoc(AddressHelper::getPositionList($weAccountId), 'name');
        //标签数据
        $tagList = StaffTag::find()->select('we_tag_id,we_tag_name')
                ->where(['we_account_id' => $weAccountId])
                ->indexBy('we_tag_name')->asArray()->all();
        $tagIds = array_keys($tagList);
        $subordinateList = [];
        $firstRow = true;
        foreach ($list as $k => $userInfo) {
            //异常信息
            $useridStr  = '';
            $checkidStr = '';
            $staff      = null;
            $i++;
            if ($firstRow) {
                $firstRow = false;
                continue;
            }
            // 每50条更新一次log
            if ($i % 50 == 0) {
                $task['finished'] = $i;
                $task->save();
            }
            // 处理weUserId 15位以上数字
            $weUserId = $this->getWeUserId($userInfo[$field['we_userid']]);
            if (empty($weUserId)) {
                $this->log($userInfo, '导入失败，userid为空');
                echo 'we_userid is empty' . PHP_EOL . PHP_EOL;
                continue;
            }
            if ($weUserId && in_array($weUserId, $this->weUserIdArr)) {
                $staff     = Staff::findOne(['we_account_id' => $weAccountId, 'we_userid' => $weUserId]);
                $useridStr = '帐号已存在；';
            }
            $this->weUserIdArr[] = $staff['we_userid'];
            //验证考勤机员工编号
            if(!empty($userInfo[$field['check_id']])){
                if(!is_numeric($userInfo[$field['check_id']])){
                    $this->log($userInfo, '考勤机员工编号必须为数字');
                    echo 'check_id must be numeric ' . PHP_EOL . PHP_EOL;
                    continue;
                }
                if($userInfo[$field['check_id']]>2147483647){
                    $this->log($userInfo, '考勤机员工编号不能大于2147483647');
                    echo 'check_id Can not greater than 2147483647' . PHP_EOL . PHP_EOL;
                    continue;
                }
                if(stripos($userInfo[$field['check_id']],'0') === 0){
                    $this->log($userInfo, '考勤机员工编号不能以0开头');
                    echo 'check_id Can not start with 0' . PHP_EOL . PHP_EOL;
                    continue;
                }
            }
            
            if ($userInfo[$field['check_id']] && in_array($userInfo[$field['check_id']], $this->checkIdArr) && ($staff['check_id'] != $userInfo[$field['check_id']])) {
                $checkidStr = '考勤机编号已存在；';
                $this->log($userInfo, '导入失败，' . $checkidStr);
                echo 'check_id already exists' . PHP_EOL . PHP_EOL;
                continue;
            }

            // 处理userid相同是否覆盖 1：覆盖  0:跳过
            if ($staff) {
                $isNew = false;
                if ($param['cover'] == 0) {
                    $success++;
                    $this->log($userInfo, '提示：已跳过此员工，' . $useridStr . $checkidStr);
                    continue;
                }
            } else {
                if($isInstall){
                    $isNew = true;
                    $staff = new Staff();
                }else{
                    $this->log($userInfo, '未安装通讯录，不能新建员工' . $useridStr );
                    continue;
                }
            }
            if($isInstall){
                if (empty($param['dept']) && empty($userInfo[$field['we_department']])) {
                    $this->log($userInfo, '导入失败，部门不能为空');
                    echo 'department is empty' . PHP_EOL . PHP_EOL;
                    continue;
                }
            }
            // 处理导入到此部门下
            if($isInstall){
                $weIdStr = '';
                if (!empty($param['dept'])) {
                    $depInfo = AddressHelper::getRedisDepartment($weAccountId, $param['dept']);
                    if (!empty($depInfo)) {
                        $weIdStr = $param['dept'];
                    }
                }

                if (empty($weIdStr) && !empty($userInfo[$field['we_department']])) {
                    $weIdStr = AddressHelper::getDepartmentWeidList($weAccountId, $userInfo[$field['we_department']], true);
                    $weIdStr = implode(',', array_filter($weIdStr));
                    if (empty($weIdStr)) {
                        //部门创建失败，结束此员工创建
                        $this->log($userInfo, '部门创建失败' . $this->qyWechat->errCode . ':' . $this->qyWechat->errMsg . '；' . $useridStr . $checkidStr);
                        echo 'create department faild' . PHP_EOL . PHP_EOL;
                        continue;
                    }
                }
            }
            //职级处理 position_level_id
            if (!empty($userInfo[$field['position_level_id']])) {
                $positionLevelIds = array_keys($positionLevelList);
                if (in_array($userInfo[$field['position_level_id']], $positionLevelIds)) {
                    $staff->position_level_id = (int) $positionLevelList[$userInfo[$field['position_level_id']]]['id'];
                }
            }

            //直接下属处理 subordinate
            if (!empty($userInfo[$field['subordinate']])) {
                foreach (explode('/', $userInfo[$field['subordinate']]) ?: [] as $v) {
                    if (!empty($v)) {
                        $subordinateList[] = ['user_id' => $this->getWeUserId($v), 'lead_id' => $weUserId];
                    }
                }
            }
            //标签 we_tag
            if($isInstall){
                if (!empty($userInfo[$field['we_tag']])) {
                    $postTagList = explode('/', $userInfo[$field['we_tag']]);
                    $tmpTag      = [];
                    foreach ($postTagList ?: [] as $v) {
                        if (in_array($v, $tagIds)) {
                            $tmpTag[] = (int) $tagList[$v]['we_tag_id'];
                        }
                    }
                    if (count($tmpTag) > 0) {
                        $staff->we_tag = implode(',', $tmpTag);
                    }
                }
            }
            if($isInstall){
                $staff->we_account_id = $weAccountId;
                $staff->we_email      = $userInfo[$field['we_email']] ?: '';
                $staff->we_userid     = $weUserId ? (string) $weUserId : '';
                $staff->we_mobile     = $userInfo[$field['we_mobile']] ? (string) $userInfo[$field['we_mobile']] : '';
                $staff->we_department = (string) $weIdStr;
                $staff->we_gender     = ($userInfo[$field['we_gender']] == '男' ? 1 : (($userInfo[$field['we_gender']] == '女') ? 2 : 0));
                $staff->we_name       = $userInfo[$field['we_name']] ? (string) $userInfo[$field['we_name']] : '';
                $staff->we_position   = $userInfo[$field['we_position']] ? (string) $userInfo[$field['we_position']] : '';
                $staff->we_weixinid   = $userInfo[$field['we_weixinid']] ? (string) $userInfo[$field['we_weixinid']] : '';
                
                $staff->pinyin        = Helper::getPinYin($userInfo[$field['we_name']]);
                $staff->letter        = Helper::getPinYin($userInfo[$field['we_name']], 'letter');
            }

            
            $staff->we_tel        = $userInfo[$field['we_tel']] ? (string) $userInfo[$field['we_tel']] : '';
            
            $staff->english_name  = $userInfo[$field['english_name']] ? (string) $userInfo[$field['english_name']] : '';

            $staff->address       = $userInfo[$field['address']] ? (string) $userInfo[$field['address']] : '';
            $staff->lead_id       = $userInfo[$field['lead_id']] ? (string) $userInfo[$field['lead_id']] : '';
            $staff->create_time = $staff->create_time ?: time();
            $staff->create_ip   = $staff->create_ip ?: Helper::getIp();
            $staff->check_id   = $userInfo[$field['check_id']] ?: '';

            //去空格
            $staff->we_userid   = trim($staff->we_userid);
            $staff->we_name     = trim($staff->we_name);
            $staff->we_mobile   = trim($staff->we_mobile);
            $staff->we_email    = trim($staff->we_email);
            $staff->we_weixinid = trim($staff->we_weixinid);

            // 处理是否同步到企业号，暂时未提供前台选择是否同步到企业号，默认同步到企业号
            $data = [
                "userid"     => $staff->we_userid,
                "name"       => $staff->we_name,
                "department" => explode(',', $staff->we_department),
                "position"   => $staff->we_position,
                "mobile"     => (string) $staff->we_mobile,
                "gender"     => $staff->we_gender, //性别。gender=0表示男，=1表示女
                //"tel" => (string)$staff->we_tel,
                "email"      => $staff->we_email,
                "weixinid"   => $staff->we_weixinid,
            ];
            if($isInstall){
                if ($isNew) {
                    if(empty($data['mobile']) && empty($data['mobile']) && empty($data['mobile'])){
                        $this->log($userInfo, '微信号,手机号,邮箱至少填写一项');
                        echo 'create staff weixinid/mobile/email can not null' . PHP_EOL . PHP_EOL;
                        continue;
                    }else{
                        $upFlag             = $this->qyWechat->createUser($data);
                        $staff->create_time = time();
                        $staff->create_ip   = Helper::getIp();
                    }
                } else {
                    if($staff->we_status==0 && $staff->staff_from=='app'){
                        if(empty($data['mobile']) && empty($data['mobile']) && empty($data['mobile'])){
                            $this->log($userInfo, '微信号,手机号,邮箱至少填写一项');
                            echo 'create staff weixinid/mobile/email can not null' . PHP_EOL . PHP_EOL;
                            continue;
                        }else{
                            $upFlag = $this->qyWechat->createUser($data);
                        }
                    }else{
                        $upFlag = $this->qyWechat->updateUser($data);
                    }
                    $staff->update_time = time();
                }
                if (!$upFlag) {
                    $this->log($userInfo, '添加或者更新员工失败' . $this->qyWechat->errCode . '：' . $this->qyWechat->errMsg);
                    echo 'create/update staff faild' . PHP_EOL . PHP_EOL;
                    continue;
                }
            }
            $staff->toQy = false;
            if ($staff->save()) {
                $success++;
                // 处理是否发送邀请
                if ($param['invite'] == 1) {
                    $invFlag = $this->qyWechat->inviteuser($staff->we_userid);
                    if ($invFlag) {
                        $this->log($userInfo, '邀请发送失败');
                        continue;
                    }
                }
                //处理weUserId、checkid重复提示
                $str = '';
                // if (!empty($useridStr)) {
                //     $str .= '提示：已覆盖员工；' . $useridStr;
                // }
                if (!empty($checkidStr)) {
                    $str .= $checkidStr;
                }
                if (!empty($str)) {
                    $this->log($userInfo, $str);
                }
            } else {
                $this->log($userInfo, '保存失败，' . json_encode($staff->getErrors()));
                continue;
            }
        }
        
        //更新员工的lead_id（直系下属）
        if (count($subordinateList) > 0) {
            foreach ($subordinateList as $v) {
                if (count($v) > 0) {
                    $emptyStaff = Staff::findOne(['we_account_id' => $weAccountId, 'we_userid' => $v['user_id']]);
                    if ($emptyStaff) {
                        $emptyStaff->lead_id = $v['lead_id'];
                        $emptyStaff->toQy    = false; //不更新到微信
                        $emptyStaff->save();
                    }
                }
            }
        }

        return $success;
    }



    /**
     * [handleStaffArchives 导入员工人事信息数据处理]
     * @param [type] $weAccountId [企业ID]
     * @param [type] $field       [表头信息]
     * @param [type] $list        [需导入的数据]
     */
    public function handleStaffArchives($task,$field,$list,$param){
        $weAccountId = (int)$task['we_account_id'];
        $firstRow = true;
        foreach ($list as $k => $userInfo) {
            $i++;
            if ($firstRow) {
                $firstRow = false;
                continue;
            }
            // 每50条更新一次log
            if ($i % 50 == 0) {
                $task['finished'] = $i;
                $task->save();
            }
            // 处理weUserId 15位以上数字
            $weUserId = $this->getWeUserId($userInfo[$field['we_userid']]);
            if (empty($weUserId)) {
                $this->log($userInfo, '导入失败，userid为空');
                echo 'we_userid is empty' . PHP_EOL . PHP_EOL;
                continue;
            }
            $staff = Staff::findOne(['we_account_id' => $weAccountId, 'we_userid' => $weUserId]);
            if (!$staff) {
                $this->log($userInfo, '导入失败，员工不存在');
                continue;
            }else{
                if ($param['cover'] == 0) {
                    $success++;
                    $this->log($userInfo, '提示：不覆盖，已跳过此员工');
                    continue;
                }
            }

            //人事状态 status
            $statusArr     = ['已转正' => 0, '试用期' => 1, '兼职' => 2, '已离职' => 3];
            $staff->status = (int) $statusArr[$userInfo[$field['status']]];

            $staff->we_account_id = $weAccountId;
            // $staff->we_name       = trim($userInfo[$field['we_name']]) ? trim($userInfo[$field['we_name']]) : '';
            $staff->update_time   = time();
            $staff->create_ip     = $staff->create_ip ?: Helper::getIp();
            $staff->entry_time    = (int) strtotime($userInfo[$field['entry_time']]);
            $staff->positive_time = (int) strtotime($userInfo[$field['positive_time']]);
            $staff->quite_time    = (int) strtotime($userInfo[$field['quite_time']]);
            $staff->quite_reason  = (string) $userInfo[$field['quite_reason']];
            $staff->back_time     = (int) strtotime($userInfo[$field['back_time']]);
            $staff->work_time     = (int) strtotime($userInfo[$field['work_time']]);
            $staff->adjust_work_year  = (float) number_format($userInfo[$field['adjust_work_year']], 2, '.', '');

            $staff->bank_card     = $userInfo[$field['bank_card']] ? (string) $userInfo[$field['bank_card']] : '';
            $staff->bank_name     = $userInfo[$field['bank_name']] ? (string) $userInfo[$field['bank_name']] : '';
            $staff->birthday_g = (int) strtotime($userInfo[$field['birthday_g']]);
            $staff->birthday_l = (int) strtotime($userInfo[$field['birthday_l']]);
            $staff->idcode        = (string)$userInfo[$field['idcode']]?:'';

            $staffArchives = StaffArchives::findOne(['we_account_id'=>$weAccountId,'staff_id'=>$staff->id]);
            if(!$staffArchives){
                $staffArchives = new StaffArchives();
                $staffArchives->staff_id = $staff->id;
                $staffArchives->we_account_id = $weAccountId;
            }
            $staffArchives->first_contract_begin_date = (int) strtotime($userInfo[$field['first_contract_begin_date']]);
            $staffArchives->first_contract_end_date   = (int) strtotime($userInfo[$field['first_contract_end_date']]);
            $staffArchives->now_contract_begin_date   = (int) strtotime($userInfo[$field['now_contract_begin_date']]);
            $staffArchives->now_contract_end_date     = (int) strtotime($userInfo[$field['now_contract_end_date']]);

            $staffArchives->now_contract_period       = (int) $userInfo[$field['now_contract_period']]?round($userInfo[$field['now_contract_period']]/0.5):0;
            $staffArchives->renewal_times             =  (int)$userInfo[$field['renewal_times']]?:0;

            $staffArchives->hire_source = (string)$userInfo[$field['hire_source']]?:'';
            $staffArchives->recommended = (string)$userInfo[$field['recommended']]?:'';

            $degreeArr    = ['大专' => 1, '本科' => 2, '硕士' => 3, '博士' => 4, '博士后' => 5, '其它' => 6];
            $staffArchives->highest_degree = (int)$degreeArr[$userInfo[$field['highest_degree']]];
            $staffArchives->graduation_school = (string)$userInfo[$field['graduation_school']]?:'';
            $staffArchives->major = (string)$userInfo[$field['major']]?:'';
            $staffArchives->nationality = (string)$userInfo[$field['nationality']]?:'';
            $staffArchives->political_state = (string)$userInfo[$field['political_state']]?:'';

            $marriageArr   = ['未选择' => 0, '未婚' => 1, '已婚' => 2, '离异' => 3];
            $staffArchives->marriage_status = (int) $marriageArr[$userInfo[$field['marriage_status']]];
            $staffArchives->emergency_name = (string)$userInfo[$field['emergency_name']]?:'';
            $staffArchives->emergency_mobile = (string)$userInfo[$field['emergency_mobile']]?:'';
            if(!$staffArchives->save()){
                $this->log($userInfo, '保存失败，' . json_encode($staffArchives->getErrors()));
                continue;
            }

            $staff->toQy = false;
            if ($staff->save()) {
                $success++;
            } else {
                $this->log($userInfo, '保存失败，' . json_encode($staff->getErrors()));
                continue;
            }
        }
        return $success;
    }


    /**
     * [handleStaffArchives 导入员工扩展字段信息数据处理]
     * @param [type] $weAccountId [企业ID]
     * @param [type] $field       [表头信息]
     * @param [type] $list        [需导入的数据]
     * @param [type] $param       [是否覆盖参数]
     * @param [type] $fieldKeys   [扩展字段对应id值]
     */
    public function handleStaffExtfield($task,$field,$list,$param,$fieldKeys){
        $weAccountId = (int)$task['we_account_id'];
        $firstRow = true;
        foreach ($list as $k => $userInfo) {
            $i++;
            if ($firstRow) {
                $firstRow = false;
                continue;
            }
            // 每50条更新一次log
            if ($i % 50 == 0) {
                $task['finished'] = $i;
                $task->save();
            }
            // 处理weUserId 15位以上数字
            $weUserId = $this->getWeUserId($userInfo[$field['we_userid']]);
            if (empty($weUserId)) {
                $this->log($userInfo, '导入失败，userid为空');
                echo 'we_userid is empty' . PHP_EOL . PHP_EOL;
                continue;
            }
            $staff = Staff::findOne(['we_account_id' => $weAccountId, 'we_userid' => $weUserId]);
            if (!$staff) {
                $this->log($userInfo, '导入失败，员工不存在');
                continue;
            }
            if ($param['cover'] == 0) {
                $success++;
                $this->log($userInfo, '提示：不覆盖，已跳过此员工');
                continue;
            }

            foreach ($fieldKeys as $key => $value) {
                $ext[$value] = $userInfo[$field[$value]];
            }

            $staff->ext_9         = Json::encode($ext); //扩展字段
            $staff->update_time   = time();
            $staff->create_ip     = $staff->create_ip ?: Helper::getIp();
            $staff->toQy = false;
            if ($staff->save()) {
                $success++;
            } else {
                $this->log($userInfo, '保存失败，' . current($staff->getErrors())[0]);
                continue;
            }
        }
        return $success;
    }


    /**
     * [getFormalTime 时间格式转换]
     * @param  [type] $time [2016/11/11 || 2016-02-02]
     * @return [type]       [description]
     */
    public function getFormalTime($time) {
        if (!empty($time)) {
            $time = strtotime(PHPExcel_Style_NumberFormat::toFormattedString($time, 'yyyy-mm-dd'));
        }
        return $time;
    }

    /**
     * 根据路径获取部门we_id
     * @param int $weId 默认部门
     * @param string 部门路径
     */
    public function getDeptWeId($weAccountId, $weId, $deptName) {
        $deptName = trim($deptName);
        if (empty($this->weIdArr)) {
            $this->weIdArr = (new Query())->select('we_id,we_parentid,we_name,all_we_parentids,level')->from('{{%department}}')->where(['we_account_id' => $weAccountId])->indexBy('we_id')->all();
            // 如果一个部门也没有， 则同步一次，创建同步任务,同时结束运行
            if (empty($this->weIdArr)) {
                (new Department())->createSyncTask($weAccountId);
                die;
            }
        }
        if (empty($this->nameArr)) {
            foreach ($this->weIdArr ?: [] as $t) {
                $stemp = explode(',', $t['all_we_parentids']);
                unset($stemp[count($stemp) - 1]);
                unset($stemp[0]);
                foreach ($stemp as $st) {
                    $tempName .= '/' . $this->weIdArr[$st]['we_name'];
                    $tempDeptid = $st;
                }
                $this->nameArr[$tempName] = $st;
                unset($tempName);
                unset($tempDeptid);
            }
        }
        if (strlen($deptName) > 0) {
            if ($deptName == '/') {
                return 1;
            } else if (strpos($deptName, '/') === 0) {
                $deptName;
            } else {
                $tempAllWeArr = explode(',', $this->weIdArr[$weId]['all_we_parentids']);
                foreach ($tempAllWeArr as $v) {
                    if (empty($v)) {
                        continue;
                    }

                    $tname .= '/' . $this->weIdArr[$v]['we_name'];
                }
                $deptName = $tname . '/' . $deptName;
            }
            $returnid = $this->nameArr[$deptName];
            // 没找到部门，就创建部门
            if (empty($returnid)) {
                $dtemp = explode('/', $deptName);
                $dname = '';
                foreach ($dtemp as $v) {
                    if (empty($v)) {
                        continue;
                    }

                    if ($dname) {
                        // 父部门id
                        $level        = $this->weIdArr[$this->nameArr[$dname]]['level'] ?: 0;
                        $parentid     = $this->weIdArr[$this->nameArr[$dname]]['we_id'] ?: 0;
                        $allParentIds = $this->weIdArr[$this->nameArr[$dname]]['all_we_parentids'] ?: '';
                    } else {
                        $level        = 0;
                        $parentid     = 1;
                        $allParentIds = ',1,';
                    }
                    // 部门路径错误,本条记录失败
                    if ($parentid <= 0) {
                        return false;
                    }
                    $dname .= '/' . $v;
                    if (!$this->nameArr[$dname]) {
                        // 创建微信部门成功后创建办公逸部门
                        $data['name']     = $v;
                        $data['parentid'] = $parentid ?: 0;
                        $result           = $this->qyWechat->createDepartment($data);
                        //微信创建成功 ，再创建本地部门
                        if ($result['id']) {
                            $department                   = new Department();
                            $department->we_account_id    = $weAccountId;
                            $department->we_id            = $result['id'];
                            $department->we_name          = $v;
                            $department->we_parentid      = $parentid;
                            $department->create_time      = time();
                            $department->level            = $level + 1;
                            $department->all_we_parentids = $allParentIds . $result['id'] . ',';
                            if ($department->save()) {
                                // 将新增部门加入$this->nameArr
                                $this->nameArr[$dname] = $department->we_id;
                                // 将新增部门加入$this->weIdArr
                                $this->weIdArr[$department->we_id]['we_id']            = $department->we_id;
                                $this->weIdArr[$department->we_id]['we_parentid']      = $department->we_parentid;
                                $this->weIdArr[$department->we_id]['we_name']          = $department->we_name;
                                $this->weIdArr[$department->we_id]['all_we_parentids'] = $department->all_we_parentids;
                                //设置返回的we_id
                                $returnid = $result['id'];
                            } else {
                                return false;
                            }
                        }
                    }
                }
                unset($dtemp);
            }
        } else {
            $returnid = $weId;
        }
        return $returnid ?: false;
    }

    /**
     * 创建导入任务
     */
    public function createTask($aid, $taskId) {
        if (!$aid) {
            return false;
        }
        $message    = ['we_account_id' => $aid, 'task_id' => $taskId];
        $message    = json_encode($message);
        $exName     = 'exchangeImportAddress';
        $quName     = 'queueImportAddress';
        $routingKey = 'importAddress';
        Yii::$app->amqp->init();
        Yii::$app->amqp->declareExchange($exName, $type = 'direct', $passive = false, $durable = true, $auto_delete = false);
        Yii::$app->amqp->declareQueue($quName, false, false, false, false);
        Yii::$app->amqp->bindQueueExchanger($quName, $exName, $routingKey);
        Yii::$app->amqp->publish_message($message, $exName, $routingKey, $content_type = '', $app_id = '');
        Yii::$app->amqp->closeConnection();
    }

    /**
     * 执行所有导入任务列表
     */
    public function exeAllTask($weAccountId = 0) {
        if ($weAccountId) {
            $where['we_account_id'] = $weAccountId;
        }
        $where['status'] = 0;
        $i               = 0;
        do {
            $i++;
            if ($i > 50) {
                break;
            }

            $task = self::findOne($where);
            if ($task) {
                $flag   = true;
                $return = $task->processTask();
            } else {
                $flag = false;
            }
        } while ($flag);
    }

    /**
     * 记录导入日志
     */
    public function log($userinfo, $errmsg) {
        //$userinfo['entry_time'] = $userinfo['entry_time']>0 ? date('Y-m-d',$userinfo['entry_time']):'';
        //$userinfo['quite_time'] = $userinfo['quite_time']>0 ? date('Y-m-d',$userinfo['quite_time']):'';
        //$userinfo['birthday_g'] = $userinfo['birthday_g']>0 ? date('Y-m-d',$userinfo['birthday_g']):'';
        $userinfo['errmsg'] = $errmsg;
        $this->log[]        = $userinfo;
    }

    /**
     * 生成excel log
     */
    public function logExcel($weAccountId) {
        $filename = rand(1000, 9999) . time() . '.xls';
        $filepath = Helper::getUploadsPath($weAccountId) . 'import/result/';
        $abspath  = Yii::getAlias('@siteroot') . $filepath;
        if (!is_dir($abspath)) {
            FileHelper::createDirectory($abspath);
        }
        (new Import())->export($filename, $this->log, $abspath);
        return $filepath . $filename;
    }

    /**
     * 获取所有已存在checkid  we_userid
     */
    public function getCheckIdArr($weAccountId) {
        $list = Staff::find()->select('we_userid,check_id')->where("we_account_id={$weAccountId}")->all();
        $list = $list ?: [];
        foreach ($list as $v) {
            if (!empty($v['check_id'])) {
                $this->checkIdArr[] = $v['check_id'];
            }
            $this->weUserIdArr[] = $v['we_userid'];
        }
    }

    public function getWeUserId($weUserId) {
        $temp = trim($weUserId, "'");
        if (strlen($temp) >= 15 && is_numeric($temp)) {
            return $temp;
        }
        return $weUserId;
    }

    public function rules() {
        return [
            [['we_account_id', 'user_id', 'appstore_id', 'status', 'create_time', 'finished_time'], 'integer'],
            [['type'], 'string', 'max' => 32],
            [['filepath', 'import_log_path'], 'string', 'max' => 255],
            [['result'], 'string', 'max' => 512],
            [['param'], 'string', 'max' => 1000],
        ];
    }

    public function attributeLabels() {
        return [
            'id'              => '主键ID',
            'we_account_id'   => '企业号id',
            'user_id'         => '管理员id',
            'appstore_id'     => '模块id',
            'type'            => '类型',
            'status'          => '状态 0 未开始 1正在导入 2 导入成功 3部分导入失败',
            'create_time'     => '创建时间',
            'finished_time'   => '完成时间',
            'filepath'        => '上传的文件保存路劲',
            'import_log_path' => '导入结果log文件（excel）',
            'param'           => '导入的参数',
            'result'          => '导入结果文字说明',
        ];
    }
}
