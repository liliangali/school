ALTER TABLE `user` CHANGE `Password` `password` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '登录密码';
ALTER TABLE `user` CHANGE `token` `token` VARCHAR(255) NULL DEFAULT NULL COMMENT '令牌';
ALTER TABLE `classes` CHANGE `CreatTime` `CreatTime` VARCHAR(6255) NOT NULL COMMENT '创建班级时间';
ALTER TABLE `course` ADD `SchoolID` INT NOT NULL COMMENT '学校ID' AFTER `CourseName`;


########第二阶段##########
ALTER TABLE `exerciseitem` ADD `Point` VARCHAR(255) NOT NULL COMMENT '分数' AFTER `AvgSpendTime`;
ALTER TABLE `exerciseitem` ADD `Conter` TEXT NOT NULL COMMENT '目标描述' AFTER `Point`;
ALTER TABLE `exercise` ADD `SubmitTime` DATETIME NOT NULL COMMENT '提交活动的时间' AFTER `AvgSpendTime`;
ALTER TABLE `school` ADD `SchoolID` INT NOT NULL AUTO_INCREMENT AFTER `Telephone`, ADD PRIMARY KEY (`SchoolID`);
ALTER TABLE `exercise` CHANGE `ExName` `ExName` VARCHAR(5000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '活动名称';