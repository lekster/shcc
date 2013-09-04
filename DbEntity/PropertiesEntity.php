<?php

namespace DbEntity;

/**
 * @Entity @Table(name="properties")
 **/
class PropertiesEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $ID;  

    /** @Column(type="integer") **/
    public CLASS_ID;

    /** @Column(type="string") **/
    public $TITLE;

    /** @Column(type="string") **/
    public $DESCRIPTION;

    /** @Column(type="integer") **/
    public $OBJECT_ID;

    /** @Column(type="integer") **/
    public $KEEP_HISTORY;

    /** @Column(type="string") **/
    public $NAME;

    /** @Column(type="string") **/
    public $TYPE;
    
    /** @Column(type="string") **/
    public $ONCHANGE;
    
}

/*
DROP TABLE IF EXISTS `properties`;
CREATE TABLE IF NOT EXISTS `properties` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CLASS_ID` int(10) NOT NULL DEFAULT '0',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `DESCRIPTION` text,
  `OBJECT_ID` int(10) NOT NULL DEFAULT '0',
  `KEEP_HISTORY` int(10) NOT NULL DEFAULT '0',
  `ONCHANGE` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=79 ;
*/
