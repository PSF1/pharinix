-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.6.20


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema miana
--

CREATE DATABASE IF NOT EXISTS miana;
USE miana;
CREATE TABLE `bin-path` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `path` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
INSERT INTO `bin-path` VALUES  (1,'bin/'),
 (2,'bin/html/'),
 (3,'usr/bootstrap-form-builder/bin/'),
 (4,'bin/node_type/'),
 (5,'bin/user/');
CREATE TABLE `booting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `command` varchar(250) NOT NULL,
  `parameters` longtext NOT NULL,
  `priority` int(10) unsigned NOT NULL,
  `ref` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
INSERT INTO `booting` VALUES  (2,'commandHelpWikiExport','',0,'550dab23ad6116.34746021');
CREATE TABLE `node_algo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_owner` int(10) unsigned DEFAULT '0',
  `user_owner` int(10) unsigned DEFAULT '0',
  `access` int(11) DEFAULT '3904',
  `modifier` int(10) unsigned DEFAULT '0',
  `modified` datetime DEFAULT NULL,
  `creator` int(10) unsigned DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `title` varchar(250) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
INSERT INTO `node_algo` VALUES  (1,0,0,3904,0,'2015-04-17 19:41:34',0,'2015-03-26 20:33:13','Hola'),
 (2,21,0,3904,0,NULL,0,NULL,'Grupo'),
 (3,0,23,3904,0,NULL,0,NULL,'Propietario'),
 (4,0,0,15,0,NULL,0,NULL,'Todos');
CREATE TABLE `node_formats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `command` varchar(250) NOT NULL,
  `type` varchar(250) NOT NULL,
  `read` varchar(1) NOT NULL DEFAULT '0',
  `write` varchar(1) NOT NULL DEFAULT '0',
  `default` varchar(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
INSERT INTO `node_formats` VALUES  (1,'formatFieldLongtext','longtext','1','1','1'),
 (2,'formatFieldBool','bool','1','1','1'),
 (3,'formatFieldDouble','double','1','1','1'),
 (4,'formatFieldInteger','integer','1','1','1'),
 (5,'formatFieldString','string','1','1','1'),
 (6,'formatFieldDate','datetime','1','1','1'),
 (7,'formatFieldPassword','password','1','1','1');
CREATE TABLE `node_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_owner` int(10) unsigned DEFAULT '0',
  `user_owner` int(10) unsigned DEFAULT '0',
  `access` int(11) DEFAULT '3904',
  `modifier` int(10) unsigned DEFAULT '0',
  `modified` datetime DEFAULT NULL,
  `creator` int(10) unsigned DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `title` varchar(250) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2371 DEFAULT CHARSET=latin1;
INSERT INTO `node_group` VALUES  (219,0,0,3904,0,NULL,0,NULL,'sudoers'),
 (21,0,0,3904,2,'2015-04-02 23:44:27',2,'2015-04-02 23:44:27','PSF'),
 (1713,0,0,3904,0,'2015-04-18 12:02:01',0,'2015-04-18 12:02:01','testlogin2');
CREATE TABLE `node_nuevo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `modifier` int(10) unsigned DEFAULT '0',
  `modified` datetime DEFAULT NULL,
  `creator` int(10) unsigned DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `access` int(11) DEFAULT '3904',
  `group_owner` int(10) unsigned DEFAULT '0',
  `user_owner` int(10) unsigned DEFAULT '0',
  `title` varchar(250) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `node_otracosa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `modifier` int(10) unsigned DEFAULT '0',
  `modified` datetime DEFAULT NULL,
  `creator` int(10) unsigned DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `access` int(11) DEFAULT '3904',
  `group_owner` int(10) unsigned DEFAULT '0',
  `user_owner` int(10) unsigned DEFAULT '0',
  `title` varchar(250) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `node_relation_user_groups_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type1` int(10) unsigned NOT NULL,
  `type2` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type1` (`type1`),
  KEY `type2` (`type2`)
) ENGINE=MyISAM AUTO_INCREMENT=2936 DEFAULT CHARSET=latin1;
INSERT INTO `node_relation_user_groups_group` VALUES  (64,23,219),
 (21,23,21),
 (2144,1713,1713);
CREATE TABLE `node_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `created` datetime NOT NULL,
  `creator_node_user` int(10) unsigned NOT NULL COMMENT '''User ID''',
  `modified` datetime NOT NULL,
  `modifier_node_user` int(10) unsigned NOT NULL COMMENT '''User ID''',
  `locked` varchar(1) DEFAULT '0' COMMENT 'System node type',
  `label_field` varchar(250) NOT NULL COMMENT 'Field that I can use to list',
  `user_owner` int(10) unsigned NOT NULL,
  `group_owner` int(10) unsigned NOT NULL,
  `access` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11076 DEFAULT CHARSET=latin1;
INSERT INTO `node_type` VALUES  (8,'algo','2015-03-21 13:21:14',0,'2015-04-17 18:38:55',0,'0','title',0,0,3904),
 (555,'user','2015-03-21 18:56:43',0,'2015-04-17 17:14:07',0,'1','name',0,0,3904),
 (556,'group','2015-03-21 19:10:19',0,'2015-04-17 17:14:16',0,'1','title',0,0,3904),
 (8180,'otracosa','2015-04-11 17:59:42',0,'2015-04-11 17:59:42',0,'0','title',0,0,3904),
 (8935,'nuevo','2015-04-17 17:03:39',0,'2015-04-17 17:03:39',0,'0','title',0,0,3904);
CREATE TABLE `node_type_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `type` varchar(250) NOT NULL,
  `len` int(10) unsigned NOT NULL,
  `required` varchar(1) NOT NULL DEFAULT '0' COMMENT 'Field required',
  `readonly` varchar(1) NOT NULL DEFAULT '0' COMMENT 'Not writeble field',
  `locked` varchar(1) NOT NULL DEFAULT '0' COMMENT 'The cant be erased of the type',
  `node_type` int(10) unsigned NOT NULL,
  `default` longtext NOT NULL COMMENT 'Default value',
  `label` varchar(250) NOT NULL,
  `help` longtext NOT NULL,
  `multi` varchar(1) NOT NULL DEFAULT '0' COMMENT 'Multivalue',
  `iskey` varchar(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=102933 DEFAULT CHARSET=latin1;
INSERT INTO `node_type_field` VALUES  (12403,'modifier','user',0,'0','1','1',556,'0','Modifier user','','0','0'),
 (12402,'modified','datetime',0,'0','1','1',556,'','Modified date','','0','0'),
 (12401,'creator','user',0,'0','1','1',556,'0','User creator','','0','0'),
 (12400,'created','datetime',0,'0','1','1',556,'','Creation date','','0','0'),
 (12399,'title','string',250,'1','0','0',556,'','Title','A title string for this node.','0','0'),
 (12398,'groups','group',0,'0','0','0',555,'0','Groups','User groups','1','0'),
 (12397,'mail','string',250,'0','0','0',555,'','Mail','User email to login.','0','1'),
 (12396,'pass','password',250,'0','0','0',555,'','Password','Password to access.','0','0'),
 (12395,'name','string',250,'0','0','0',555,'','User name','It can be any.','0','0'),
 (12394,'modifier','user',0,'0','1','1',555,'0','Modifier user','','0','0'),
 (12393,'modified','datetime',0,'0','1','1',555,'','Modified date','','0','0'),
 (12392,'creator','user',0,'0','1','1',555,'0','User creator','','0','0'),
 (12391,'created','datetime',0,'0','1','1',555,'','Creation date','','0','0'),
 (9121,'title','string',250,'1','0','0',8,'','Title','A title string for this node.','0','0'),
 (9122,'created','datetime',0,'0','1','1',8,'','Creation date','','0','0'),
 (9123,'creator','user',0,'0','1','1',8,'0','User creator','','0','0'),
 (9124,'modified','datetime',0,'0','1','1',8,'','Modified date','','0','0'),
 (9125,'modifier','user',0,'0','1','1',8,'0','Modifier user','','0','0'),
 (12390,'title','string',250,'1','0','0',555,'','Title','A title string for this node.','0','0'),
 (83178,'group_owner','group',0,'0','0','1',8,'0','Group','Owner group','0','0'),
 (83177,'user_owner','user',0,'0','0','1',8,'0','Owner','Owner user','0','0'),
 (83176,'access','nodesec',0,'0','0','1',8,'3904','Access','Access control flags.','0','0'),
 (83155,'group_owner','group',0,'0','0','1',556,'0','Group','Owner group','0','0'),
 (83153,'user_owner','user',0,'0','0','1',556,'0','Owner','Owner user','0','0'),
 (83154,'group_owner','group',0,'0','0','1',555,'0','Group','Owner group','0','0'),
 (83152,'user_owner','user',0,'0','0','1',555,'0','Owner','Owner user','0','0'),
 (83151,'access','nodesec',0,'0','0','1',556,'3904','Access','Access control flags.','0','0'),
 (83150,'access','nodesec',0,'0','0','1',555,'3904','Access','Access control flags.','0','0'),
 (76155,'modifier','user',0,'0','1','1',8180,'0','Modifier user','','0','0'),
 (76154,'modified','datetime',0,'0','1','1',8180,'','Modified date','','0','0'),
 (76153,'creator','user',0,'0','1','1',8180,'0','User creator','','0','0'),
 (65416,'order','integer',0,'0','0','0',7,'','Field','','0','0'),
 (76152,'created','datetime',0,'0','1','1',8180,'','Creation date','','0','0'),
 (65415,'modifier','user',0,'0','1','1',7,'0','Modifier user','','0','0'),
 (65414,'modified','datetime',0,'0','1','1',7,'','Modified date','','0','0'),
 (65413,'creator','user',0,'0','1','1',7,'0','User creator','','0','0'),
 (65412,'created','datetime',0,'0','1','1',7,'','Creation date','','0','0'),
 (65411,'access','nodesec',0,'0','0','1',7,'3904','Access','Access control flags.','0','0'),
 (65410,'group_owner','group',0,'0','0','1',7,'0','Group','Owner group','0','0'),
 (65409,'user_owner','user',0,'0','0','1',7,'0','Owner','Owner user','0','0'),
 (65408,'title','string',250,'1','0','0',7,'','Title','A title string for this node.','0','0'),
 (76151,'access','nodesec',0,'0','0','1',8180,'3904','Access','Access control flags.','0','0'),
 (76150,'group_owner','group',0,'0','0','1',8180,'0','Group','Owner group','0','0'),
 (76149,'user_owner','user',0,'0','0','1',8180,'0','Owner','Owner user','0','0'),
 (76148,'title','string',250,'1','0','0',8180,'','Title','A title string for this node.','0','0'),
 (83149,'modifier','user',0,'0','1','1',8935,'0','Modifier user','','0','0'),
 (83148,'modified','datetime',0,'0','1','1',8935,'','Modified date','','0','0'),
 (83147,'creator','user',0,'0','1','1',8935,'0','User creator','','0','0'),
 (83142,'title','string',250,'1','0','0',8935,'','Title','A title string for this node.','0','0'),
 (83146,'created','datetime',0,'0','1','1',8935,'','Creation date','','0','0'),
 (83145,'access','nodesec',0,'0','0','1',8935,'3904','Access','Access control flags.','0','0'),
 (83144,'group_owner','group',0,'0','0','1',8935,'0','Group','Owner group','0','0'),
 (83143,'user_owner','user',0,'0','0','1',8935,'0','Owner','Owner user','0','0');
CREATE TABLE `node_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_owner` int(10) unsigned DEFAULT '0',
  `user_owner` int(10) unsigned DEFAULT '0',
  `access` int(11) DEFAULT '3904',
  `groups` int(10) unsigned DEFAULT '0',
  `mail` varchar(250) DEFAULT '',
  `pass` varchar(250) DEFAULT NULL,
  `name` varchar(250) DEFAULT '',
  `modifier` int(10) unsigned DEFAULT '0',
  `modified` datetime DEFAULT NULL,
  `creator` int(10) unsigned DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `title` varchar(250) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2371 DEFAULT CHARSET=latin1;
INSERT INTO `node_user` VALUES  (2,0,0,3904,0,'guest@localhost','','guest',1,'0000-00-00 00:00:00',1,'0000-00-00 00:00:00','Guest'),
 (23,0,0,3904,0,'aaaaa976@gmail.com','0cc175b9c0f1b6a831c399e269772661','PSF',2,'2015-04-02 23:44:27',2,'2015-04-02 23:44:27','Pedro PelÃ¡ez'),
 (1713,0,0,3904,0,'testlogin2@localhost','124653cf9d6a29a3d4b5f264b1105dec','testlogin2',0,'2015-04-18 12:02:01',0,'2015-04-18 12:02:01','testlogin2');
CREATE TABLE `page-blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idpage` int(10) unsigned NOT NULL,
  `idcol` varchar(250) NOT NULL,
  `command` varchar(250) NOT NULL,
  `parameters` longtext NOT NULL,
  `priority` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46886 DEFAULT CHARSET=latin1;
INSERT INTO `page-blocks` VALUES  (9,1,'colRight','executeCommandOnline','',0),
 (10,0,'footCopy','echoHTML','html=%3Ch3%3EPharinix+Copyright+%C2%A9+%3C%3Fphp+echo+date%28%22Y%22%29%3B+%3F%3E+Pedro+Pelaez%3C%2Fh3%3E%0D%0A%3Cdiv%3EThis+program+is+free+software%3B+you+can+redistribute+it+and%2For+modify+it+under+the+terms+of+the+GNU+General+Public+License+as+published+by+the+Free+Software+Foundation%3B+either+version+2+of+the+License%2C+or+%28at+your+option%29+any+later+version.%3Cbr%2F%3E%0D%0A%3Cbr%2F%3E%0D%0AThis+program+is+distributed+in+the+hope+that+it+will+be+useful%2C+but+WITHOUT+ANY+WARRANTY%3B+without+even+the+implied+warranty+of+MERCHANTABILITY+or+FITNESS+FOR+A+PARTICULAR+PURPOSE.++See+the+GNU+General+Public+License+for+more+details.%3Cbr%2F%3E%0D%0A%3Cbr%2F%3E%0D%0AYou+should+have+received+a+copy+of+the+GNU+General+Public+License+along+with+this+program%3B+if+not%2C+write+to+the+Free+Software+Foundation%2C+Inc.%2C+59+Temple+Place+-+Suite+330%2C+Boston%2C+MA++02111-1307%2C+USA.%0D%0A%3C%2Fdiv%3E',0),
 (12,3,'content','echoHTML','html=<h1>Page not found: <?php echo \"\'{$_GET[\"rewrite\"]}\'\";?></h1>',0),
 (14,1,'colLeft','textUrlEncoder','',0),
 (9922,9895,'content','getNodeHtml','nodetype=group&node=7',0),
 (13,4,'content','commandHelp',' ',0),
 (21,10,'content','templateEditor','',0),
 (22,11,'content','iconsList','',0),
 (9919,9892,'content','getNodeHtml','nodetype=user&node=7',0),
 (9918,9891,'content','getNodeHtml','nodetype=group&node=5',0),
 (428,1,'colLeft','manHelpOnline','',1),
 (3214,3191,'content','getNodeTypeDefHtml','nodetype=user',0),
 (3215,3192,'content','getNodeTypeDefHtml','nodetype=group',0),
 (994,972,'content','getNodeTypeDefHtml','nodetype=algo',0),
 (9923,9896,'content','getNodeHtml','nodetype=user&node=9',0),
 (9920,9893,'content','getNodeHtml','nodetype=group&node=6',0),
 (9912,9885,'content','getNodeHtml','nodetype=group&node=2',0),
 (9913,9886,'content','getNodeHtml','nodetype=user&node=4',0),
 (3492,3469,'content','getNodeHtml','nodetype=algo&node=1',0),
 (9921,9894,'content','getNodeHtml','nodetype=user&node=8',0),
 (9917,9890,'content','getNodeHtml','nodetype=user&node=6',0),
 (9914,9887,'content','getNodeHtml','nodetype=group&node=3',0),
 (9915,9888,'content','getNodeHtml','nodetype=user&node=5',0),
 (9916,9889,'content','getNodeHtml','nodetype=group&node=4',0),
 (9924,9897,'content','getNodeHtml','nodetype=group&node=8',0),
 (9925,9898,'content','getNodeHtml','nodetype=user&node=10',0),
 (9926,9899,'content','getNodeHtml','nodetype=group&node=9',0),
 (9927,9900,'content','getNodeHtml','nodetype=user&node=11',0),
 (9928,9901,'content','getNodeHtml','nodetype=group&node=10',0),
 (9929,9902,'content','getNodeHtml','nodetype=user&node=12',0),
 (9930,9903,'content','getNodeHtml','nodetype=group&node=11',0),
 (9931,9904,'content','getNodeHtml','nodetype=user&node=13',0),
 (9932,9905,'content','getNodeHtml','nodetype=group&node=12',0),
 (9934,9907,'content','getNodeHtml','nodetype=group&node=13',0),
 (9936,9909,'content','getNodeHtml','nodetype=group&node=14',0),
 (9941,9914,'content','getNodeHtml','nodetype=user&node=18',0),
 (9940,9913,'content','getNodeHtml','nodetype=group&node=16',0),
 (9950,9923,'content','getNodeHtml','nodetype=group&node=21',0),
 (9951,9924,'content','getNodeHtml','nodetype=user&node=23',0),
 (46873,46837,'bool_write','formatFieldBool','fieldname=testboolwrite&toread=0&towrite=1&value&required=1&readonly=0&system=0&multivalued=0&label=Bool&help=A bool field',0),
 (46872,46837,'longtext_read','formatFieldLongtext','fieldname=testlongtextread&toread=1&towrite=0&value&required=1&readonly=0&system=0&multivalued=0&value=longtext&label=Longtext&help=A long text field',0),
 (46871,46837,'longtext_write','formatFieldLongtext','fieldname=testlongtextwrite&toread=0&towrite=1&value&required=1&readonly=0&system=0&multivalued=0&default=longtext&label=Longtext&help=A long text field',0),
 (45780,0,'mainMenu','menuInlineToHTML','',0),
 (46874,46837,'bool_read','formatFieldBool','fieldname=testboolwrite&toread=1&towrite=0&value=1&required=1&readonly=0&system=0&multivalued=0&label=Bool&help=A bool field',0),
 (38935,38902,'content','getNodeHtml','nodetype=group&node=1713',0),
 (38936,38903,'content','getNodeHtml','nodetype=user&node=1713',0),
 (21426,21393,'content','getNodeHtml','nodetype=user&node=67',0),
 (21425,21392,'content','getNodeHtml','nodetype=group&node=66',0),
 (21423,21390,'content','getNodeHtml','nodetype=group&node=65',0),
 (21424,21391,'content','getNodeHtml','nodetype=user&node=66',0),
 (46876,46837,'double_read','formatFieldDouble','fieldname=testdoubleread&toread=1&towrite=0&value=100&required=1&readonly=0&system=0&multivalued=0&label=Double&help=A double field',0),
 (36346,36313,'content','getNodeTypeDefHtml','nodetype=nuevo',0),
 (46875,46837,'double_write','formatFieldDouble','fieldname=testdoublewrite&toread=0&towrite=1&value=100&required=1&readonly=0&system=0&multivalued=0&label=Double&help=A double field',0),
 (33292,33259,'content','getNodeHtml','nodetype=user&node=1327',0),
 (33291,33258,'content','getNodeHtml','nodetype=group&node=1327',0),
 (32808,32775,'content','getNodeTypeDefHtml','nodetype=otracosa',0),
 (46877,46837,'integer_write','formatFieldInteger','fieldname=testintegerwrite&toread=0&towrite=1&value=100&required=1&readonly=0&system=0&multivalued=0&label=Integer&help=A integer field',0),
 (46878,46837,'integer_read','formatFieldInteger','fieldname=testintegerread&toread=1&towrite=0&value=100&required=1&readonly=0&system=0&multivalued=0&label=Integer&help=A integer field',0),
 (46879,46837,'string_write','formatFieldString','fieldname=teststringwrite&toread=0&towrite=1&value=String&required=1&readonly=0&system=0&multivalued=0&label=String&help=A string field',0),
 (46880,46837,'string_read','formatFieldString','fieldname=teststringread&toread=1&towrite=0&value=String&required=1&readonly=0&system=0&multivalued=0&label=String&help=A string field',0),
 (46881,46837,'password_write','formatFieldPassword','fieldname=testpasswordwrite&toread=0&towrite=1&value=password&required=1&readonly=0&system=0&multivalued=0&label=Password&help=A password field',0),
 (46882,46837,'password_read','formatFieldPassword','fieldname=testpasswordread&toread=1&towrite=0&value=password&required=1&readonly=0&system=0&multivalued=0&label=Password&help=A password field',0),
 (46883,46837,'datetime_write','formatFieldDate','fieldname=testdatewrite&toread=0&towrite=1&value=24-04-2015&required=1&readonly=0&system=0&multivalued=0&label=Date&help=A datetime field',0),
 (46884,46837,'datetime_read','formatFieldDate','fieldname=testdateread&toread=1&towrite=0&value=24-04-2015&required=1&readonly=0&system=0&multivalued=0&label=Date&help=A datetime field',0),
 (46885,46837,'headblock','echoHTML','html=<legend>Formatters demo</legend>',0);
CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `template` varchar(250) NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` longtext NOT NULL,
  `keys` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46838 DEFAULT CHARSET=latin1;
INSERT INTO `pages` VALUES  (1,'home','etc/templates/pages/two_column.xml','Home','',''),
 (4,'help/command','etc/templates/pages/default.xml','Command\'s help','',''),
 (3,'404','etc/templates/pages/default.xml','Page not found','',''),
 (9897,'node_type_group_8','etc/templates/pages/default.xml','Node 8','',''),
 (10,'template/editor','etc/templates/pages/default.xml','Template editor','',''),
 (11,'help/icons','etc/templates/pages/default.xml','Icons list','',''),
 (9896,'node_type_user_9','etc/templates/pages/default.xml','Node 9','',''),
 (9895,'node_type_group_7','etc/templates/pages/default.xml','Node 7','',''),
 (972,'node_type_algo','etc/templates/pages/default.xml','algo node type','',''),
 (3191,'node_type_user','etc/templates/pages/default.xml','user node type','',''),
 (3192,'node_type_group','etc/templates/pages/default.xml','group node type','',''),
 (3469,'node_type_algo_1','etc/templates/pages/default.xml','Node 1','',''),
 (9885,'node_type_group_2','etc/templates/pages/default.xml','Node 2','',''),
 (9886,'node_type_user_4','etc/templates/pages/default.xml','Node 4','',''),
 (9887,'node_type_group_3','etc/templates/pages/default.xml','Node 3','',''),
 (9888,'node_type_user_5','etc/templates/pages/default.xml','Node 5','',''),
 (9889,'node_type_group_4','etc/templates/pages/default.xml','Node 4','',''),
 (9890,'node_type_user_6','etc/templates/pages/default.xml','Node 6','',''),
 (9891,'node_type_group_5','etc/templates/pages/default.xml','Node 5','',''),
 (9892,'node_type_user_7','etc/templates/pages/default.xml','Node 7','',''),
 (9893,'node_type_group_6','etc/templates/pages/default.xml','Node 6','',''),
 (9894,'node_type_user_8','etc/templates/pages/default.xml','Node 8','',''),
 (9898,'node_type_user_10','etc/templates/pages/default.xml','Node 10','',''),
 (9899,'node_type_group_9','etc/templates/pages/default.xml','Node 9','',''),
 (9900,'node_type_user_11','etc/templates/pages/default.xml','Node 11','',''),
 (9901,'node_type_group_10','etc/templates/pages/default.xml','Node 10','',''),
 (9902,'node_type_user_12','etc/templates/pages/default.xml','Node 12','',''),
 (9903,'node_type_group_11','etc/templates/pages/default.xml','Node 11','',''),
 (9904,'node_type_user_13','etc/templates/pages/default.xml','Node 13','',''),
 (9905,'node_type_group_12','etc/templates/pages/default.xml','Node 12','',''),
 (9907,'node_type_group_13','etc/templates/pages/default.xml','Node 13','',''),
 (9909,'node_type_group_14','etc/templates/pages/default.xml','Node 14','',''),
 (9914,'node_type_user_18','etc/templates/pages/default.xml','Node 18','',''),
 (9913,'node_type_group_16','etc/templates/pages/default.xml','Node 16','',''),
 (9923,'node_type_group_21','etc/templates/pages/default.xml','Node 21','',''),
 (9924,'node_type_user_23','etc/templates/pages/default.xml','Node 23','',''),
 (46837,'help/formatters','etc/templates/pages/formatter.xml','Formatters gallery','',''),
 (21393,'node_type_user_67','etc/templates/pages/default.xml','Node 67','',''),
 (21392,'node_type_group_66','etc/templates/pages/default.xml','Node 66','',''),
 (21390,'node_type_group_65','etc/templates/pages/default.xml','Node 65','',''),
 (21391,'node_type_user_66','etc/templates/pages/default.xml','Node 66','',''),
 (38902,'node_type_group_1713','etc/templates/pages/default.xml','Node 1713','',''),
 (36313,'node_type_nuevo','etc/templates/pages/default.xml','nuevo node type','',''),
 (38903,'node_type_user_1713','etc/templates/pages/default.xml','Node 1713','',''),
 (33259,'node_type_user_1327','etc/templates/pages/default.xml','Node 1327','',''),
 (33258,'node_type_group_1327','etc/templates/pages/default.xml','Node 1327','',''),
 (32775,'node_type_otracosa','etc/templates/pages/default.xml','otracosa node type','','');
CREATE TABLE `sec` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(250) NOT NULL,
  `permissions` int(10) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL COMMENT 'User owner',
  `group` int(10) unsigned NOT NULL COMMENT 'Group owner',
  PRIMARY KEY (`id`),
  KEY `path` (`path`),
  KEY `group` (`group`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `url_rewrite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` longtext,
  `rewriteto` longtext,
  PRIMARY KEY (`id`),
  KEY `url` (`url`(250))
) ENGINE=MyISAM AUTO_INCREMENT=46946 DEFAULT CHARSET=latin1;
INSERT INTO `url_rewrite` VALUES  (1,'home','command=pageToHTML&page=home'),
 (3,'help/command','command=pageToHTML&page=help/command'),
 (9945,'node/group/7','command=pageToHTML&page=node_type_group_7'),
 (13,'template/editor','command=pageToHTML&page=template/editor'),
 (14,'help/icons','command=pageToHTML&page=help/icons'),
 (9947,'node/group/8','command=pageToHTML&page=node_type_group_8'),
 (986,'node/type/algo','command=pageToHTML&page=node_type_algo'),
 (3209,'node/type/user','command=pageToHTML&page=node_type_user'),
 (3210,'node/type/group','command=pageToHTML&page=node_type_group'),
 (3489,'node/algo/1','command=pageToHTML&page=node_type_algo_1'),
 (9935,'node/group/2','command=pageToHTML&page=node_type_group_2'),
 (9937,'node/group/3','command=pageToHTML&page=node_type_group_3'),
 (9939,'node/group/4','command=pageToHTML&page=node_type_group_4'),
 (9941,'node/group/5','command=pageToHTML&page=node_type_group_5'),
 (21461,'node/group/65','command=pageToHTML&page=node_type_group_65'),
 (9943,'node/group/6','command=pageToHTML&page=node_type_group_6'),
 (21464,'node/user/67','command=pageToHTML&page=node_type_user_67'),
 (21462,'node/user/66','command=pageToHTML&page=node_type_user_66'),
 (9949,'node/group/9','command=pageToHTML&page=node_type_group_9'),
 (9951,'node/group/10','command=pageToHTML&page=node_type_group_10'),
 (21463,'node/group/66','command=pageToHTML&page=node_type_group_66'),
 (9953,'node/group/11','command=pageToHTML&page=node_type_group_11'),
 (9954,'node/user/1','command=pageToHTML&page=node_type_user_1'),
 (9955,'node/group/12','command=pageToHTML&page=node_type_group_12'),
 (9957,'node/group/13','command=pageToHTML&page=node_type_group_13'),
 (9959,'node/group/14','command=pageToHTML&page=node_type_group_14'),
 (9964,'node/user/2','command=pageToHTML&page=node_type_user_2'),
 (9963,'node/group/16','command=pageToHTML&page=node_type_group_16'),
 (9974,'node/user/23','command=pageToHTML&page=node_type_user_23'),
 (9973,'node/group/21','command=pageToHTML&page=node_type_group_21'),
 (46945,'help/console','command=pageToHTML&page=home'),
 (46944,'help/formatters','command=pageToHTML&page=help/formatters'),
 (38998,'node/group/1713','command=pageToHTML&page=node_type_group_1713'),
 (38999,'node/user/1713','command=pageToHTML&page=node_type_user_1713'),
 (36405,'node/type/nuevo','command=pageToHTML&page=node_type_nuevo'),
 (33349,'node/user/1327','command=pageToHTML&page=node_type_user_1327'),
 (33348,'node/group/1327','command=pageToHTML&page=node_type_group_1327'),
 (32864,'node/type/otracosa','command=pageToHTML&page=node_type_otracosa');



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
