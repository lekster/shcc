<?php

interface Immo_MobileCommerce_Lockable
{
    public function getOldProcessRunTime();
    public function getOldProcessPid();
    public function isLocked();
    public function unlock();
    public function lock();
}
