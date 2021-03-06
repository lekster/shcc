<?php
/**
* Thumbnail builder
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2
*/

// 
class thumb extends module {

// --------------------------------------------------------------------

function thumb() {

  // setting module name
  $this->name="thumb";
  $this->title="<#LANG_MODULE_THUMB#>";
  $this->module_category="<#LANG_SECTION_SYSTEM#>";

  $this->checkInstalled();

}


// --------------------------------------------------------------------

function run() {

  //if (preg_match('/~/', $this->src)) {
  // $this->src=preg_replace('/\/~(.+?)\//', '/', $this->src);
  //}

  $out['REQUESTED']=$this->src;

  preg_match('/(.*)?\/.*$/',$_SERVER['PHP_SELF'],$match);
  $this->src_def=urlencode('http://'.$_SERVER['SERVER_NAME'].$match[1].$this->src);


  if (file_exists($this->src)) {
   //$lst=GetImageSize($this->src);
   $out['REAL_WIDTH']=$lst[0];
   $out['REAL_HEIGHT']=$lst[1];
   $image_format=$lst[2];

   $out['UNIQ']=rand(1, time());
   $out['WIDTH']=$this->width;
   $out['HEIGHT']=$this->height;
   $out['MAX_HEIGHT']=$this->max_height;
   $out['MAX_WIDTH']=$this->max_width;
   $out['CLOSE']=$this->close;
   $out['BGCOLOR']=(($this->bgcolor[0]='#')?substr($this->bgcolor,1):$this->bgcolor);
   $out['COLOR']=(($this->color[0]='#')?substr($this->color,1):$this->color);
   $out['ENLARGE']=$this->enlarge;
   $out['SRC']=urlencode($this->src);
   $out['SRC_REAL']=$this->src_def;
  }


  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}


 function resizeImage($filename, $new_width=0, $new_height=0) {

 if (file_exists($filename)) {

  $lst=GetImageSize($filename);
  $image_width=$lst[0];
  $image_height=$lst[1];
  $image_format=$lst[2];

  $type=0;

  switch($type)
  {
   case 0:
    if (($new_width!=0) && ($new_width<$image_width)) {
     $image_height=(int)($image_height*($new_width/$image_width));
     $image_width=$new_width;
    }
    if (($new_height!=0) && ($new_height<$image_height)) {
     $image_width=(int)($image_width*($new_height/$image_height));
     $image_height=$new_height;
    }
   break;

   case 1:
     $image_width=$new_width;
     $image_height=$image_height;
   break;
  }


  if ($image_format==1) {
    $old_image=imagecreatefromgif($filename);
   } elseif ($image_format==2) {
    $old_image=imagecreatefromjpeg($filename);
   } elseif ($image_format==3) {
    $old_image=imagecreatefrompng($filename);
   } else {
    return 0;
   }

   $new_image=imageCreateTrueColor($image_width, $image_height);
   $white = ImageColorAllocate($new_image, 255, 255, 255);
   ImageFill($new_image, 0, 0, $white);

  /*imageCopyResized*/imagecopyresampled( $new_image, $old_image, 0, 0, 0, 0, $image_width, $image_height, imageSX($old_image), imageSY($old_image));

   //   Save to file
   imageJpeg($new_image, $filename);
   return 1;

 } else {
  return 0;
 }

 }


}
?>
