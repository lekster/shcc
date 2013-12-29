<?php
/*
* @version 0.2 (wizard)
*/
/*
CREATE TABLE `devices` (                             
             `device_id` int(10) unsigned NOT NULL AUTO_INCREMENT,       
             `title` varchar(255) NOT NULL DEFAULT '',            
             `raw_id` varchar(255) NOT NULL,             
             `device_plugin_id` int NOT NULL ,
             `status` int(3) NOT NULL DEFAULT '0',                
             `check_latest` datetime DEFAULT NULL,                
             `check_latest` datetime DEFAULT NULL,                  
             `script_id` int(10) NOT NULL DEFAULT '0',            
             `code` text,                                         
             `online_interval` int(10) NOT NULL DEFAULT '0',      
             `log` text,                                          
             PRIMARY KEY (`device_id`)                                   
           ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8  
*/


  if ($this->mode=='setvalue') {
   global $prop_id;
   global $new_value;
   global $id;
   $this->setProperty($prop_id, $new_value);
   $this->redirect("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode);
  }

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='devices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE device_id='$id'");
 // var_dump($rec);var_dump($rec);
  if ($this->mode=='update') {
   $ok=1;
  //updating 'HOSTNAME' (varchar)

   global $title;
   $rec['title']=$title;

  global $raw_id;
  $rec['raw_id']=$raw_id;

   global $code;
   $rec['code']=$code;

   global $run_type;

       if ($run_type=='script') {
        global $script_id;
        $rec['script_id']=$script_id;
       } else {
        $rec['script_id']=0;
       }


   if ($rec['code']!='' && $run_type=='code') {
    //echo $content;
    $errors=php_syntax_error($code);
    if ($errors) {
     $out['ERR_CODE']=1;
     $out['ERRORS']=nl2br($errors);
     $ok=0;
    }
   }



  //updating 'ONLINE_INTERVAL' (int)
   global $online_interval;
   $rec['online_interval']=(int)$online_interval;
  //UPDATING RECORD
   if ($ok) {
    $rec['status']=0;
    $rec['check_latest']=null;
    $rec['check_next']=date('Y-m-d H:i:s');
    if ($rec['device_id']) {
     SQLUpdate($table_name, $rec, 'device_id'); // update
    } else {
     $new_rec=1;
     $rec['device_id']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }

  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
  $out['LOG']=nl2br(@$out['LOG']);

  if ($rec['device_id']) 
  {
     $properties=SQLSelect("SELECT * FROM device_properties WHERE DEVICE_ID='".$rec['device_id']."' ORDER BY sysname");
     //обновис св-ва из плагина
     $this->fetchPropertiesFromPlugin($rec['device_id']);

     if ($this->mode=='update') 
     {
        $total=count($properties);
        for($i=0;$i<$total;$i++) 
        {
           global ${'linked_object'.$properties[$i]['property_id']};
           global ${'linked_property'.$properties[$i]['property_id']};
           if (${'linked_object'.$properties[$i]['property_id']} && ${'linked_property'.$properties[$i]['property_id']}) {
            $properties[$i]['linked_object']=${'linked_object'.$properties[$i]['property_id']};
            $properties[$i]['linked_property']=${'linked_property'.$properties[$i]['property_id']};
            SQLUpdate('device_properties', $properties[$i], 'property_id');
           } elseif ($properties[$i]['linked_object'] || $properties[$i]['linked_property']) {
            $properties[$i]['linked_object']='';
            $properties[$i]['linked_property']='';
            SQLUpdate('device_properties', $properties[$i], 'property_id');
           }
           global ${'starred'.$properties[$i]['property_id']};
           if (${'starred'.$properties[$i]['property_id']}) {
             //$properties[$i]['STARRED']=1;
             SQLUpdate('device_properties', $properties[$i], 'property_id');
           }
        }
     }



     $out['PROPERTIES']=$properties;
  }

  $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");

?>