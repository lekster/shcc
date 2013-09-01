<?php

namespace Src\Entity;


/**
 * @Entity @Table(name="ip_address")
 **/
class IpAddressEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $ip_address_id;  

    /** @Column(type="string") **/
    public $ip_address_mask;
    
     /** @Column(type="boolean")**/
    public $is_active = true;

    
    /**
     * @ManyToMany(targetEntity="UserEntity")
     * @JoinTable(name="users_ips",
     *      joinColumns={@JoinColumn(name="ip_address_id", referencedColumnName="ip_address_id")},
     *      inverseJoinColumns={@JoinColumn(name="user_id", referencedColumnName="user_id")}
     *      )
     **/
    //private $users;
/*
     public function __construct() {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }
*/
    
}



