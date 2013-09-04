<?php

namespace DbEntity;

/**
 * @Entity @Table(name="pvalues")
 **/
class PvaluesEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $ID;  

    /** @Column(type="integer") **/
    public $PROPERTY_ID;

    /** @Column(type="integer") **/
    public $OBJECT_ID;

    /** @Column(type="string") **/
    public $VALUE;

    /** @Column(type="string") **/
    public $UPDATED;
}


/*
DROP TABLE IF EXISTS `pvalues`;
CREATE TABLE IF NOT EXISTS `pvalues` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PROPERTY_ID` int(10) NOT NULL DEFAULT '0',
  `OBJECT_ID` int(10) NOT NULL DEFAULT '0',
  `VALUE` text NOT NULL,
  `UPDATED` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=99 ;

*/