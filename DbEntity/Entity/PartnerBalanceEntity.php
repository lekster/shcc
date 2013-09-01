<?php

namespace Src\Entity;


/**
 * @Entity @Table(name="partner_balance")
 **/
class PartnerBalanceEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $partner_balance_id;  

    /** @Column(type="integer") **/
    public $sms_balance;

    /** @Column(type="integer") **/
    public $partner_id;

    /** @Column(type="integer") **/
    public $sms_balance_type;
    
    /** @Column(type="integer") **/
    public $sms_balance_limit;

}