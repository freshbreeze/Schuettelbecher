<?php

/******************************
*
* joggle text
*
*******************************
First Version 2014
*******************************/


/******************************
*
* Variables
*
*******************************/
$default_text_file = 'text.txt';
$default_text_title = 'Das Hya-Hya-M&auml;dchen';
$default_text_autor = 'Hanns Heinz Ewers';

$default_mode = 'original';
$a_mode = array('original','kapitel','abschnitt','satz','teilsatz','wort');

session_start();


/******************************
*
* Requests
*
*******************************/
$m = $default_mode;
if(isset($_REQUEST['m'])){
	$m = $_REQUEST['m'];
}

if(isset($_REQUEST['text_by_user']) && $_REQUEST['text_by_user']==0){
  $_SESSION['text_by_user'] = false; // Home Link
}else if(isset($_REQUEST['text_by_user']) && $_REQUEST['text_by_user']==1){
  $_SESSION['text_by_user'] = true;
}else if(isset($_REQUEST['text_by_user']) && $_REQUEST['text_by_user']==2){
  $_SESSION['text_by_user'] = true; // New Text
  $_SESSION['user_text'] = '';
}

if(!isset($_SESSION['text_by_user'])){
  $_SESSION['text_by_user'] = false;
}
if(!isset($_SESSION['user_text'])){
  $_SESSION['user_text'] = '';
}

$user_text = '';
if(isset($_POST['user_text'])){
  $user_text = strip_tags($_POST['user_text']);
	$_SESSION['user_text'] = $user_text;
}

/******************************
*
* User Text
*
*******************************/
if($_SESSION['text_by_user']){
  $title = 'User Text';
  $a_textoriginal = explode("\r\n",$_SESSION['user_text']);
  $text = '';
  if($_SESSION['user_text'] != ''){
    switch ($m) {
        case "original":
            $text = '';
            $line = $a_textoriginal;
            foreach ($line as $line_num => $line) {
              if(strpos($line,'. Kapitel')){
                $text .= '<h3>'.trim($line).'</h3>'."\n";
              }else if($line==''){
                $text .= '<br>'."\n";
              }else{
                $text .= $line."<br>\n";
              }
            }
            $text = '<p>'.$text.'</p>';
            break;
        case "kapitel":
            $text = ''; 
            $a_kapitel = getKapitelOriginal($a_textoriginal);
            shuffle($a_kapitel);
            $i = 0;
            foreach ($a_kapitel as $kapitel) {
              $i++;
              $search = "#<h3>(.*)</h3>#s";
              $replace = '<h3>'.$i.'. Kapitel <span class="inv">($1)</span></h3>';
              $text .= preg_replace($search,$replace,$kapitel);
            }
            $text = '<p>'.$text.'</p>';
            break;
        case "abschnitt":
        case "satz":
        case "teilsatz":
        case "wort":
            // loop satz in loop abschnitt in loop all kapitel
            $a_kapitel = getKapitelEdited($a_textoriginal);
            if(count($a_kapitel)>1){
              foreach ($a_kapitel as $kapitel) {
                $a = array();
                $a = explode('</h3>', $kapitel);
                $kapitel_title = '<h3>'.$a[0].'</h3>';
                @$kapitel_text = $a[1];
                $kapitel_text = getShuffled($kapitel_text,$m);
                $text .= $kapitel_title.$kapitel_text;
              }   
            }else{
              $text = getShuffled($_SESSION['user_text'],$m);
            }
            break;
    }
  }
  $user_text = $text;
}else{
/******************************
*
* Existing Text
*
*******************************/
  $a_default_text = file($default_text_file);

  /******************************
  *
  * Title
  *
  *******************************/
  $title = $default_text_title;

  /******************************
  *
  * Text
  *
  *******************************
  * 
  * mode: original kapitel abschnitt satz teilsatz wort
  *
  *******************************/
  switch ($m) {
      case "original":
          $text = '';
          $line = $a_default_text;
          foreach ($line as $line_num => $line) {
            if(strpos($line,'. Kapitel')){
              $text .= '<h3>'.trim($line).'</h3>'."\n";
            }else if($line==''){
              $text .= '<br>'."\n";
            }else{
              $text .= $line."<br>\n";
            }
          }
          break;
      case "kapitel":
          $text = '';        
          $a_kapitel = getKapitelOriginal($a_default_text);
          shuffle($a_kapitel);
          $i = 0;
          foreach ($a_kapitel as $kapitel) {
            $i++;
            $search = "#<h3>(.*)</h3>#s";
            $replace = '<h3>'.$i.'. Kapitel <span class="inv">($1)</span></h3>';
            $text .= preg_replace($search,$replace,$kapitel);
          }
          break;
      case "abschnitt":
      case "satz":
      case "teilsatz":
      case "wort":
          // loop satz in loop abschnitt in loop all kapitel
          $text = '';        
          $a_kapitel = getKapitelEdited($a_default_text);
          foreach ($a_kapitel as $kapitel) {
            $a = array();
            $a = explode('</h3>', $kapitel);
            $kapitel_title = '<h3>'.$a[0].'</h3>';
            $kapitel_text = $a[1];
            $kapitel_text = getShuffled($kapitel_text,$m);
            $text .= $kapitel_title.$kapitel_text;
          }
          break;
  }
}//else user_text
?><html>
<head>	
  <meta charset="utf-8">
  <title><?php echo $title; ?></title>
  <script type="text/javascript" src="main.js"></script>
  <link rel="stylesheet" type="text/css" href="main.css">
</head>
<body>
  <h1 style="margin-bottom: 10px;"><a href="?text_by_user=0" class="logo">Sch&uuml;ttelbecher</a></h1>
  <div id="right" class="addthis_sharing_toolbox"></div>
  <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5341a72977ecce64" async></script>
  <?php if($_SESSION['text_by_user']){ ?>
  <p id="introduction">Hier k&ouml;nnen sie einen eigenen Text durchsch&uuml;tteln.</p>
  <div id="nav"><ul><li><a href="?text_by_user=0">Home</a></li><li><a href="?text_by_user=2">Neuer Text</a></li></ul></div>
  <?php }else{ ?>
  <p id="introduction">Hier k&ouml;nnen sie den unten stehenden Text durchsch&uuml;tteln nach Kapiteln, Abschnitten, S&auml;tzen, Teils&auml;tzen und W&ouml;rtern. <a href="?m=original&text_by_user=1" class="textlink">Oder einen eigenen Text</a></p><p id="idea">Idee: <a href="http://nosenoise.ch/" target="_new" class="textlink">Bruno Schlatter</a>, Ausf&uuml;hrung: <a href="https://github.com/freshbreeze"target="_new" class="textlink">Freshbreeze</a>, im Rahmen des <a href="http://republicdomain.ch/" target="_new" class="textlink">Public Domain 2014</a></p>
  <?php } ?>
  <div id="nav"><ul><?php
  $nav = '';
  foreach ($a_mode as $s) {
    if($m == $s){
      echo '<li><a href="?m='.$s.'" class="active">'.ucfirst($s).'</a></li>';
    }else{
      echo '<li><a href="?m='.$s.'">'.ucfirst($s).'</a></li>';
    }
  }
  ?></ul></div>
  <?php
  if($_SESSION['text_by_user']){
    if($user_text == ''){
      echo '<br>
      <form action="?text_by_user=1" method="POST">
        <textarea name="user_text" cols="50" rows="10">'.$user_text.'</textarea><br>
        <input type="submit" value=" Senden ">
      </form>';
    }else{
      echo '<br><div id="text">'.$user_text.'</div>';
    }
  }else{
    echo '<div id="title"><h1>'.$title.'</h1><p>'.$default_text_autor.'</p></div>
    <div id="text">'.$text.'</div>';
  }
  ?>
</body>
</html><?php



/******************************
*
* Functions
*
*******************************/
function multiexplode ($delimiters,$string) {
  //$delimiters has to be array
  //$string has to be array
  $ready = str_replace($delimiters, $delimiters[0], $string);
  $launch = explode($delimiters[0], $ready);
  return  $launch;
}

function getKapitelOriginal($text){
  $a = array();
  $kapitel = 0;
  $line = $text;
  foreach ($line as $line_num => $line) {
    if(strpos($line,'. Kapitel')){
      $kapitel++;
      $a[$kapitel] = '';
      $a[$kapitel] .= '<h3>'.trim($line).'</h3>'."\n";
    }else if($line==''){
      $a[$kapitel] .= '<br>'."\n";
    }else{
      if(!isset($a[$kapitel])){
        $a[$kapitel] = '';
      }
      $a[$kapitel] .= trim($line)."<br>\n";
    }
  }
  return $a;
}

function getKapitelEdited($text){
  $a = array();
  $kapitel = 0;
  $line = $text;
  foreach ($line as $line_num => $line) {
    if(strpos($line,'. Kapitel')){
      $kapitel++;
      $a[$kapitel] = '';
      $a[$kapitel] .= '<h3>'.trim($line).'</h3>'."\n";
    }else if(trim($line)==''){
      //$a[$kapitel] .= '<br>'."\n";
    }else{
      if(!isset($a[$kapitel])){
        $a[$kapitel] = '';
      }
      $a[$kapitel] .= $line;
    }
  }
  return $a;
}

function getShuffled($text,$mode){
  $s = '';
  $tmp = '';
  $a_tmp = array();
  $a = array();
  $line = explode("\n",$text);
  foreach ($line as $v) { 
    if(trim($v)!=''){
      $a[] = $v;
    }
  }
  shuffle($a);
  $a_part = array();
  foreach ($a as $abschnitt) {
    $s .= '<p>';
    if($abschnitt!=''){
      if($mode=='abschnitt'){
        $s .= $abschnitt;
      }else{
        /******************************
        * Satz
        *******************************/
        $search = "/\xc2\xbb(.*)\xC2\xAB/";
        preg_match($search,$abschnitt,$match);        
        if(isset($match[0])){
         // found &laquo; and &raquo;. leave it as it is.
          if($mode=='satz'){
            $s .= $abschnitt;
          }else if($mode=='teilsatz'){
            /******************************
            * Teilsatz mit &laquo; and &raquo;
            *******************************/
            $leftover = str_replace($match[0],'',$abschnitt);
            $tmp = substr($match[0], 2, -2);// rm &laquo; and &raquo;
            $s .= str_replace(':','',getCommaShuffled($leftover));
            $s .= '&laquo;'.str_replace('..','.',trim(getCommaShuffled($tmp))).'&raquo;';
            //echo '<h1>'.$abschnitt.'</h1><h2>'.$match[0].'</h2><h2>'.$leftover.'</h2>';exit;
          }else if($mode=='wort'){
            /******************************
            * Wort mit &laquo; and &raquo;
            *******************************/
            $leftover = str_replace($match[0],'',$abschnitt);
            $tmp = substr($match[0], 2, -2);// rm &laquo; and &raquo;
            $s .= str_replace(':','',getWordShuffled($leftover));
            $s .= '&laquo;'.str_replace('..','.',trim(getWordShuffled($tmp))).'&raquo;';
          }else{
            //not possible 
          }
        }else{
          $a_tmp = explode('.',trim($abschnitt));
          foreach ($a_tmp as $tmp) {
            if($tmp!=''){
              if($mode=='satz'){
                $s .= $tmp.'.';
              }else if($mode=='teilsatz'){
                /******************************
                * Teilsatz
                *******************************/
                $s .= getCommaShuffled($tmp);
              }else if($mode=='wort'){
                /******************************
                * Wort
                *******************************/
                $s .= getWordShuffled($tmp);
              }else{
                //not possible 
              }
            }
          }
        }//else found &laquo; and &raquo;
        $s .= '</p>';
      }//else abschnitt
    }
  }
  return $s;
}

function getCommaShuffled($s){
  $a = explode(', ',trim($s));
  shuffle($a);
  $s = '';
  foreach ($a as $v) {
    $s .= lcfirst(trim($v)).', ';
  }
  return ucfirst(trim(rtrim($s,', '))).'. ';
}

function getWordShuffled($s){
  $a = explode(' ',trim($s));
  shuffle($a);
  $s = '';
  foreach ($a as $v) {
    $s .= trim($v).' ';
  }
  return ucfirst(trim(rtrim($s,' '))).'. ';
}
?>