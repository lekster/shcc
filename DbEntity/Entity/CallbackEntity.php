<?php

namespace Src\Entity;


/**
 * @Entity @Table(name="callback")
 **/
class CallbackEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $callback_id;  

    /** @Column(type="string") **/
    public $template_url;

    /** @Column(type="integer") **/
    public $user_id;
    
     /** @Column(type="boolean")**/
    public $is_active;

    /** 
	*  @ManyToOne(targetEntity="UserEntity")
	*  @JoinColumn(name="user_id", referencedColumnName="user_id")
    **/
    public $users;

}


