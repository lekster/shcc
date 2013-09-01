<?php

namespace DbEntity;

/**
 * @Entity @Table(name="settings")
 **/
class SettingsEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $ID;  

    /** @Column(type="integer") **/
    public $PRIORITY;

    /** @Column(type="integer") **/
    public $HR;

    /** @Column(type="string") **/
    public $TITLE;

    /** @Column(type="string") **/
    public $NAME;

    /** @Column(type="string") **/
    public $TYPE;
    /** @Column(type="string") **/
    public $NOTES;

    /** @Column(type="string") **/
    public $VALUE;
    
    /** @Column(type="string") **/
    public $DEFAULTVALUE;

    /** @Column(type="string") **/
    public $URL;

    /** @Column(type="string") **/
    public $URL_TITLE;

}


/*
CREATE TABLE IF NOT EXISTS `settings` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PRIORITY` int(3) unsigned NOT NULL DEFAULT '0',
  `HR` int(3) unsigned NOT NULL DEFAULT '0',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `NAME` varchar(50) NOT NULL DEFAULT '',
  `TYPE` varchar(59) NOT NULL DEFAULT '',
  `NOTES` text NOT NULL,
  `VALUE` varchar(255) NOT NULL DEFAULT '',
  `DEFAULTVALUE` varchar(255) NOT NULL DEFAULT '',
  `URL` varchar(255) NOT NULL DEFAULT '',
  `URL_TITLE` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=54 ;
*/