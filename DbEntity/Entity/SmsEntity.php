<?php

namespace Src\Entity;


/**
 * @Entity @Table(name="sms")
 **/
class SmsEntity
{

	const SMS_TYPE_SMS = 1;
	const SMS_TYPE_SMS_PAY = 2;

	const SMS_STATUS_WAIT = 0;
	const SMS_STATUS_SEND = 1;
  const SMS_STATUS_DELIVERED = 41;
  const SMS_STATUS_UNDELIVERED = 40;
  const SMS_STATUS_FAIL = -1;
  //const SMS_STATUS_PAYMENT_SENT = 11;

	const SMS_CALLBACK_STATUS_WAIT = 0;
	const SMS_CALLBACK_STATUS_SENDED = 11;
	const SMS_CALLBACK_STATUS_FAIL = 10;


    /** @Id @Column(type="guid") **/
    public $sms_id;  

    /** @Column(type="integer") **/
    public $sms_type_id;

    /** @Column(type="string")**/
    public $msisdn;

    /** @Column(type="string")**/
    public $text;

    /** @Column(type="string")**/
    public $date_create;

    /** @Column(type="string")**/
    public $delay_until;

     /** @Column(type="string")**/
    public $delivery_deadline;

    /** @Column(type="boolean")**/
    public $use_operator_timezone = false;

    /** @Column(type="integer") **/
    public $alpha_number_id;

    /** @Column(type="integer") **/
    public $real_sms_count;

    /** @Column(type="integer") **/
    public $partner_id;

    /** @Column(type="integer") **/
    public $user_id;

    /** @Column(type="string") **/
    public $send_time;

    /** @Column(type="integer") **/
    public $status;

    /** @Column(type="integer") **/
    public $packet_id;

    /** @Column(type="string") **/
    public $delivered_date;

    /** @Column(type="integer") **/
    public $operator_id;

    /** @Column(type="integer") **/
    public $operator_group_id;

    /** @Column(type="integer") **/
    public $callback_status;

}



/*
sms_id uuid NOT NULL,
  sms_type_id smallint NOT NULL DEFAULT 0,
  msisdn bigint NOT NULL,
  text text,
  date_create timestamp without time zone NOT NULL DEFAULT now(),
  delay_until timestamp without time zone,
  delivery_deadline timestamp without time zone,
  use_operator_timezone boolean NOT NULL DEFAULT false,
  alpha_number_id integer NOT NULL,
  real_sms_count smallint NOT NULL,
  partner_id integer NOT NULL,
  user_id bigint NOT NULL,
  send_time timestamp without time zone,
  status smallint NOT NULL DEFAULT 0,
  packet_id bigint,
  delivered_date timestamp without time zone,
  operator_id smallint,
  operator_group_id smallint,
  callback_status smallint NOT NULL DEFAULT 0,

*/

