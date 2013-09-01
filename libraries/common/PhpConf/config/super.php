<?php

return array(
    'host'  =>  array(
        'mysql'     => 'base.mysql.immo',
        'pg'        => 'base.pg.immo'
    ),
    'user'  =>  array(
        'mysql'     => 'user',
        'pg'        => 'basic_user'
    ),
    'pass'  =>  array(
        'mysql'     => 'secret_1',
        'pg'        => 'secret_2'
    ),
    'msg'   =>  array(
        'hello'     => 'Hi %user%, how are you?',
        'a'         => '%user%, did you have a %thing%?'
    )
);

?>