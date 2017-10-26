/**
 * 数据库更改记录
 *
 * 注意: 为保证数据库的完整性和一致性，请不要直接更改数据库
 *       请大家将所有对数据库的更改都转换为可以直接执行的SQL语句
 *       建议每次增加SQL时备注上姓名、时间和简单描述，以便于排错
 *       每月一个文件，文件最后请保留一个空行
 */


#20150303 蒋龙奎 示例
ALTER TABLE `dv_bbs` ADD COLUMN `click_count`  int(11) NOT NULL DEFAULT 0 COMMENT '访问次数' AFTER `is_comment`;

