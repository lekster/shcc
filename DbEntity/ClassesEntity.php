<?php

namespace DbEntity;

/**
 * @Entity @Table(name="classes")
 **/
class SettingsEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $ID;  

    /** @Column(type="string") **/
    public $TITLE;

    /** @Column(type="integer") **/
    public $PARENT_ID;

    /** @Column(type="string") **/
    public $SUB_LIST;

    /** @Column(type="string") **/
    public $PARENT_LIST;

    /** @Column(type="integer") **/
    public $NOLOG;

    /** @Column(type="string") **/
    public $NOTES;

    /** @Column(type="string") **/
    public $DESCRIPTION;
}


/*
DROP TABLE IF EXISTS `classes`;
CREATE TABLE IF NOT EXISTS `classes` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PARENT_ID` int(10) NOT NULL DEFAULT '0',
  `SUB_LIST` text,
  `PARENT_LIST` text,
  `NOLOG` int(3) NOT NULL DEFAULT '0',
  `DESCRIPTION` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;
*/