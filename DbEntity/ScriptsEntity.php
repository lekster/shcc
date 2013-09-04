<?php

namespace DbEntity;

/**
 * @Entity @Table(name="scripts")
 **/
class ScriptsEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $ID;  

    /** @Column(type="string") **/
    public $TITLE;

    /** @Column(type="string") **/
    public $CODE;

    /** @Column(type="string") **/
    public $DESCRIPTION;

    /** @Column(type="integer") **/
    public $TYPE;

    /** @Column(type="string") **/
    public $XML;

    /** @Column(type="integer") **/
    public $CATEGORY_ID;

    /** @Column(type="string") **/
    public $EXECUTED;

    /** @Column(type="string") **/
    public $EXECUTED_PARAMS;

}

/*
DROP TABLE IF EXISTS `scripts`;
CREATE TABLE IF NOT EXISTS `scripts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `CODE` text,
  `DESCRIPTION` text,
  `TYPE` int(3) unsigned NOT NULL DEFAULT '0',
  `XML` text,
  `CATEGORY_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `EXECUTED` datetime DEFAULT NULL,
  `EXECUTED_PARAMS` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;
*/