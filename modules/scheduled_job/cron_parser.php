 <?php
class CronParser{
  private $bits = Array(); //exploded String like 0 1 * * *
  private $now= Array();    //Array of cron-style entries for time()
  private $lastRan;         //Timestamp of last ran time.
  private $taken;
  public function __construct($string){
    $tstart = microtime();
    $this->bits = @explode(" ", $string);
    $this->getNow();         
    $this->calcLastRan();
    $tend = microtime();
    $this->taken = $tend-$tstart;
  }
  private function getNow(){
    $t = strftime("%M,%H,%d,%m,%w,%Y", time()); //Get the values for now in a format we can use
    $this->now = explode(",", $t); //Make this an array
  }
  public function getLastRan(){
    return explode(",", strftime("%M,%H,%d,%m,%w,%Y", $this->lastRan)); //Get the values for now in a format we can use    
  }
  function getExtremeMonth($extreme){
    if ($extreme == "END"){
      $year = $this->now[5] - 1;
    } else {
      $year = $this->now[5];
    }
    //Now determine start or end month in the last year
    if ($this->bits[3] == "*" && $extreme == "END"){//Check month format
      $month = 12;            
    } else if ($this->bits[3] == "*" && $extreme == "START"){
      $month = 1;
    } else {
      $months = $this->expand_ranges($this->bits[3]);
      if ($extreme == "END"){
        sort($months);
      } else {
        rsort($months);
      }
      $month = array_pop($months);
    }
    
    //Now determine the latest day in the specified month
    $day=$this->getExtremeOfMonth($month, $year, $extreme);
    $hour = $this->getExtremeHour($extreme);
    $minute = $this->getExtremeMinute($extreme);
    
    return mktime($hour, $minute, 0, $month, $day, $year);
  }
  
    /**
     * Assumes that value is not *, and creates an array of valid numbers that
     * the string represents.  Returns an array.
     */
private function expand_ranges($str){
    if (strstr($str,  ",")){
      $tmp1 = explode(",", $str);
      $count = count($tmp1);
      for ($i=0;$i<$count;$i++){//Loop through each comma-separated value
        if (strstr($tmp1[$i],  "-")){ //If there's a range in this place, expand that too
          $tmp2 = explode("-", $tmp1[$i]);
          for ($j=$tmp2[0];$j<=$tmp2[1];$j++){
            $ret[] = $j;
          }
        } else {//Otherwise, just add the value
          $ret[] = $tmp1[$i];
        }
      }
    } else if (strstr($str,  "-")){//There might only be a range, no comma sep values at all.  Just loop these
      $range = explode("-", $str);
      for ($i=$range[0];$i<=$range[1];$i++){
        $ret[] = $i;
      }
    } else {//Otherwise, it's a single value
      $ret[] = $str;
    }
    return $ret;
  }
    
    /**
     * Given a string representation of a set of weekdays, returns an array of
     * possible dates.
     */
private function getWeekDays($str, $month, $year){
   $daysInMonth = $this->daysinmonth($month, $year);
   if (strstr($str,  ",")){
     $tmp1 = explode(",", $str);
     $count = count($tmp1);
     for ($i=0;$i<$count;$i++){//Loop through each comma-separated value
       if (strstr($tmp1[$i],  "-")){ //If there's a range in this place, expand that too
         $tmp2 = explode("-", $tmp1[$i]);
         
         for ($j=$start;$j<=$tmp2[1];$j++){
           for ($n=1;$n<=$daysInMonth;$n++){
             if ($j == jddayofweek(gregoriantojd ( $month, $n, $year),0)){
               $ret[] = $n;
             }                             
           }
         }
       } else {//Otherwise, just add the value
         for ($n=1;$n<=$daysInMonth;$n++){
           if ($tmp1[$i] == jddayofweek(gregoriantojd ( $month, $n, $year),0)){
             $ret[] = $n;
           }                             
         }
       }
     }
   } else if (strstr($str,  "-")){//There might only be a range, no comma sep values at all.  Just loop these
     $range = explode("-", $str);
     for ($i=$start;$i<=$range[1];$i++){
       for ($n=1;$n<=$daysInMonth;$n++){
         if ($i == jddayofweek(gregoriantojd ( $month, $n, $year),0)){
           $ret[] = $n;
         }                             
       }
     }
   } else {//Otherwise, it's a single value
     for ($n=1;$n<=$daysInMonth;$n++){                
       if ($str == jddayofweek(gregoriantojd ( $month, $n, $year),0)){
         $ret[] = $n;
       }                             
     }
   }
   
   return $ret;        
}
    
private function daysinmonth($month, $year){
   if(checkdate($month, 31, $year)) return 31;
   if(checkdate($month, 30, $year)) return 30;
   if(checkdate($month, 29, $year)) return 29;
   if(checkdate($month, 28, $year)) return 28;
   return 0; // error
}    
   
   /**
    * Get the timestamp of the last ran time.
    */
private function calcLastRan(){
   $now = time();
   
   if ($now < $this->getExtremeMonth("START")){
     //The cron isn't due to have run this year yet.  Getting latest last year
     $tsLatestLastYear = $this->getExtremeMonth("END");    
     $this->lastRan = $tsLatestLastYear;
            
     $year = date("Y", $this->lastRan);
     $month = datetime("m", $this->lastRan);
     $day = date("d", $this->lastRan);
     $hour = date("h", $this->lastRan);
     $minute = date("i", $this->lastRan);        
     
     
   } else { //Cron was due to run this year.  Determine when it was last due
     $year = $this->now[5];               
               
     $arMonths = $this->expand_ranges($this->bits[3]);
     if (!in_array($this->now[3], $arMonths) && $this->bits[3] != "*"){//Not due to run this month.  Get latest of last month
       sort($arMonths);
       do{
         $month = array_pop($arMonths);
       } while($month > $this->now[3]);
       $day = $this->getExtremeOfMonth($month, $this->now[5], "END");
       $hour = $this->getExtremeHour("END");
       $minute = $this->getExtremeMinute("END");    
     } else if ($now < $this->getExtremeOfMonth($this->now[3], $this->now[5], "START")){ //It's due in this month, but not yet.
       sort($arMonths);
       do{
         $month = array_pop($arMonths);
       } while($month > $this->now[3]);
       $day = $this->getExtremeOfMonth($month, $this->now[5], "END");
       $hour = $this->getExtremeHour("END");
       $minute = $this->getExtremeMinute("END");
     } else {//It has been due this month already
       $month = $this->now[3];        
       $days = $this->getDaysArray($this->now[3]);
       if (!in_array($this->now[2], $days)){
         //No - Get latest last scheduled day                   
         sort($days);
         do{
           $day = array_pop($days);
         } while($day > $this->now[2]);
         
         $hour = $this->getExtremeHour("END");
         $minute = $this->getExtremeMinute("END");
         
       } else if($this->now[1] < $this->getExtremeHour("START")){//Not due to run today yet
         sort($days);
         do{
           $day = array_pop($days);
         } while($day >= $this->now[2]);
         
         $hour = $this->getExtremeHour("END");
         $minute = $this->getExtremeMinute("END");
       } else {
         $day = $this->now[2];
         //Yes - Check if this hour is in the schedule?
         $arHours = $this->expand_ranges($this->bits[1]);
         if (!in_array($this->now[1], $arHours) && $this->bits[1] != "*"){
           //No - Get latest last hour
           sort($arHours);
           do{
             $hour = array_pop($arHours);
           } while($hour > $this->now[1]);
           
           $minute = $this->getExtremeMinute("END");
           
         } else if ($now < $this->getExtremeMinute("START") && $this->bits[1] != "*"){ //Not due to run this hour yet
           sort($arHours);
           do{
             $hour = array_pop($arHours);
           } while($hour >= $this->now[1]);
           $minute = $this->getExtremeMinute("END");
         } else {
           //Yes, it is supposed to have run this hour already - Get last minute
           $hour = $this->now[1];
           if ($this->bits[0] != "*"){
             $arMinutes = $this->expand_ranges($this->bits[0]);
             do{
               $minute = array_pop($arMinutes);                                   
             } while($minute >= $this->now[0]);
             
             //If the first time in the hour that the cron is due to run is later than now, return latest last hour
             if($minute > $this->now[1] || $minute == ""){
               $minute = $this->getExtremeMinute("END"); //The minute will always be the last valid minute in an hour
               //Get the last hour.
               if ($this->bits[1] == "*"){
                 $hour = $this->now[1] - 1;
               } else {
                 $arHours = $this->expand_ranges($this->bits[1]);
                 sort($arHours);
                 do{
                   $hour = array_pop($arHours);
                 } while($hour >= $this->now[1]);
               }
             }
             
           } else {
             $minute = $this->now[0] -1;
           }
         }                  
       }
     }
   }
   $this->lastRan = mktime($hour, $minute, 0, $month, $day, $year);
}

private function getExtremeOfMonth($month, $year, $extreme){
   $daysInMonth = $this->daysinmonth($month, $year);
   if ($this->bits[2] == "*"){
     if ($this->bits[4] == "*"){//Is there a day range?
       if ($extreme == "END"){
         $day = $daysInMonth;
       } else {
         $day=1;
       }
     } else {//There's a day range.  Ignore the dateDay range and just get the list of possible weekday values.
       $days = $this->getWeekDays($this->bits[4],$month, $year);
       if ($extreme == "END"){
         sort($days);
       } else {
         rsort($days);    
       }
       $day = array_pop($days);
     }
   } else {
     $days = $this->expand_ranges($this->bits[2]);
     if ($extreme == "END"){
       sort($days);
     } else {
       rsort($days);    
     }
     
     do {
       $day = array_pop($days);
     } while($day > $daysInMonth);
   }    
   return $day;
   }
     
private function getDaysArray($month){
   $days = array();
   
   if ($this->bits[4] != "*"){               
     $days = $this->getWeekDays($this->bits[4], $month, $this->now[5]);
   }
   if ($this->bits[2] != "*" && $this->bits[4] == "*") {
     $days = $this->expand_ranges($this->bits[2]);
   }
   if ($this->bits[2] == "*" && $this->bits[4] == "*"){
     //Just return every day of the month
     $daysinmonth = $this->daysinmonth($month, $this->now[5]);
     for($i = 1;$i<=$daysinmonth;$i++){
       $days[] = $i;
     }
   }
   
   return $days;
}
private function getExtremeHour($extreme){
   if ($this->bits[1] == "*"){
     if ($extreme == "END"){
       $hour = 23;
     } else {
       $hour = 0;    
     }
   } else {
     $hours = $this->expand_ranges($this->bits[1]);
     if ($extreme == "END"){
       sort($hours);
     } else {
       rsort($hours);    
     }
     $hour = array_pop($hours);
   }
   return $hour;
}
private function getExtremeMinute($extreme){
   if ($this->bits[0] == "*"){
     if ($extreme == "END"){
       $minute = 59;
     } else {
       $minute = 0;    
     }
   } else {
     $minutes = $this->expand_ranges($this->bits[0]);
     if ($extreme == "END"){
       sort($minutes);
     } else {
       rsort($minutes);    
     }
     $minute = array_pop($minutes);
   }
   return $minute;
   }
}
?> 