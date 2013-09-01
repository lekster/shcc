<?php

return array(
    'extends'   => 'super.php',
    'pass'  =>  array(
        'mysql'     => 'secret_3',
        'pg'        => 'secret_4'
    ),
    'iterator'  => array(
        'a',
        'b',
        'c',
        'd - %d%',
        'E = %x%*%y%*%y%'
    )
);

?>