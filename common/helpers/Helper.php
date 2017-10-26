<?php
/**
 * 公共助手类
 * 功能 : 放置常用的函数
 * 注意 : 只能包含静态函数, 每个函数必须有标准的块注释
 *
 * @author tasal<fei.he@pcstars.com>
 * @version $Id: Helper.php 42883 2017-06-22 08:41:22Z A1262 $
 */

namespace common\helpers;

use common\base\Cache;
use common\base\Query;
use common\librarys\Crypt;
use common\librarys\MobileDetect;
use common\librarys\QyWechat;
use common\models\Logs;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Json;

class Helper {

    /**
     * 字符串截取，支持中文和其他编码
     * @static
     * @access public
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * @return string
     */
    public static function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
            if (false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }

        return (strlen($slice) < strlen($str) && $suffix) ? $slice . '...' : $slice;
    }

    /**
     * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
     * @param string $len 长度
     * @param string $type 字串类型
     * 0 字母 1 数字 其它 混合
     * @param string $addChars 额外字符
     * @return string
     */
    public static function randString($len = 6, $type = '', $addChars = '') {
        $str = '';
        switch ($type) {
        case 0:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1:
            $chars = str_repeat('0123456789', 3);
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3:
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" . $addChars;
            break;
        default:
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
        }
        if ($len > 10) {
//位数过长重复字符串一定次数
            $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
        }
        if ($type != 4) {
            $chars = str_shuffle($chars);
            $str = substr($chars, 0, $len);
        } else {
            // 中文随机字
            for ($i = 0; $i < $len; $i++) {
                $str .= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
            }
        }
        return $str;
    }

    /**
     * 字节格式化 把字节数格式为 B K M G T 描述的大小
     * @param string 字节数
     * @return string
     */
    public static function byteFormat($bytes) {
        $sizetext = array(" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $sizetext[$i];
    }

    /**
     * 检查字符串是否是UTF8编码
     * @param string $string 字符串
     * @return Boolean
     */
    public static function checkCharset($string, $charset = "UTF-8") {
        if ($string == '') {
            return;
        }

        $check = preg_match('%^(?:
                                [\x09\x0A\x0D\x20-\x7E] # ASCII
                                | [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
                                | \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
                                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
                                | \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
                                | \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
                                | [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
                                | \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
                                )*$%xs', $string);

        return $charset == "UTF-8" ? ($check == 1 ? $string : iconv('gb2312', 'utf-8', $string)) : ($check == 0 ? $string : iconv('utf-8', 'gb2312', $string));
    }

    /**
     * 代码加亮
     * @param String  $str 要高亮显示的字符串 或者 文件名
     * @param Boolean $show 是否输出
     * @return String
     */
    public static function highlightCode($str, $show = false) {
        if (file_exists($str)) {
            $str = file_get_contents($str);
        }
        $str = stripslashes(trim($str));
        // The highlight string function encodes and highlights
        // brackets so we need them to start raw
        $str = str_replace(array('&lt;', '&gt;'), array('<', '>'), $str);

        // Replace any existing PHP tags to temporary markers so they don't accidentally
        // break the string out of PHP, and thus, thwart the highlighting.

        $str = str_replace(array('&lt;?php', '?&gt;', '\\'), array('phptagopen', 'phptagclose', 'backslashtmp'), $str);

        // The highlight_string function requires that the text be surrounded
        // by PHP tags.  Since we don't know if A) the submitted text has PHP tags,
        // or B) whether the PHP tags enclose the entire string, we will add our
        // own PHP tags around the string along with some markers to make replacement easier later

        $str = '<?php //tempstart' . "\n" . $str . '//tempend ?>'; // <?
        // All the magic happens here, baby!
        $str = highlight_string($str, TRUE);

        // Prior to PHP 5, the highlight function used icky font tags
        // so we'll replace them with span tags.
        if (abs(phpversion()) < 5) {
            $str = str_replace(array('<font ', '</font>'), array('<span ', '</span>'), $str);
            $str = preg_replace('#color="(.*?)"#', 'style="color: \\1"', $str);
        }

        // Remove our artificially added PHP
        $str = preg_replace("#\<code\>.+?//tempstart\<br />\</span\>#is", "<code>\n", $str);
        $str = preg_replace("#\<code\>.+?//tempstart\<br />#is", "<code>\n", $str);
        $str = preg_replace("#//tempend.+#is", "</span>\n</code>", $str);

        // Replace our markers back to PHP tags.
        $str = str_replace(array('phptagopen', 'phptagclose', 'backslashtmp'), array('&lt;?php', '?&gt;', '\\'), $str); //<?
        $line = explode("<br />", rtrim(ltrim($str, '<code>'), '</code>'));
        $result = '<div class="code"><ol>';
        foreach ($line as $key => $val) {
            $result .= '<li>' . $val . '</li>';
        }
        $result .= '</ol></div>';
        $result = str_replace("\n", "", $result);
        if ($show !== false) {
            echo ($result);
        } else {
            return $result;
        }
    }

    /**
     * 输出安全html
     * @param String  $text HTML文本
     * @param String $tags 允许的html标签
     * @return String
     */
    public static function h($text, $tags = null) {
        $text = trim($text);
        //完全过滤注释
        $text = preg_replace('/<!--?.*-->/', '', $text);
        //完全过滤动态代码
        $text = preg_replace('/<\?|\?' . '>/', '', $text);
        //完全过滤js
        $text = preg_replace('/<script?.*\/script>/', '', $text);

        $text = str_replace('[', '&#091;', $text);
        $text = str_replace(']', '&#093;', $text);
        $text = str_replace('|', '&#124;', $text);
        //过滤换行符
        $text = preg_replace('/\r?\n/', '', $text);
        //br
        $text = preg_replace('/<br(\s\/)?' . '>/i', '[br]', $text);
        $text = preg_replace('/<p(\s\/)?' . '>/i', '[br]', $text);
        $text = preg_replace('/(\[br\]\s*){10,}/i', '[br]', $text);
        //过滤危险的属性，如：过滤on事件lang js
        while (preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i', $text, $mat)) {
            $text = str_replace($mat[0], $mat[1], $text);
        }
        while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
            $text = str_replace($mat[0], $mat[1] . $mat[3], $text);
        }
        if (empty($tags)) {
            $tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
        }
        //允许的HTML标签
        $text = preg_replace('/<(' . $tags . ')( [^><\[\]]*)>/i', '[\1\2]', $text);
        $text = preg_replace('/<\/(' . $tags . ')>/Ui', '[/\1]', $text);
        //过滤多余html
        $text = preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i', '', $text);
        //过滤合法的html标签
        while (preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i', $text, $mat)) {
            $text = str_replace($mat[0], str_replace('>', ']', str_replace('<', '[', $mat[0])), $text);
        }
        //转换引号
        while (preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i', $text, $mat)) {
            $text = str_replace($mat[0], $mat[1] . '|' . $mat[3] . '|' . $mat[4], $text);
        }
        //过滤错误的单个引号
        while (preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i', $text, $mat)) {
            $text = str_replace($mat[0], str_replace($mat[1], '', $mat[0]), $text);
        }
        //转换其它所有不合法的 < >
        $text = str_replace('<', '&lt;', $text);
        $text = str_replace('>', '&gt;', $text);
        $text = str_replace('"', '&quot;', $text);
        //反转换
        $text = str_replace('[', '<', $text);
        $text = str_replace(']', '>', $text);
        $text = str_replace('|', '"', $text);
        //过滤多余空格
        $text = str_replace('  ', ' ', $text);
        return $text;
    }

    /**
     * UBB代码解析
     * @param String  $Text 要解析的UBB文本
     * @return String
     */
    public static function ubb($Text) {
        $Text = trim($Text);
        //$Text=htmlspecialchars($Text);
        $Text = preg_replace("/\\t/is", "  ", $Text);
        $Text = preg_replace("/\[h1\](.+?)\[\/h1\]/is", "<h1>\\1</h1>", $Text);
        $Text = preg_replace("/\[h2\](.+?)\[\/h2\]/is", "<h2>\\1</h2>", $Text);
        $Text = preg_replace("/\[h3\](.+?)\[\/h3\]/is", "<h3>\\1</h3>", $Text);
        $Text = preg_replace("/\[h4\](.+?)\[\/h4\]/is", "<h4>\\1</h4>", $Text);
        $Text = preg_replace("/\[h5\](.+?)\[\/h5\]/is", "<h5>\\1</h5>", $Text);
        $Text = preg_replace("/\[h6\](.+?)\[\/h6\]/is", "<h6>\\1</h6>", $Text);
        $Text = preg_replace("/\[separator\]/is", "", $Text);
        $Text = preg_replace("/\[center\](.+?)\[\/center\]/is", "<center>\\1</center>", $Text);
        $Text = preg_replace("/\[url=http:\/\/([^\[]*)\](.+?)\[\/url\]/is", "<a href=\"http://\\1\" target=_blank>\\2</a>", $Text);
        $Text = preg_replace("/\[url=([^\[]*)\](.+?)\[\/url\]/is", "<a href=\"http://\\1\" target=_blank>\\2</a>", $Text);
        $Text = preg_replace("/\[url\]http:\/\/([^\[]*)\[\/url\]/is", "<a href=\"http://\\1\" target=_blank>\\1</a>", $Text);
        $Text = preg_replace("/\[url\]([^\[]*)\[\/url\]/is", "<a href=\"\\1\" target=_blank>\\1</a>", $Text);
        $Text = preg_replace("/\[img\](.+?)\[\/img\]/is", "<img src=\\1>", $Text);
        $Text = preg_replace("/\[color=(.+?)\](.+?)\[\/color\]/is", "<font color=\\1>\\2</font>", $Text);
        $Text = preg_replace("/\[size=(.+?)\](.+?)\[\/size\]/is", "<font size=\\1>\\2</font>", $Text);
        $Text = preg_replace("/\[sup\](.+?)\[\/sup\]/is", "<sup>\\1</sup>", $Text);
        $Text = preg_replace("/\[sub\](.+?)\[\/sub\]/is", "<sub>\\1</sub>", $Text);
        $Text = preg_replace("/\[pre\](.+?)\[\/pre\]/is", "<pre>\\1</pre>", $Text);
        $Text = preg_replace("/\[email\](.+?)\[\/email\]/is", "<a href='mailto:\\1'>\\1</a>", $Text);
        $Text = preg_replace("/\[colorTxt\](.+?)\[\/colorTxt\]/eis", "color_txt('\\1')", $Text);
        $Text = preg_replace("/\[emot\](.+?)\[\/emot\]/eis", "emot('\\1')", $Text);
        $Text = preg_replace("/\[i\](.+?)\[\/i\]/is", "<i>\\1</i>", $Text);
        $Text = preg_replace("/\[u\](.+?)\[\/u\]/is", "<u>\\1</u>", $Text);
        $Text = preg_replace("/\[b\](.+?)\[\/b\]/is", "<b>\\1</b>", $Text);
        $Text = preg_replace("/\[quote\](.+?)\[\/quote\]/is", " <div class='quote'><h5>引用:</h5><blockquote>\\1</blockquote></div>", $Text);
        $Text = preg_replace("/\[code\](.+?)\[\/code\]/eis", "highlight_code('\\1')", $Text);
        $Text = preg_replace("/\[php\](.+?)\[\/php\]/eis", "highlight_code('\\1')", $Text);
        $Text = preg_replace("/\[sig\](.+?)\[\/sig\]/is", "<div class='sign'>\\1</div>", $Text);
        $Text = preg_replace("/\\n/is", "<br/>", $Text);
        return $Text;
    }

    /**
     * 移除XSS
     * @param String  $val
     * @return String
     */
    public static function remove_xss($val) {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=@avascript:alert('XSS')>
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
            // @ @ search for the hex values
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
        }

        // now the only remaining whitespace attacks are \t, \n, and \r
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }

    /**
     * 把返回的数据集转换成Tree
     * @access public
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     */
    public static function listToTree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = &$list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * 对查询结果集进行排序
     * @access public
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param array $sortby 排序类型
     * asc正向排序 desc逆向排序 nat自然排序
     * @return array
     */
    public static function listSortBy($list, $field, $sortby = 'asc') {
        if (is_array($list)) {
            $refer = $resultSet = array();
            foreach ($list as $i => $data) {
                $refer[$i] = &$data[$field];
            }

            switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc': // 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
            }
            foreach ($refer as $key => $val) {
                $resultSet[] = &$list[$key];
            }

            return $resultSet;
        }
        return false;
    }

    /**
     * 在数据列表中搜索
     * @access public
     * @param array $list 数据列表
     * @param mixed $condition 查询条件
     * 支持 array('name'=>$value) 或者 name=$value
     * @return array
     */
    public static function listSearch($list, $condition) {
        if (is_string($condition)) {
            parse_str($condition, $condition);
        }

        // 返回的结果集合
        $resultSet = array();
        foreach ($list as $key => $data) {
            $find = false;
            foreach ($condition as $field => $value) {
                if (isset($data[$field])) {
                    if (0 === strpos($value, '/')) {
                        $find = preg_match($value, $data[$field]);
                    } elseif ($data[$field] == $value) {
                        $find = true;
                    }
                }
            }
            if ($find) {
                $resultSet[] = &$list[$key];
            }

        }
        return $resultSet;
    }

    /**
     * 删除目录及目录下所有文件或删除指定文件
     * @param str $path   待删除目录路径
     * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
     * @return bool 返回删除状态
     */
    public static function delDirAndFile($path, $delDir = FALSE) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
                }

            }
            closedir($handle);
            if ($delDir) {
                return rmdir($path);
            }

        } else {
            if (file_exists($path)) {
                return unlink($path);
            } else {
                return FALSE;
            }
        }
    }

    /**
     * 将一个字符串部分字符用*替代隐藏
     * @param string    $string   待转换的字符串
     * @param int       $bengin   起始位置，从0开始计数，当$type=4时，表示左侧保留长度
     * @param int       $len      需要转换成*的字符个数，当$type=4时，表示右侧保留长度
     * @param int       $type     转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
     * @param string    $glue     分割符
     * @return string   处理后的字符串
     */
    public static function hideStr($string, $bengin = 0, $len = 4, $type = 0, $glue = "@") {
        if (empty($string)) {
            return false;
        }

        $array = array();
        if ($type == 0 || $type == 1 || $type == 4) {
            $strlen = $length = mb_strlen($string);
            while ($strlen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strlen, "utf8");
                $strlen = mb_strlen($string);
            }
        }
        switch ($type) {
        case 1:
            $array = array_reverse($array);
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i])) {
                    $array[$i] = "*";
                }

            }
            $string = implode("", array_reverse($array));
            break;
        case 2:
            $array = explode($glue, $string);
            $array[0] = hideStr($array[0], $bengin, $len, 1);
            $string = implode($glue, $array);
            break;
        case 3:
            $array = explode($glue, $string);
            $array[1] = hideStr($array[1], $bengin, $len, 0);
            $string = implode($glue, $array);
            break;
        case 4:
            $left = $bengin;
            $right = $len;
            $tem = array();
            for ($i = 0; $i < ($length - $right); $i++) {
                if (isset($array[$i])) {
                    $tem[] = $i >= $left ? "*" : $array[$i];
                }

            }
            $array = array_chunk(array_reverse($array), $right);
            $array = array_reverse($array[0]);
            for ($i = 0; $i < $right; $i++) {
                $tem[] = $array[$i];
            }
            $string = implode("", $tem);
            break;
        default:
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i])) {
                    $array[$i] = "*";
                }

            }
            $string = implode("", $array);
            break;
        }
        return $string;
    }

    /**
     * 自动转换字符集 支持数组转换
     * @param String  $fContents 要转换的内容
     * @param String  $from 编码,gbk,utf-8
     * @param String $to 编码
     * @return String
     */
    public static function autoCharset($fContents, $from = 'gbk', $to = 'utf-8') {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
            //如果编码相同或者非字符串标量则不转换
            return $fContents;
        }
        if (is_string($fContents)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($fContents, $to, $from);
            } elseif (function_exists('iconv')) {
                return iconv($from, $to, $fContents);
            } else {
                return $fContents;
            }
        } elseif (is_array($fContents)) {
            foreach ($fContents as $key => $val) {
                $_key = auto_charset($key, $from, $to);
                $fContents[$_key] = auto_charset($val, $from, $to);
                if ($key != $_key) {
                    unset($fContents[$key]);
                }

            }
            return $fContents;
        } else {
            return $fContents;
        }
    }

    /**
     * 获取用户IP
     * @return string   用户IP
     */
    public static function getIp() {
        if (getenv('HTTP_CLIENT_IP') and strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') and strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') and strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) and $_SERVER['REMOTE_ADDR'] and strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        //检测到有获取"123.126.110.66, 61.240.149.141"的情况,取第一个IP
        $isip = preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $onlineip, $ips);
        return $onlineip = $ips[0] ? $ips[0] : '0.0.0.0';
    }

    public static function getServerId() {
        $localServerIp = $_SERVER['SERVER_ADDR'];
        $serverId = preg_replace('/(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})/is', "$3-$4", $localServerIp);
        return $serverId;
    }

    public static function ipToCity($ip) {
        $api = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;
        $ipInfo = file_get_contents($api);
        $ipInfo = json_decode($ipInfo);
        if ($ipInfo->code != 0) {
            return '未知地区';
        } else {
            if (empty($ipInfo->data->city)) {
                return '未知地区';
            } else {
                return $ipInfo->data->city . $ipInfo->data->isp;
            }
        }
    }
    /**
     * 优化时间显示
     * @param String  $date 时间字符串
     * @return String
     */
    public static function smartyTime($date) {
        $date = intval($date);
        if ($date < 1) {
            return '-';
        }
        $limit = time() - $date;
        if ($limit < 5) {
            return '5秒前';
        }
        if ($limit < 60) {
            return $limit . '秒前';
        }
        if ($limit >= 60 && $limit < 3600) {
            return floor($limit / 60) . '分前';
        }
        if ($limit >= 3600 && $limit < 86400) {
            return floor($limit / 3600) . '小时前';
        }
        if ($limit >= 86400 and $limit < 259200) {
            return floor($limit / 86400) . '天前';
        }
        if ($limit >= 259200) {
            return date('Y-m-d', $date);
        } else {
            return '';
        }
    }

    /**
     * 优化时间段显示
     * @param  mixed  $start  开始时间,时间戳或字符串(原样输出)
     * @param  mixed  $end    结束时间,时间戳或字符串(原样输出)
     * @param  string $format 格式化,参考date函数,但年月日只能间隔一个字符
     * @param  string $link   连接字符,默认' ～ '
     * @return string         优化的时间段
     */
    public static function smartyDateRange($start, $end, $format = 'Y年m月d日 G点i分', $link = ' ～ ') {

        $ss = $es = '';
        $sd = $ed = $nd = [];
        if (empty($start)) {
            $ss = '未知';
        }

        if (!is_numeric($start)) {
            $ss = $start;
        }

        if (empty($end)) {
            $es = '未知';
        }

        if (!is_numeric($end)) {
            $es = $end;
        }

        if ($ss == $es && $ss == "未知") {
            return $ss;
        }

        if ($ss) {
            return $ss . $link . date($format, $end);
        }

        if ($es) {
            return date($format, $start) . $link . $es;
        }

        $sd = getdate($start);
        $ed = getdate($end);
        $nd = getdate();
        $ss = date($format, $start);
        $es = date($format, $end);

        if ($ss == $es) {
            return $ss;
        }

        //都是今年不显示年
        if ($sd['seconds'] == 0 && $ed['seconds'] == 0) {
            $format = preg_replace('/s.?/u', '', $format);
        }

        //都是今年不显示年
        if ($sd['minutes'] + $sd['seconds'] == 0 && $ed['minutes'] + $ed['seconds'] == 0) {
            $format = preg_replace('/[is].?/u', '', $format);
        }

        //都是今年不显示年
        if ($sd['hours'] + $sd['minutes'] + $sd['seconds'] == 0 && $ed['hours'] + $ed['minutes'] + $ed['seconds'] == 0) {
            $format = preg_replace('/\s?[GgHhis].?/u', '', $format);
        }

        //不是同一年完整显示
        if ($sd['year'] != $ed['year']) {
            return $ss . $link . $es;
        }

        //都是今年不显示年
        if ($sd['year'] == $ed['year'] && $sd['year'] == $nd['year']) {
            $ss = date(preg_replace('/[Yy]./u', '', $format), $start);
        }

        //年相同不显示年
        if ($sd['year'] == $ed['year']) {
            $es = date(preg_replace('/y./ui', '', $format), $end);
        }

        //年月相同只显示日
        if ($sd['year'] . $sd['mon'] == $ed['year'] . $ed['mon']) {
            $es = date(preg_replace('/[Yy].[mn]./u', '', $format), $end);
        }

        //年月日都相同只显示时间
        if ($sd['year'] . $sd['mon'] . $sd['mday'] == $ed['year'] . $ed['mon'] . $ed['mday']) {
            $es = date(preg_replace('/[Yy].[mn].[dj].\s+/u', '', $format), $end);
        }

        return $ss . $link . $es;
    }

    /**
     * 对象转为数组
     * @param object  $models 要转换的对象
     * @return array
     */
    public static function objToArr($obj) {
        if (!is_object($obj)) {
            return $obj;
        }

        $res = array();
        foreach ($obj as $key => $value) {
            $res[$key] = $value;
        }
        return $res;
    }

    /**
     * 根据email获取email首页
     * @param String  $mail
     * @return String
     */
    public static function goToMail($mail) {
        if (strpos($mail, '@') === false) {
            return false;
        }

        $t = explode('@', $mail);
        $t = strtolower($t[1]);
        if ($t == '163.com') {
            return 'http://mail.163.com';
        } else if ($t == 'vip.163.com') {
            return 'http://vip.163.com';
        } else if ($t == '126.com') {
            return 'http://mail.126.com';
        } else if ($t == 'qq.com' || $t == 'vip.qq.com' || $t == 'foxmail.com') {
            return 'http://mail.qq.com';
        } else if ($t == 'gmail.com') {
            return 'http://mail.google.com';
        } else if ($t == 'sohu.com') {
            return 'http://mail.sohu.com';
        } else if ($t == 'tom.com') {
            return 'http://mail.tom.com';
        } else if ($t == 'vip.sina.com') {
            return 'http://vip.sina.com';
        } else if ($t == 'sina.com.cn' || $t == 'sina.com') {
            return 'http://mail.sina.com.cn';
        } else if ($t == 'tom.com') {
            return 'http://mail.tom.com';
        } else if ($t == 'yahoo.com.cn' || $t == 'yahoo.cn') {
            return 'http://mail.cn.yahoo.com';
        } else if ($t == 'tom.com') {
            return 'http://mail.tom.com';
        } else if ($t == 'yeah.net') {
            return 'http://www.yeah.net';
        } else if ($t == '21cn.com') {
            return 'http://mail.21cn.com';
        } else if ($t == 'hotmail.com') {
            return 'http://www.hotmail.com';
        } else if ($t == 'sogou.com') {
            return 'http://mail.sogou.com';
        } else if ($t == '188.com') {
            return 'http://www.188.com';
        } else if ($t == '139.com') {
            return 'http://mail.10086.cn';
        } else if ($t == '189.cn') {
            return 'http://webmail15.189.cn/webmail';
        } else if ($t == 'wo.com.cn') {
            return 'http://mail.wo.com.cn/smsmail';
        } else if ($t == '139.com') {
            return 'http://mail.10086.cn';
        } else {
            return 'http://' . $t;
        }
    }

    /**
     * 发送邮件
     * @param string    $to   发送给谁的邮件地址
     * @param string    $subject   邮件标题
     * @param string    $body   邮件内容
     * @param array    $attachment    附件
     * @param array    $config    配置
     * @return string   1成功或者错误信息
     */
    public static function sendMail($to, $subject = '', $body = '', $attachment = null, $config = '') {
        if (empty($to)) {
            return false;
        }
        $message['to'] = $to;
        $message['subject'] = $subject;
        $message['body'] = $body;
        $message['attachment'] = $attachment;
        $message['config'] = $config;
        $message = Json::encode($message);
        Helper::publishMail($message);
    }
    public static function publishMail($message) {
        $exName = 'exchange_sendmail';
        $quName = 'queue_sendmail';
        $routingKey = 'sendmail';
        Yii::$app->amqp->init();
        Yii::$app->amqp->declareExchange($exName, $type = 'direct', $passive = false, $durable = true, $auto_delete = false);
        Yii::$app->amqp->declareQueue($quName, false, false, false, false);
        Yii::$app->amqp->bindQueueExchanger($quName, $exName, $routingKey);
        Yii::$app->amqp->publish_message($message, $exName, $routingKey, $content_type = '', $app_id = '');
        Yii::$app->amqp->closeConnection();

    }

    /**
     * 提示消息并重定向
     * @param  string  $message 显示的消息
     * @param  string $type 消息类型, 成功success,错误error,信息info,
     * @param  string $url     跳转的URL
     * @param  integer $delay   跳转间隔
     * @param  string  $script  要执行的JS脚本
     */
    public static function redirectMessage($message, $type = 'info', $url = '', $delay = 2, $script = '') {

        $controller = Yii::$app->controller;
        if (empty($controller)) {
            $controller = new \common\base\Controller(Yii::$app->id, '');
        }

        //Ajax请求自动转为标准Json返回 add by tasal 161214
        if (Yii::$app->request->isAjax) {
            return $controller->renderJson(['errno' => $type == 'success' ? 0 : $type, 'errmsg' => $message, 'url'=>$url]);
        }
        if (empty($url)) {
            $url = filter_var(Yii::$app->getRequest()->getReferrer());
        } elseif (is_array($url)) {
            $route = isset($url[0]) ? $url[0] : '';
            $url = Yii::$app->urlManager->createUrl($route, array_splice($url, 1));
        }

        //移动设备:
        $md = new MobileDetect();
        if ($md->isMobile() || $md->isTablet()) {
            echo $controller->renderPartial('@common/views/public/error_mobile', array(
                'message' => $message,
                'url' => $url,
                'delay' => $delay,
                'type' => $type,
                'script' => $script,
            ));
            //用户中心前台
        } elseif ($controller->id != 'public') {
            //$model->layout = '//layouts/content';
            echo $controller->renderPartial('@common/views/public/error_uc', array(
                'message' => $message,
                'url' => $url,
                'delay' => $delay,
                'type' => $type,
                'script' => $script,
            ));
            //用户中心后台
        } else {
            echo $controller->renderPartial('@common/views/public/error', array(
                'message' => $message,
                'url' => $url,
                'delay' => $delay,
                'type' => $type,
                'script' => $script,
            ));
        }
        Yii::$app->end();
    }

    /**
     * 将数组转为关联数组,暂时只支持二维数组
     * 主要用于将数据库查询的结果转变为以主键为Key的关联数组,和将其中某个值提取出来,
     * @param  array $array 二维数组
     * @param  string $key  二维数组中的关联key
     * @param  bool         为true时只返回包含指定key的值的一维数组
     * @return array        以指定key对应的值作为key的关联数组
     */
    public static function arrayToAssoc($array, $key = '', $onlyKey = false) {
        $map = array();
        if (!is_array($array)) {
            return $map;
        }

        if (isset($array[$key]) && !is_array($array[$key]) && !is_object($array[$key])) {
            return $array[$key];
        }

        foreach ($array as $value) {
            if (isset($value[$key]) && !is_array($value[$key]) && !is_object($value[$key])) {
                if ($onlyKey) {
                    $map[] = $value[$key];
                } else {
                    $map[$value[$key]] = $value;
                }
            } else {
                continue;
            }
        }
        return $map;
    }

    /**
     * @action 二维数组根据特定键值索引排序
     * @param $arr Array 二维数组
     * @param $orderbyKey String 特定索引
     * @param $type String ASC|DESC
     * @return Array
     */
    public static function arraySort($Array = array(), $OrderByKey = NULL, $Type = 'ASC') {
        $ReturnArray = array();
        //判断排序列为空操作
        if (!empty($OrderByKey)) {
            //转成一维数组操作
            $Column = array();
            //循环操作得到的数组
            if (!empty($Array)) {
                foreach ($Array as $key => $value) {
                    //一维数组负值操作
                    $Column[$key] = $value[$OrderByKey];
                }
                //判断排序类型
                $Type = strtoupper($Type) == "ASC" ? SORT_ASC : SORT_DESC;
                //对数组进行按传值排序
                array_multisort($Column, $Type, $Array);
                $ReturnArray = $Array;
            }
        } else {
            $ReturnArray = $Array;
        }
        //返回数组
        return $ReturnArray;
    }

    /**
     * 下载远程图片
     * @param string $url 图片的绝对url
     * @param string $filepath 文件的完整路径（包括目录，不包括后缀名,例如/www/images/test） ，此函数会自动根据图片url和http头信息确定图片的后缀名
     * @return mixed 下载成功返回一个描述图片信息的数组，下载失败则返回false
     */
    public static function downloadImage($url, $filepath) {
        //服务器返回的头信息
        $responseHeaders = array();
        //原始图片名
        $originalfilename = '';
        //图片的后缀名
        $ext = '';
        $ch = curl_init($url);
        //设置curl_exec返回的值包含Http头
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //设置curl_exec返回的值包含Http内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //设置抓取跳转（http 301，302）后的页面
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);   //edit by liufengyue 2015-03-07 原因：不注释掉会报错
        //设置最多的HTTP重定向的数量
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);

        //服务器返回的数据（包括http头信息和内容）
        $html = curl_exec($ch);
        //获取此次抓取的相关信息
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);
        if ($html !== false) {
            //分离response的header和body，由于服务器可能使用了302跳转，所以此处需要将字符串分离为 2+跳转次数 个子串
            $httpArr = explode("\r\n\r\n", $html, 2 + $httpinfo['redirect_count']);
            //倒数第二段是服务器最后一次response的http头
            $header = $httpArr[count($httpArr) - 2];
            //倒数第一段是服务器最后一次response的内容
            $body = $httpArr[count($httpArr) - 1];
            $header .= "\r\n";

            //获取最后一次response的header信息
            preg_match_all('/([a-z0-9-_]+):\s*([^\r\n]+)\r\n/i', $header, $matches);
            if (!empty($matches) && count($matches) == 3 && !empty($matches[1]) && !empty($matches[1])) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    if (array_key_exists($i, $matches[2])) {
                        $responseHeaders[$matches[1][$i]] = $matches[2][$i];
                    }
                }
            }

            //获取图片后缀名
            if (0 < preg_match('{(?:[^\/\\\\]+)\.(jpg|jpeg|gif|png|bmp)$}i', $url, $matches)) {
                $originalfilename = $matches[0];
                $ext = $matches[1];
            } else {
                if (array_key_exists('Content-Type', $responseHeaders)) {
                    if (0 < preg_match('{image/(\w+)}i', $responseHeaders['Content-Type'], $extmatches)) {
                        $ext = $extmatches[1];
                    }
                }
            }
            //保存文件
            if (!empty($ext)) {
                $filepath .= ".$ext";
                $local_file = fopen($filepath, 'w');
                if (false !== $local_file) {
                    if (false !== fwrite($local_file, $body)) {
                        fclose($local_file);
                        $sizeinfo = getimagesize($filepath);
                        return array('filepath' => realpath($filepath), 'width' => $sizeinfo[0], 'height' => $sizeinfo[1], 'orginalfilename' => $originalfilename, 'filename' => pathinfo($filepath, PATHINFO_BASENAME));
                    }
                }
            }
        }
        return false;
    }

    /**
     * 解析QQ表情,将表情符号替换为图片
     * @param  string $content 需替换的内容
     * @param  string $use     表情显示的方式,符号symbol如:/::) 文字cname如:/微笑
     * @return string          将表情符号替换为图片之后的内容
     */
    public static function qqemotion($content, $use = 'symbol') {
        $cnname = array('/微笑', '/撇嘴', '/色', '/发呆', '/得意', '/流泪', '/害羞', '/闭嘴', '/睡', '/大哭', '/尴尬', '/发怒', '/调皮', '/呲牙', '/惊讶', '/难过', '/酷', '/冷汗', '/抓狂', '/吐', '/偷笑', '/可爱', '/白眼', '/傲慢', '/饥饿', '/困', '/惊恐', '/流汗', '/憨笑', '/大兵', '/奋斗', '/咒骂', '/疑问', '/嘘', '/晕', '/折磨', '/衰', '/骷髅', '/敲打', '/再见', '/擦汗', '/抠鼻', '/鼓掌', '/糗大了', '/坏笑', '/左哼哼', '/右哼哼', '/哈欠', '/鄙视', '/委屈', '/快哭了', '/阴险', '/亲亲', '/吓', '/可怜', '/菜刀', '/西瓜', '/啤酒', '/篮球', '/乒乓', '/咖啡', '/饭', '/猪头', '/玫瑰', '/凋谢', '/示爱', '/爱心', '/心碎', '/蛋糕', '/闪电', '/炸弹', '/刀', '/足球', '/瓢虫', '/便便', '/月亮', '/太阳', '/礼物', '/拥抱', '/强', '/弱', '/握手', '/胜利', '/抱拳', '/勾引', '/拳头', '/差劲', '/爱你', '/NO', '/OK', '/爱情', '/飞吻', '/跳跳', '/发抖', '/怄火', '/转圈', '/磕头', '/回头', '/跳绳', '/挥手', '/激动', '/街舞', '/献吻', '/左太极', '/右太极');
        $symbol = array("/::)", "/::~", "/::B", "/::|", "/:8-)", "/::<", "/::$", "/::X", "/::Z", "/::'(", "/::-", "/::@", "/::P", "/::D", "/::O", "/::(", "/::+", "/:--b", "/::Q", "/::T", "/:,@P", "/:,@-D", "/::d", "/:,@o", "/::g", "/:-)", "/::!", "/::L", "/::>", "/::,@", "/:,@f", "/::-S", "/:?", "/:,@x", "/:,@@", "/::8", "/:,@!", "/:!!!", "/:xx", "/:bye", "/:wipe", "/:dig", "/:handclap", "/:&-(", "/:B-)", "/:<@", "/:@>", "/::-O", "/:>-", "/:P-(", "/::'", "/:X-)", "/::*", "/:@x", "/:8*", "/:pd", "/:<W>", "/:beer", "/:basketb", "/:oo", "/:coffee", "/:eat", "/:pig", "/:rose", "/:fade", "/:showlove", "/:heart", "/:break", "/:cake", "/:li", "/:bome", "/:kn", "/:footb", "/:ladybug", "/:shit", "/:moon", "/:sun", "/:gift", "/:hug", "/:strong", "/:weak", "/:share", "/:v", "/:@)", "/:jj", "/:@@", "/:bad", "/:lvu", "/:no", "/:ok", "/:love", "/:<L>", "/:jump", "/:shake", "/:<O>", "/:circle", "/:kotow", "/:turn", "/:skip", "/:oY", "/:#-0", "/:hiphot", "/:kiss", "/:<&", "/:&>");
        $path = CDN_STATIC . 'img/qq/emotion/';

        if ($use == 'cnname') {
            $replace = $cnname;
        } else {
            $replace = $symbol;
        }

        foreach ($replace as $key => $value) {
            $content = str_replace($value, '<img src="' . $path . $key . '.gif" class="qqemotion">', $content);
        }
        return $content;
    }

    /**
     *
     * 微网站拥有权限后显示版权
     * @param integer $we_account_id 微信公众帐号
     * @return bool 返回版权
     */
    public static function copyrightAuth($we_account_id, $url = true) {
        $WeisiteModel = new Weisite;
        $Weisite = $WeisiteModel->find(array('select' =>
            '`copyright`,`copyright_url`',
            'condition' => 'we_account_id = :we_account_id',
            'params' => array(':we_account_id' => $we_account_id)));
        if ($Weisite->copyright != '') {
            if ($url == true) {
                return '<a href="' . $Weisite->copyright_url . '" target="_blank">' . $Weisite->copyright . '</a>';
            } else {
                return $Weisite->copyright;
            }
        }
        if ($url == true) {
            return '<span><a href="http://' . AgentTool::get_site_settings()['domain'] . '" target="_blank">技术支持：' . AgentTool::get_site_settings()['site_name'] . '</a></span>';
        }
        return '技术支持：' . AgentTool::get_site_settings()['site_name'];
    }
    /**
     *
     * @param int $weAccountId
     * @param array $party  办公逸部门主键id
     * @param array $staff  办公逸员工主键id
     * @param array $totag  标签we_id
     * @param string $content
     * @param string $module
     * @param array $app
     * @param string $type
     * @param string $sendType
     * @param string $split 是否分割 false 不分割 true 分割  2 未打卡提醒单独队列(amqp_attend_reminder)
     */
    public static function multiSendWeixin($weAccountId, $party, $staff, $content, $module, $app, $type = "text", $sendType = 'user', $split = false, $safe = 0, $totag) {
        if ((int) $weAccountId <= 0 || (empty($party) && empty($staff))) {
            return false;
        }
        //发送部门
        if ($party) {
            $party = (new Query())->select("DISTINCT(we_id)")->from('{{%department}}')->where(['we_account_id' => $weAccountId, 'id' => $party])->indexBy('we_id')->all();
            if (!empty($party)) {
                $toparty = array_keys($party);
            }
        }
        //发送员工
        if ($staff) {
            if ($party) {
                $staff = (new Query())->select("DISTINCT(we_userid), we_department")->from('{{%staff}}')->where(['we_account_id' => $weAccountId, 'id' => $staff])->all();
                foreach ($staff ?: [] as $s) {
                    $weidArr = explode(',', $s['we_department']);
                    foreach ($weidArr as $weid) {
                        if (!in_array($weid, $party)) {
                            $touser[] = $s['we_userid'];
                        }
                    }
                }
            } else {
                $staff = (new Query())->select("DISTINCT(we_userid)")->from('{{%staff}}')->where(['we_account_id' => $weAccountId, 'id' => $staff])->indexBy('we_userid')->all();
                if (!empty($staff)) {
                    $touser = array_keys($staff);
                }
            }
        }
        $size = 1000;
        $topartyArr = array_chunk($toparty, $size);
        $touserArr = array_chunk($touser, $size);
        $totagArr = array_chunk($totag, $size);
        $maxCount = (count($topartyArr) > count($touserArr)) ? count($topartyArr) : count($touserArr);
        $maxCount = ($maxCount > count($totagArr)) ? $maxCount : count($totagArr);
        //如果party或者staff数量大于1000时，有些员工可能会收到多条消息
        for ($i = 0; $i < $maxCount; $i++) {
            $toUserArr['toparty'] = $topartyArr[$i] ? join('|', $topartyArr[$i]) : '';
            $toUserArr['touser'] = $touserArr[$i] ? join('|', $touserArr[$i]) : '';
            $toUserArr['totag'] = $totagArr[$i] ? join('|', $totagArr[$i]) : '';
            self::sendWeixin($weAccountId, $toUserArr, $content, $module, $app, $type, 'user', $split, $safe);
        }
    }

    /**
     * 发送微信消息 ,目前只能发送单条文本消息
     * @param int $weAccountId 企业号ID
     * @param string $touser 接受者:微信userid，多个已“|”分隔，如：UserID1|UserID2|UserID3
     * @param string $content 文本消息内容
     *  图文消息[
     *         [
     *            "title" => "Title",             //标题
     *            "description" => "Description", //描述
     *            "url" => "URL",                 //点击后跳转的链接。可根据url里面带的code参数校验员工的真实身份。
     *            "picurl" => "PIC_URL",          //图文消息的图片链接,支持JPG、PNG格式，较好的效果为大图640*320，
     *         ],
     *         [
     *            "title" => "Title",             //标题
     *            "description" => "Description", //描述
     *            "url" => "URL",                 //点击后跳转的链接。可根据url里面带的code参数校验员工的真实身份。
     *            "picurl" => "PIC_URL",          //图文消息的图片链接,支持JPG、PNG格式，较好的效果为大图640*320，
     *         ],
     *       ]
     * @param string $module 模块名称
     * @param array $app 应用数组 [''=>'',''=>'',''=>'',''=>'',''=>'']
     * @param string $type 消息类型 'text':文本,'news':图文
     * @param string $sendType 接受人的类型，user：员工，party：部门
     * @return boolean 成功返回true,失败false
     */
    public static function sendWeixin($weAccountId, $toUser, $content, $module, $app, $type = "text", $sendType = 'user', $split = false, $safe = 0) {
        // $weAccount = MCache::weaccount(intval($weAccountId));
        // if(empty($weAccount) || empty($weAccount['we_corp_id'])){
        //     (new SendMessage())->saveMessage($weAccountId?:0, $module , $toUser ,$content,$type,2);
        //     return false;
        // }
        // $qyWechat = new QyWechat(['bgy_app_id'=>$agentId]);
        // if($type=='text'){
        //     $content = ['content'=>$content];
        // }elseif($type=='news'){
        //     $content = ['articles'=>$content];
        // }
        // if($sendType=='user'){
        //     $message = [
        //         "touser" => $toUser,
        //         "agentid" => $agentId,
        //         "msgtype" => $type,
        //         "$type" => $content
        //     ];
        // }elseif($sendType=='party'){
        //     $message = [
        //         "toparty" => $toUser,
        //         "agentid" => $agentId,
        //         "msgtype" => $type,
        //         "$type" => $content
        //     ];
        // }

        // $result = $qyWechat->sendMessage($message);
        // if($result){
        //     //发送成功日志记录
        //     (new SendMessage())->saveMessage($weAccountId, $module , $toUser ,$content,$type);
        //     return true;
        // }else{
        //     //发送失败日志记录
        //     (new SendMessage())->saveMessage($weAccountId, $module , $toUser ,$content,$type,$qyWechat->errCode);
        //     return false;
        // }
        Helper::sendWeixinTask($weAccountId, $toUser, $content, $module, $app, $type, $sendType, $split, $safe);
    }

    public static function sendWeixinTask($weAccountId, $toUser, $content, $module, $app, $type = "text", $sendType = 'user', $split = false, $safe = 0) {

        if ($app) {
            $temp['we_account_id'] = $app['we_account_id'];
            $temp['appstore_id'] = $app['appstore_id'];
            $temp['we_agent_id'] = $app['we_agent_id'];
            $temp['we_permanent_code'] = $app['we_permanent_code'];
            $app = $temp;
        } else {
            return false;
        }
        if ($type == 'text') {
            $content = ['content' => $content];
        } elseif ($type == 'news') {
            $content = ['articles' => $content];
        } elseif ($type == 'file' || $type == 'voice' || $type == 'video' || $type == 'image') {
            $content = ['media_id' => $content];
        } elseif ($type == 'mpnews') {
            $content = ['articles' => $content];
        }

        if (is_array($toUser)) {
            $message = [
                "we_account_id" => $weAccountId,
                "module" => $module,
                "toparty" => $toUser['toparty'] ?: '',
                "touser" => $toUser['touser'] ?: '',
                "totag" => $toUser['totag'] ?: '',
                "agentid" => $app['we_agent_id'],
                "msgtype" => $type,
                "$type" => $content,
            ];
        } elseif (is_string($toUser)) {
            if ($sendType == 'user') {
                $message = [
                    "we_account_id" => $weAccountId,
                    "module" => $module,
                    "touser" => $toUser,
                    "agentid" => $app['we_agent_id'],
                    "msgtype" => $type,
                    "$type" => $content,
                ];
            } elseif ($sendType == 'party') {
                $message = [
                    "we_account_id" => $weAccountId,
                    "module" => $module,
                    "toparty" => $toUser,
                    "agentid" => $app['we_agent_id'],
                    "msgtype" => $type,
                    "$type" => $content,
                ];
            }
        }
        $message['safe'] = $safe;
        $message['app'] = $app;
        $message['split'] = $split;
        $message = Json::encode($message);

        Helper::publishMessage($message, $split);
    }
    public static function publishMessage($message, $split = false) {
        if ($split === 2) {
            //未打卡提醒单独队列
            $exName = 'exchange_sendmessage';
            $quName = 'queue_sendmessage';
            $routingKey = 'sendmessage';
            Yii::$app->amqp_attend_reminder->init();
            Yii::$app->amqp_attend_reminder->declareExchange($exName, $type = 'direct', $passive = false, $durable = true, $auto_delete = false);
            Yii::$app->amqp_attend_reminder->declareQueue($quName, false, false, false, false);
            Yii::$app->amqp_attend_reminder->bindQueueExchanger($quName, $exName, $routingKey);
            Yii::$app->amqp_attend_reminder->publish_message($message, $exName, $routingKey, $content_type = '', $app_id = '');
            Yii::$app->amqp_attend_reminder->closeConnection();
        } else {
            $exName = 'exchange_sendmessage';
            $quName = 'queue_sendmessage';
            $routingKey = 'sendmessage';
            Yii::$app->amqp->init();
            Yii::$app->amqp->declareExchange($exName, $type = 'direct', $passive = false, $durable = true, $auto_delete = false);
            Yii::$app->amqp->declareQueue($quName, false, false, false, false);
            Yii::$app->amqp->bindQueueExchanger($quName, $exName, $routingKey);
            Yii::$app->amqp->publish_message($message, $exName, $routingKey, $content_type = '', $app_id = '');
            Yii::$app->amqp->closeConnection();
        }

    }

    /**
     * 发短信功能
     * @param integer $we_account_id 微信公众帐号
     * @param string $mobile 手机号
     * @param string $content 内容
     * @param string $vcode 验证码，可为空
     * @return integer 返回值，1:成功,0失败,-1余额不足
     */
    public static function sendSMS($we_account_id, $mobile, $content, $vcode = '') {
        $we_account_id = intval($we_account_id);
        $sms = \Yii::$app->sms;
        //$mobile='13811991863';
        //$content='张三预约2014年4月1日18点5人到望京店就餐，电话13811240123,快点联系客人吧【强大微】';
        //$we_account_id = intval(\Yii::$app->session['weaccount']['id']);
        $we_account = Cache::weaccount($we_account_id);
        if ($we_account && $we_account['user_id']) {
            $user_id = intval($we_account['user_id']);
        } else {
            return -1;
        }

        $user = User::model()->findByPk($user_id);
        if ($user && $user->sms_count > 0) {
            $user->sms_count = $user->sms_count - 1; //更新可发送数量
            $user->setIsNewRecord(false);
            $user->save(false);

            $sms_log = new SmsLog;
            $sms_log->we_account_id = $we_account_id;
            $sms_log->create_time = time();
            $sms_log->to_mobile = $mobile;
            $sms_log->content = $content;
            $sms_log->vcode = $vcode;
            $result = $sms->sendSMS($mobile, $content);
            $sms_log->return_data = $result;
            $sms_log->setIsNewRecord(true);
            if ($sms_log->save(false)) {
                return 1;
            } else {
                return 0;
            }

        } else {
            return -1;

        }

    }
    /**
     * 验证短信验证码
     * @param integer $we_account_id 微信公众帐号
     * @param string $mobile 手机号
     * @param string $vcode 验证码
     * @param string $ss 间隔时间 秒
     * @return integer 返回值，1:成功,0失败
     */
    public static function verifyMobileVcode($we_account_id, $mobile, $vcode, $ss) {
        $sms_logs = \Yii::$app->db->createCommand()
            ->select('create_time,vcode')
            ->from('{{sms_log}}')
            ->where('we_account_id = :we_account_id and to_mobile = :to_mobile and create_time>= :create_time', array(':we_account_id' => $we_account_id, ':to_mobile' => $mobile, ':create_time' => time() - $ss))
            ->order('id desc')
            ->limit(1)
            ->queryAll();
//        var_dump($sms_logs);
        if (isset($sms_logs)) {
            if (count($sms_logs) > 0 && $sms_logs[0]['vcode'] == $vcode) {
                return 1;
            } else {
                return 0;
            }

        }

    }

    /**
     * 验证短信发送次数
     * @param integer $we_account_id 微信公众帐号
     * @param string $mobile 手机号
     * @param string $ss 距离当前时间 秒
     * @return integer 返回值，时间短内已经发送过的次数
     */
    public static function verifyMobileSendCount($we_account_id, $mobile, $ss) {
        $sms_log = \Yii::$app->db->createCommand()
            ->select('count(*)')
            ->from('{{sms_log}}')
            ->where('we_account_id = :we_account_id and to_mobile = :to_mobile and create_time>= :create_time', array(':we_account_id' => $we_account_id, ':to_mobile' => $mobile, ':create_time' => time() - $ss))
            ->queryScalar();
        return intval($sms_log);

    }

    /**
     * GET 请求
     * @param string $url
     */
    public static function http_get($url, $timeout = 0) {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                // 设置超时2秒
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 2);
        if ($timeout) {
            curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeout);
        }
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $errno = curl_errno($oCurl);
        $error = curl_error($oCurl);
        curl_close($oCurl);
        API_TRACE && \common\models\WechatApiLog::logSave(intval(Yii::$app->session['weaccount']['id']), 0, 0, 0, $url, $param, $sContent, $errno, $error);

        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @return string content
     */
    public static function http_post($url, $param, $timeout = 0) {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        // 设置超时2秒
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 2);
        if ($timeout) {
            curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeout);
        }
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);

        $errno = curl_errno($oCurl);
        $error = curl_error($oCurl);
        curl_close($oCurl);
        API_TRACE && \common\models\WechatApiLog::logSave(intval(Yii::$app->session['weaccount']['id']), 0, 0, 0, $url, $param, $sContent, $errno, $error);

        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * 替换HTML代码
     * @param string $obj
     * @return string content
     */
    public static function clearHtml($obj) {
        $content = preg_replace("/(<[^>]+>)/is", "", $obj);
        return $content;
    }

    /**
     * 格式化内容，过率HTML，JS，CSS，回车，换行
     *
     * @param [string] $body [内容]
     * @return [string]     返回所有文字
     */
    public static function formatHtml($body) {
        $show_body = htmlspecialchars_decode($body);
        $show_body = preg_replace("@<script(.*?)</script>@is", "", $show_body);
        $show_body = preg_replace("@<iframe(.*?)</iframe>@is", "", $show_body);
        $show_body = preg_replace("@<style(.*?)</style>@is", "", $show_body);
        $show_body = preg_replace("@<(.*?)>@is", "", $show_body);
        $show_body = preg_replace('/\s/i', '', $show_body);

        $show_body = strip_tags($show_body);
        $show_body = preg_replace('/\n/is', '', $show_body);
        $show_body = preg_replace('/ |　/is', '', $show_body);
        $show_body = preg_replace('/&nbsp;/is', '', $show_body);
        return $show_body;
    }

    /**
     * 去除回车换行符与敏感html标签
     * @param string $str
     * @return string $str
     */
    public static function deleteHtml($str) {
        $str = str_replace("<br/>", "", $str);
        $str = str_replace("\t", "", $str);
        $str = str_replace("\r\n", "", $str);
        $str = str_replace("\r", "", $str);
        $str = str_replace("\n", "", $str);
        return trim($str);
    }

    /**
     *
     * @param int $weAccountId 企业号ID
     * @param string $operatorId 操作者id ，后台操作的是用user_id，前台操作的使用 staff_id
     * @param int $logType  1管理员操作，2员工操作，3manage操作
     * @param string $operateType 后台：注册，登陆，注销，添加，修改，删除， 前台：登陆，
     * @param string $content
     * @param string $deviceId 设备ID
     * @return boolean 添加成功返回true，失败false
     */
    public static function operateLog($logType, $weAccountId, $operatorId, $operateType, $content, $deviceId = '') {
        return (new Logs())->log($logType, $weAccountId, $operatorId, $operateType, $content, $deviceId);
    }

    /**
     * 给url增加参数
     * @param string $url
     * @param string or array
     * return String or false  new url
     */
    public static function joinQueryString($url, $param) {
        if (empty($param)) {
            return false;
        }

        $url = urldecode($url);
        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        $start = strrpos($url, '#');
        if ($start) {
            $url = substr($url, 0, $start);
            $fragment = '#' . $fragment;
        }
        return !empty($query) ? $url . '&' . $param . $fragment : $url . '?' . $param . $fragment;
    }

    /*
     * url中的参数转成键值对的数组
     *
     * @access   public
     * @param    string      $parametersarray    url的quertstring
     * @return   string
     * */
    public static function string2array($parametersarray) {
        $parameter = explode('&', $parametersarray);
        foreach ($parameter as $val) {
            $para = explode('=', $val);
            $top_parameters_v[$para[0]] = $para[1];
        }
        return $top_parameters_v;
    }
    /**
     * 汉字转为拼音
     * @param string $str  输入的汉字参数需为utf-8编码
     * @param string $type 'pinyin'：全拼 ,'letter'：首字母
     * @return string $str 拼音
     */
    public static function getPinYin($str, $type = 'pinyin') {
        include_once 'Pinyin.php';
        return getPinyin($str, $type) ?: '#';
    }

    /**
     *share SJ_SDK分享
     *@param int $we_account_id
     *return array $signPackage  返回
     */
    public static function share($we_account_id) {
        if (isset($we_account_id)) {
            $account = MCache::weaccount($we_account_id);
            if ($account) {
                $wechat = new QyWechat(['aid' => $we_account_id]);
                $signPackage = $wechat->getSignPackage();
                return $signPackage;
            }
        }
    }

    /**
     * 获取当前帐号上传路径, 仅按规则获取路径, 不创建文件夹
     * 路径规则: 取帐号ID的后九位每三位作为一个文件夹
     * @param  string $weAccountId 帐号ID
     * @return string 存放绝对路径
     */
    public static function getUploadsPath($weAccountId = '', $realpath = true) {
        $weAccountId = $weAccountId ?: \Yii::$app->session['weaccount']['id'];
        if ($weAccountId < 1) {
            return false;
        }
        $weAccountId = substr('000000000' . $weAccountId, -9);
        $path = '/uploads/' . implode(str_split($weAccountId, 3), '/') . '/';
        if ($realpath) {
            FileHelper::createDirectory(Yii::getAlias('@siteroot') . $path);
        }
        return $path;
    }

    /**
     * 上传文件 (2015-03-07 edit by liufnegyue)
     * @url     上传的地址
     * @r_file  文件路径
     * @return
     */
    public static function upload_file($url, $r_file) {
        // $file = array("fax_file"=>'@'.$r_file);
        if (class_exists('\CURLFile')) {
            //关键是判断curlfile,官网推荐php5.5或更高的版本使用curlfile来实例文件
            $file = array(
                'fax_file' => new \CURLFile(realpath($r_file)),
            );
        } else {
            $file = array(
                "fax_file" => '@' . $r_file, //文件路径，前面要加@，表明是文件上传.
            );
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $file);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $result = curl_exec($curl); //$result 获取页面信息
        curl_close($curl);
        return $result; //返回结果
    }

    public static function getWeekCn($datetime) {
        if (empty($datetime)) {
            return '';
        }

        $weekList = ['日', '一', '二', '三', '四', '五', '六'];
        return $weekList[date('w', $datetime)];
    }

    public static function timeToHI($time, $format = 'H:i') {
        if (empty($time)) {
            return '';
        }

        //大于24小时当着时间戳处理
        if ($time < 1440) {
            $h = floor($time / 60);
            $i = $time % 60;
            $i = substr('0' . $i, -2);
        } else {
            $h = date('H', $time);
            $i = date('i', $time);
        }
        $format = str_replace('H', $h, $format);
        $format = str_replace('i', $i, $format);
        return $format;
    }
    /**
     * 图片压缩函数
     * @param <string>  $srcFile       原图片路径
     * @param <int>     $newWidth      压缩后图片宽度
     * @param <int>     $newHeight     压缩后图片高度
     * @return <string>                压缩后图片存储路径
     */
    public static function thumb($srcFile, $newWidth, $newHeight) {
        $pathinfo = pathinfo($srcFile);
        $dst_file = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_' . $newWidth . 'x' . $newHeight . '.' . $pathinfo['extension'];
        if (!file_exists($dst_file)) {
            if ($newWidth < 1 || $newHeight < 1) {
                return false;
            }
            if (!file_exists($srcFile)) {
                return false;
            }
            // 图像类型
            $img_type = exif_imagetype($srcFile);
            $support_type = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF);
            if (!in_array($img_type, $support_type, true)) {
                return false;
            }
            /* 载入图像 */
            switch ($img_type) {
            case IMAGETYPE_JPEG:
                $src_img = imagecreatefromjpeg($srcFile);
                break;
            case IMAGETYPE_PNG:
                $src_img = imagecreatefrompng($srcFile);
                break;
            case IMAGETYPE_GIF:
                $src_img = imagecreatefromgif($srcFile);
                break;
            default:
                return false;
            }
            /* 获取源图片的宽度和高度 */
            $src_width = imagesx($src_img);
            $src_height = imagesy($src_img);
            // 为剪切图像创建背景画板
            $image_p = imagecreatetruecolor($newWidth, $newHeight);
            if ($img_type == IMAGETYPE_PNG) {
                $c = imagecolorallocatealpha($image_p, 0, 0, 0, 127); //拾取一个完全透明的颜色
                imagealphablending($image_p, false); //关闭混合模式，以便透明颜色能覆盖原画布
                imagefill($image_p, 0, 0, $c); //填充
                imagesavealpha($image_p, true); //设置保存PNG时保留透明通道信息
            }

            //拷贝剪切的图像数据到画板，生成剪切图像
            imagecopyresampled($image_p, $src_img, 0, 0, 0, 0, $newWidth, $newHeight, $src_width, $src_height);

            // 为裁剪图像创建背景画板
            $new_img = imagecreatetruecolor($newWidth, $newHeight);
            if ($img_type == IMAGETYPE_PNG) {
                $n = imagecolorallocatealpha($new_img, 0, 0, 0, 127); //拾取一个完全透明的颜色
                imagealphablending($new_img, false); //关闭混合模式，以便透明颜色能覆盖原画布
                imagefill($new_img, 0, 0, $n); //填充
                imagesavealpha($new_img, true); //设置保存PNG时保留透明通道信息
            }
            //拷贝剪切的图像数据到画板
            imagecopy($new_img, $image_p, 0, 0, 0, 0, $newWidth, $newHeight);
            /* 按格式保存为图片 */
            switch ($img_type) {
            case IMAGETYPE_JPEG:
                imagejpeg($image_p, $dst_file, 100);
                break;
            case IMAGETYPE_PNG:
                imagepng($image_p, $dst_file, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($image_p, $dst_file, 100);
                break;
            default:
                break;
            }
        }
        return ltrim($dst_file, '.');
    }

    /**
     * 去除字符串中的emoji表情符号
     * @param $str
     */
    public static function removeEmoji($text) {
        $text = preg_replace_callback('/[\xf0-\xf7].{3}/', function ($r) {return '';}, $text);
        return $text;
    }

    /**
     * [encryptUrl 文件路径加密]
     * @param [type] $url  [文件路径]
     * @param string $type [状态 默认时直接返回加密串、为1时拼上‘/viewattachment?v=’路径]
     */
    public static function encryptUrl($url, $type = 0) {
        $sc = new Crypt(CRYPT_KEY);
        if ($type != 0) {
            return $url = $sc->php_encrypt($url);
        } else {
            $url = 'path=' . $url;
            $url = $sc->php_encrypt($url);
            return $url = '/viewattachment?v=' . $url;
        }
    }

    /**
     * [decryptUrl 文件路径解密]
     * @param [type] $url [文件路径]
     */
    public static function decryptUrl($url) {
        $sc = new Crypt(CRYPT_KEY);
        return $url = $sc->php_decrypt($url);
    }
    /**
     * [decryptUrl 获取内容中的详细信息]
     * @param [type] $url [文件路径]
     */
    static function matchLinks($document) {
        preg_match_all("'<\s*a\s.*?href\s*=\s*([\"\'])?(?(1)(.*?)\\1|([^\s\>]+))[^>]*>?(.*?)</a>'isx", $document, $links);
        while (list($key, $val) = each($links[2])) {
            if (!empty($val)) {
                $match['link'][] = $val;
            }

        }
        while (list($key, $val) = each($links[3])) {
            if (!empty($val)) {
                $match['link'][] = $val;
            }

        }
        while (list($key, $val) = each($links[4])) {
            if (!empty($val)) {
                $match['content'][] = $val;
            }

        }
        while (list($key, $val) = each($links[0])) {
            if (!empty($val)) {
                $match['all'][] = $val;
            }

        }
        return $match;
    }

    static function makeQrcode($aid, $url, $logo = false, $size = "320") {
        $rand = rand(10, 100000000);
        $siteRoot = Yii::getAlias('@siteroot');
        $path = $siteRoot . Helper::getUploadsPath($aid) . 'qrcode/';
        if (!file_exists($path)) {
            @mkdir($path, 0777, true); //临时777
        }
        $name = $rand . ".png";
        $qrcodeUrl = Helper::getUploadsPath($aid) . 'qrcode/' . $name;
        require_once 'common/extensions/qrcode/phpqrcode.php';
        $errorCorrectionLevel = 'L'; //容错级别
        $qrcodePath = $path . $name;
        //生成二维码图片
        \QRcode::png($url, $qrcodePath, $errorCorrectionLevel, $size, 2, true);
        $QR = $qrcodePath;
        if ($logo !== false) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR); //二维码图片宽度
            $QR_height = imagesy($QR); //二维码图片高度
            $logo_width = imagesx($logo); //logo图片宽度
            $logo_height = imagesy($logo); //logo图片高度
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小

            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                $logo_qr_height, $logo_width, $logo_height);
        }
        //输出图片

        imagepng($QR, $qrcodePath);
        return $qrcodeUrl;
    }

    /**
     * 导出时转换15位以上长数字账号及0打头的数字账号
     * @param $weUserId
     * @param bool $change
     * @return string
     */
    public static function weUserIdFormat($weUserId, $change = false) {
        if ($change) {
            if (is_numeric($weUserId) && (strlen($weUserId) > 15 || $weUserId[0] == 0)) {
                $weUserId = "'" . $weUserId;
            }
        }
        return $weUserId;
    }

    /**
     * 获取文件夹大小
     *
     */
    public static function getDirSize($dir) {
        $handle = opendir($dir);
        while (false !== ($FolderOrFile = readdir($handle))) {
            if ($FolderOrFile != "." && $FolderOrFile != "..") {
                if (is_dir("$dir/$FolderOrFile")) {
                    $sizeResult += self::getDirSize("$dir/$FolderOrFile");
                } else {
                    $sizeResult += filesize("$dir/$FolderOrFile");
                }
            }
        }
        closedir($handle);
        return $sizeResult;
    }

    /**
     *  单位自动转换函数
     * @param int $size
     */
    public static function getRealSize($size) {
        $kb = 1024; // Kilobyte
        $mb = 1024 * $kb; // Megabyte
        $gb = 1024 * $mb; // Gigabyte
        $tb = 1024 * $gb; // Terabyte

        if ($size < $kb) {
            return $size . " B";
        } else if ($size < $mb) {
            return round($size / $kb, 2) . " KB";
        } else if ($size < $gb) {
            return round($size / $mb, 2) . " MB";
        } else if ($size < $tb) {
            return round($size / $gb, 2) . " GB";
        } else {
            return round($size / $tb, 2) . " TB";
        }
    }
}
