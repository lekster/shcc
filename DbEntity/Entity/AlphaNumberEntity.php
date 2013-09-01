<?php

namespace Src\Entity;


/**
 * @Entity @Table(name="alpha_number")
 **/
class AlphaNumberEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $alpha_number_id;  

    /** @Column(type="string") **/
    public $name;

    /** @Column(type="integer") **/
    public $partner_id;

    /** @Column(type="string") **/
    public $plug_name;
    
     /** @Column(type="boolean")**/
    public $is_active = true;

    /** @Column(type="string") **/
    public $description;

}

