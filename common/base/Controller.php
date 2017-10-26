<?php
/**
 * 控制器基类
 *
 */

namespace common\base;

use Yii;

class Controller extends \yii\web\Controller
{
    /**
     * 渲染为标准JSON格式
     * @param  mixed $data 如果为数组或对象, 
     *                         如果只有2个值, 且第一个为数字第二个字符串将分别作为errno和errmsg,
     *                         否则将除errno和errmsg的值赋值给data
     *                     如果为整数作为errno
     *                     如果为字符串作为errmsg
     * @param  array $param 将参数附加到一维数组
     * @return json  返回必带errno和errmsg可能带data的json标准字符串
     */
    public function renderJson($data,$param=[]) {

        $json=is_array($param)?$param:[];

        if(is_object($data)){
            $data = Helper::objToArr($data);
        }
        if(is_array($data)){
            if(count($data)==2 && is_numeric($data[0]) && is_string($data[1])){
                $json['errno'] = $data[0];
                $json['errmsg'] = $data[1];
                unset($data);
            }

            if(isset($data['errno'])){
                $json['errno'] = $data['errno'];
                unset($data['errno']);
            }

            if(isset($data['errmsg'])){
                $json['errmsg'] = $data['errmsg'];
                unset($data['errmsg']);
            }

            if(!empty($data)){
                 $json['data'] = $data;
            }
        }elseif(is_int($data)){
            $json['errno'] = $data;
        }elseif(is_string($data)){
            $json['errmsg'] = $data;
        }

        $json['errno'] = isset($json['errno'])?$json['errno']:0;
        $json['errmsg'] = isset($json['errmsg'])?$json['errmsg']:'ok';

        echo json_encode($json);

        $response = Yii::$app->getResponse();
        $response->format = $response::FORMAT_JSON;
        $response->send();
        Yii::$app->end();
    }
}
