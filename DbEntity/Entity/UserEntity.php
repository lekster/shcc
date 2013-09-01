<?php

namespace Src\Entity;


/**
 * @Entity @Table(name="users")
 **/
class UserEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $user_id;  

    /** @Column(type="string") **/
    public $name;
    
    /** @Column(type="integer") **/
    public $partner_id;

    /** @Column(type="string") **/
    public $ssl_name;

    /** @Column(type="boolean")**/
    public $is_ip_restrict = true;

    /** @Column(type="string") **/
    //public $callback_url;

    /** @Column(type="string") **/
    public $description;

     /** @Column(type="boolean")**/
    public $is_active = true;

    
    /**
     * @ManyToMany(targetEntity="IpAddressEntity")
     * @JoinTable(name="users_ips",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="user_id")},
     *      inverseJoinColumns={@JoinColumn(name="ip_address_id", referencedColumnName="ip_address_id")}
     *      )
     **/
    private $ips;


    /**
     * @OneToMany(targetEntity="CallbackEntity", mappedBy="users")
     * @JoinTable(name="callback", joinColumns={@JoinColumn(name="user_id")})
     */
    private $callbacks;


    public function __construct() {
        $this->ips = new \Doctrine\Common\Collections\ArrayCollection();
        $this->callbacks = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getIp()
    {
    	return $this->ips;
    }

    public function getCallbacks()
    {
        return $this->callbacks;
    }


}