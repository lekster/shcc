<?php

namespace DbEntity;

/**
 * @Entity @Table(name="objects")
 **/
class ObjectsEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $ID;  

    /** @Column(type="string") **/
    public $TITLE;

    /** @Column(type="integer") **/
    public $CLASS_ID;

    /** @Column(type="string") **/
    public $DESCRIPTION;

    /** @Column(type="integer") **/
    public $LOCATION_ID;
}


/*
DROP TABLE IF EXISTS `objects`;
CREATE TABLE IF NOT EXISTS `objects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `CLASS_ID` int(10) NOT NULL DEFAULT '0',
  `DESCRIPTION` text,
  `LOCATION_ID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;
*/