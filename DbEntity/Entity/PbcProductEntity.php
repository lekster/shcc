<?php

namespace Src\Entity;

/**
 * @Entity @Table(name="pbc_product")
 **/
class PbcProductEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $pbc_product_id;  

   /** @Column(type="string" ) **/
    public $foreign_name;

    /** @Column(type="string" ) **/
    public $pbc_name;

    /** @Column(type="boolean")**/
    public $is_active = false;

    /** @Column(type="string" ) **/
    public $pbc_password;
    
}