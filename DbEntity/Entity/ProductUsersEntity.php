<?php

namespace Src\Entity;

/**
 * @Entity @Table(name="product_users")
 **/
class ProductUsersEntity
{

    /** @Id @Column(type="integer")  **/
    public $pbc_product_id;
    
    /** @Id @Column(type="integer")  **/
    public $user_id;

    /** @Column(type="boolean")**/
    public $is_default = false;

    /** @Column(type="boolean")**/
    public $is_active = false;
}