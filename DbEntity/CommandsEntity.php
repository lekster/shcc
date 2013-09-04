<?php

namespace DbEntity;

/**
 * @Entity @Table(name="commands")
 **/
class CommandsEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $ID;  

    /** @Column(type="string") **/
    public $TITLE;

    /** @Column(type="string") **/
    public $COMMAND;

    /** @Column(type="string") **/
    public $URL;

    /** @Column(type="integer") **/
    public $WIDTH;

    /** @Column(type="integer") **/
    public $HEIGHT;

    /** @Column(type="integer") **/
    public $PARENT_ID;

    /** @Column(type="string") **/
    public $SUB_LIST;

    /** @Column(type="string") **/
    public $PARENT_LIST;

    /** @Column(type="integer") **/
    public $PRIORITY;
    
    /** @Column(type="string") **/
    public $WINDOW;

    /** @Column(type="integer") **/
    public $AUTOSTART;

    /** @Column(type="string") **/
    public $TYPE;

    /** @Column(type="integer") **/
    public $MIN_VALUE;

    /** @Column(type="integer") **/
    public $MAX_VALUE;

    /** @Column(type="string") **/
    public $CUR_VALUE;

    /** @Column(type="integer") **/
    public $STEP_VALUE;

    /** @Column(type="string") **/
    public $LINKED_OBJECT;
    
    /** @Column(type="string") **/
    public $LINKED_PROPERTY;

    /** @Column(type="string") **/
    public $ONCHANGE_OBJECT;

    /** @Column(type="string") **/
    public $ONCHANGE_METHOD;

    /** @Column(type="string") **/
    public $ICON;

    /** @Column(type="string") **/
    public $DATA;

    /** @Column(type="integer") **/
    public $SCRIPT_ID;

    /** @Column(type="integer") **/
    public $AUTO_UPDATE;

    /** @Column(type="string") **/
    public $CODE;

    /** @Column(type="string") **/
    public $SYSTEM;

    /** @Column(type="integer") **/
    public $EXT_ID;

    /** @Column(type="integer") **/
    public $VISIBLE_DELAY;

    /** @Column(type="integer") **/
    public $INLINE;
}

/*
DROP TABLE IF EXISTS `commands`;
CREATE TABLE IF NOT EXISTS `commands` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `COMMAND` varchar(255) NOT NULL DEFAULT '',
  `URL` varchar(255) NOT NULL DEFAULT '',
  `WIDTH` int(10) NOT NULL DEFAULT '0',
  `HEIGHT` int(10) NOT NULL DEFAULT '0',
  `PARENT_ID` int(10) NOT NULL DEFAULT '0',
  `SUB_LIST` text,
  `PARENT_LIST` text,
  `PRIORITY` int(10) NOT NULL DEFAULT '0',
  `WINDOW` varchar(255) NOT NULL DEFAULT '',
  `AUTOSTART` int(3) NOT NULL DEFAULT '0',
  `TYPE` char(50) NOT NULL DEFAULT '',
  `MIN_VALUE` int(10) NOT NULL DEFAULT '0',
  `MAX_VALUE` int(10) NOT NULL DEFAULT '0',
  `CUR_VALUE` varchar(255) NOT NULL DEFAULT '',
  `STEP_VALUE` int(10) NOT NULL DEFAULT '0',

  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `ONCHANGE_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `ONCHANGE_METHOD` varchar(255) NOT NULL DEFAULT '',
  `ICON` varchar(50) NOT NULL DEFAULT '',
  `DATA` text,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  `AUTO_UPDATE` int(10) NOT NULL DEFAULT '0',
  `CODE` text,
  `SYSTEM` varchar(255) NOT NULL DEFAULT '',
  `EXT_ID` int(10) NOT NULL DEFAULT '0',
  `VISIBLE_DELAY` int(10) NOT NULL DEFAULT '0',
  `INLINE` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=98 ;

*/