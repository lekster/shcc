<?php

namespace Src\Entity;


/**
 * @Entity @Table(name="sms_payment")
 **/
class SmsPaymentEntity
{
	const STATUS_WAIT = 0;
	const STATUS_SEND = 1;
	const STATUS_SUCCESS = 31;
	const STATUS_FAIL = 41;

    /** @Id @Column(type="guid") **/
    public $sms_payment_id;  

    /** @Column(type="integer") **/
    public $pbc_product_id;

    /** @Column(type="integer") **/
    public $payment_sum;

    /** @Column(type="integer") **/
    public $payment_status;

    /** @Column(type="string")**/
    public $date_create;

}