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










##########新增表
DROP TABLE IF EXISTS `educational`;
CREATE TABLE `educational` (
  `EduID` int(11) NOT NULL AUTO_INCREMENT COMMENT '学制id',
  `EduName` varchar(50) NOT NULL COMMENT '学制名称',
  `GradeNum` int(11) DEFAULT NULL COMMENT '年级数量',
  PRIMARY KEY (`EduID`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of educational
-- ----------------------------
INSERT INTO `educational` VALUES ('1', '小学六年制', '6');
INSERT INTO `educational` VALUES ('2', '小学五年制', '5');
INSERT INTO `educational` VALUES ('3', '初中三年制', '3');
INSERT INTO `educational` VALUES ('4', '初中四年制', '4');
INSERT INTO `educational` VALUES ('5', '高中三年制', '3');
INSERT INTO `educational` VALUES ('6', '完中六年制', '6');
INSERT INTO `educational` VALUES ('7', '九年义务教育制', '9');
INSERT INTO `educational` VALUES ('8', '实验学校12年制', '12');


# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.6.35)
# Database: eschool
# Generation Time: 2017-08-15 08:09:11 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table subject
# ------------------------------------------------------------

DROP TABLE IF EXISTS `subject`;

CREATE TABLE `subject` (
  `CourseNO` int(11) NOT NULL AUTO_INCREMENT COMMENT '科目编号',
  `CName` varchar(50) NOT NULL COMMENT '科目名称',
  PRIMARY KEY (`CourseNO`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `subject` WRITE;
/*!40000 ALTER TABLE `subject` DISABLE KEYS */;

INSERT INTO `subject` (`CourseNO`, `CName`)
VALUES
	(1,'语文'),
	(2,'数学'),
	(3,'英语'),
	(4,'品德与社会'),
	(5,'科学'),
	(6,'体育'),
	(7,'音乐'),
	(8,'美术'),
	(9,'生物'),
	(10,'历史'),
	(11,'地理'),
	(12,'化学'),
	(13,'物理'),
	(14,'书法'),
	(15,'计算机'),
	(16,'综合');

/*!40000 ALTER TABLE `subject` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

ALTER TABLE `exercise` ADD `EvalNO` INT NOT NULL AFTER `SubmitTime`;
ALTER TABLE `teahomework` ADD `CourseID` INT NOT NULL AFTER `EvalNO`;
ALTER TABLE `exercise` ADD `EvalScore` VARCHAR(255) NOT NULL AFTER `EvalNO`;
ALTER TABLE `teahomework` ADD `EvalScore` VARCHAR(255) NOT NULL AFTER `CourseID`;
ALTER TABLE `answerinfo` ADD `EvalScore` VARCHAR(255) NOT NULL AFTER `id`;







