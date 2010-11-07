<?php
/*
Plugin Name: metamarks
Plugin URI: http://www.metablogging.gr/metamarks
Description: A Plugin from metablogging.gr that allows your readers to bookmark a post with greek and international bookmarking sites. Bookmarking sites can be selected from the options page as well as appearance of the bookmarking icons under each post.
Version: 0.7
Author: Nikos Anagnostou
Author URI: http://www.ofb.gr/
*/ 

global $perlink, $base, $rollover, $strTitle, $marksdata;
$marksdata =array();
$marksdata[0]	= array("buzz","http://buzz.reality-tape.com/submit.php?url=_URL_");
$marksdata[1]	= array("cull","http://www.cull.gr/refer?url=_URL_&title=_TITLE_");
$marksdata[2] 	= array("bobit", "http://www.bobit.gr/articles/new?bob_this=_URL_");
$marksdata[3]   = array("digme", "http://www.digme.gr/submit.php?url=_URL_");
$marksdata[4]	= array("foracamp", "http://www.foracamp.gr/user/login?destination=?/node/add/story/_TITLE_/_URL_");
$marksdata[5]	= array("freestuff","http://bookmarks.freestuff.gr/bookmarks/user?action=add&address=_URL_&title=_TITLE_");
$marksdata[6]	= array("delicious","http://del.icio.us/post?url=_URL_&title=_TITLE_&v=4");
$marksdata[7]	= array("google","http://www.google.com/bookmarks/mark?bkmk=_URL_&title=_TITLE_&op=edit");
$marksdata[8]	= array("facebook","http://www.facebook.com/sharer.php?src=bm&v=4&u=_URL_&t=_TITLE_");
$marksdata[9]	= array("twitter", "http://twitter.com/home?status=_URL_");
$marksdata[10]	= array("digg", "http://digg.com/submit?url=_URL_&title=_TITLE_&bodytext=&media=&topic=");
$marksdata[11]	= array("yahoobuzz","http://buzz.yahoo.com/submit?submitUrl=_URL_&submitHeadline=_TITLE_");	
add_action('admin_menu', 'metamarks_add_pages');
//'metamarks_count' holds the total nr of marks for every version of the plugin. This block works after saving the options>metamarks>form in the backend
if($_POST['metamarks_count']>0){
	/* Options used 
metamarks: stores enable/disable flags for  marks
metamarks_css: stores the background and border values
metamarks_state: stores flags about how to show the marks (popup vs array, show or not metamarks download
metamarks_div_id: used to store a big number (>1000). This num is concatenated to the text "fr" and generate a div css id so that there is no conflict of ids shown in multiple posts in the same page.
*/
	if (get_option('metamarks')){
		for($j=1;$j<=$_POST['metamarks_count'];$j++)
		{
			$values.=$_POST['a'.$j].",";
		}
		$values=substr($values,0,strlen($values)-1);
		update_option('metamarks',$values);
	}
	else {
	for($j=1;$j<=count($marksdata)-1;$j++)
		{
			$values.="1,";
		}
		$values=substr($values,0,strlen($values)-1);
		add_option('metamarks',$values);
		//add_option('metamarks', '1,1,1,1,1,1,1,1,1,1,1,1,1,1');
	}
	if(get_option('metamarks_css')){
		$css=explode(",",get_option('metamarks_css'));
		$metamarks_frame_bg=$css[0];
		$metamarks_frame_brd=$css[1];
		$_POST['metamarks_frame_bg']==$metamarks_frame_bg?$metamarks_frame_bg=$css[0]:$metamarks_frame_bg=$_POST['metamarks_frame_bg'];
		$_POST['metamarks_frame_brd']==$metamarks_frame_brd?$metamarks_frame_brd=$css[1]:$metamarks_frame_brd=$_POST['metamarks_frame_brd'];
		update_option('metamarks_css',$metamarks_frame_bg.",".$metamarks_frame_brd);
	}
	else 
	{
		$metamarks_frame_bg='white';
		$metamarks_frame_brd='black';
		add_option('metamarks_css',$metamarks_frame_bg.",".$metamarks_frame_brd);
	}
	if(get_option('metamarks_state')){
		$state=explode(",",get_option('metamarks_state'));
		$expanded=$state[0];
		$download=$state[1];
		$textonly=$state[2];
    	$rollover=$state[3];
		$_POST['expanded']==$expanded?$expanded=$state[0]:$expanded=$_POST['expanded'];
		$_POST['download']==$download?$download=$state[1]:$download=$_POST['download'];
		$_POST['textonly']==$textonly?$textonly=$state[2]:$textonly=$_POST['textonly'];
    $_POST['rollover']==$rollover?$rollover=$state[3]:$rollover=$_POST['rollover'];
		update_option('metamarks_state',$expanded.",".$download.",".$textonly.",".$rollover);
	}
	else 
	{
		$expanded=1;
		$download=1;
		$textonly=1;
    	$rollover=1;
		add_option('metamarks_state',$expanded.",".$download.",".$textonly.",".$rollover);
	}
}
//**********A T T E N T I O N**************
//This filter is enabled. If you don't want metamarks to appear in your feed you need to disable it and add the bm_frame function in your theme manually.
//Check http://metablogging.gr/metamarks for instructions
if(!is_feed())add_filter('the_content', 'bm_frame');
//This function is the main workhorse. Adds the marks to the content
function bm_frame($content) {
//TODO:move the variables outside the function so that the queries run once.
	global $perlink,$strTitle, $base, $rollover, $marksdata;
	
	if(get_option('metamarks_div_id')){
		$idframe=get_option('metamarks_div_id')+1;
		update_option('metamarks_div_id',$idframe);
	}
	else {
		add_option('metamarks_div_id','10000');
	}
	$css=get_option('metamarks_css');
	$css=explode(",",$css);
		$metamarks_frame_bg=$css[0];
		$metamarks_frame_brd=$css[1];
	$state=get_option('metamarks_state');
	$state=explode(",",$state);
	$expanded=$state[0];
	$download = $state[1];
    $textonly = $state[2];
    $rollover=$state[3];
	
	$strTitle = urlencode(the_title('', '', false));
	$perlink  = urlencode(get_permalink());
	$path=substr($_SERVER['SCRIPT_NAME'],0,strlen($_SERVER['SCRIPT_NAME'])-9);
	$base="http://".$_SERVER['SERVER_NAME'].$path."/wp-content/plugins/metamarks/";
	$showMarkFlag=get_option('metamarks');
	$showMarkFlag = explode(",", $showMarkFlag);


	//here we start building the output
	//State 0: Markers hidden,
	//State 1: Markers  shown
	
		
	if($state[0]==0) {$marks.="<img onclick='mframe=document.getElementById(\"fr".$idframe."\");if(mframe.style.display==\"none\"){mframe.style.display=\"block\";}	else{	mframe.style.display=\"none\"}' src='".$base."images/metamarks.gif' alt='Metamarks' title='Metamarks! Greek Social Bookmarking.Click to show/hide'  style='margin-bottom:10px;'/> "; 
		}
	$marks.="<div id='fr".$idframe."'  style='padding:2px;border:1px solid ".$metamarks_frame_brd.";background-color:".$metamarks_frame_bg.";font-size: 9px";
	//State 1: mark  shown as a block
	if($state[0]==1){ $marks.=";display:block;'>Μοιράσου το με: ";}
	else{
	$marks.= ";display:none;'>Μοιράσου το με: ";
	}
  for($i=0;$i<sizeof($marksdata);$i++)
  {
  	$marks.= show_marker($showMarkFlag,$marksdata,$i, $state);
  }
	if($state[1]){$marks.="<p style='width:100%;float:left;'>&nbsp;<a style='text-decoration:none;font-size:8px;' href='http://metablogging.gr/metamarks/'>Κατέβασε το metamarks plugin</a></p><br/>";}

	$marks.="</div>";
	$content=$content.$marks;
	return $content;
}
//Builds the mark with name, image, link for submission to the service 
function show_marker($showMarkFlag,$marksdata,$i,$state){
		global $perlink,$strTitle, $base, $rollover;
		$marksdata[$i][1]=str_replace('_URL_', $perlink,$marksdata[$i][1]);
		$marksdata[$i][1]=str_replace('_TITLE_', $strTitle,$marksdata[$i][1]);
		if($showMarkFlag[$i]==1){ 
		   $intmarks.="<a style='text-decoration:none;' href='".$marksdata[$i][1]."'>";
				if($state[2]){
					$icon=$marksdata[$i][0].".png";
					$icon_grey=$marksdata[$i][0]."_grey.png";
					$intmarks.="<img  ".rollover($base,$icon,$icon_grey, $rollover)." style='margin:0px 5px; ' alt='".$marksdata[$i][0]."' title='".$marksdata[$i][0]."'/>";
						}else {
			$intmarks.=$marksdata[$i][0]."&nbsp;";}
			$intmarks.="</a>";
		}
		return $intmarks;
}

//Determines whether to show rollover 
function rollover ($base, $icon, $icon_grey,  $rollover){
if($rollover){
	$output= "src='".$base."images/".$icon_grey."' onmouseover='this.src=\"".$base."images/".$icon."\"' onmouseout='this.src=\"".$base."images/".$icon_grey."\"'";
}else{
$output= "src=' ".$base."images/".$icon."' ";
}
return $output;
}
//Adds the checked attribute to an input tag
function ichecked($showMarkFlag, $key){
	if ($showMarkFlag[$key]==1) return "checked";
	else return "";
}


//Admin Interface Section
//Adds a metamarks page to the options (=settings) admin menu of wp 
function metamarks_add_pages() {
//    global $wpdb;
    if (function_exists('add_submenu_page'))
        add_submenu_page('options-general.php', __('metamarks'), __('Metamarks'), 1, __FILE__, 'metamarks_options_subpanel');
}

//Adds the panel data
function metamarks_options_subpanel() 
{
	global $marksdata, $filenotfound;
	if(!$filenotfound){
	$showMarkFlag = get_option('metamarks');
	$showMarkFlag = explode(",", $showMarkFlag);
	if(get_option('metamarks_css'))
	{
		$css=get_option('metamarks_css');
		$css=explode(",",$css);
	}
	else
	{
		$metamarks_frame_bg='white';
		$title_clr='white';
		add_option('metamarks_css',$metamarks_frame_bg.",".$metamarks_frame_brd);
	}
	if(get_option('metamarks_state'))
	{
		$state=get_option('metamarks_state');
		$state=explode(",",$state);
	}
	else
	{
		$expanded=1;
		$download=1;
		add_option('metamarks_state',$expanded.",".$download);
	}
?>
<div class="wrap">
        <h2 id="write-post">Metamarks Options</h2>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=metamarks/metamarks.php">
<h3>Select the bookmarking services you want to activate</h3>
<?php 

foreach($marksdata as $key=>$md){
	echo "<input name='a".($key+1)."' type='checkbox' id='a".($key+1)."' value='1' ".ichecked($showMarkFlag, $key)."' /> ".$md[0]."<br/>";
}
?>
	<input type="hidden" name="metamarks_count" value='<?php echo sizeof($marksdata);?>'/>
    <h3 id="meta_css">Metamarks CSS</h3>
      Frame Background Color:&nbsp;<input type="text" name="metamarks_frame_bg" value="<?php echo $css[0];?>" /> <br/>
      Frame border color&nbsp;<input type="text" name="metamarks_frame_brd" value="<?php echo $css[1];?>" /> <br/>
   <br/>
      <h3 id="meta_state">Metamarks Load State</h3>
       Load Expanded:&nbsp;<input type="checkbox" name="expanded" value="1" <?php if($state[0]==1) {echo("checked");}?> /> <br/>
      Show download message: &nbsp;<input type="checkbox" name="download" value="1" <?php if($state[1]==1) {echo("checked");}?> /> <br/>
	      Show icons :&nbsp;<input type="checkbox" name="textonly" value="1" <?php if($state[2]==1) {echo("checked");}?> /> <br/>  
Rollover enabled :&nbsp;<input type="checkbox" name="rollover" value="1" <?php if($state[3]==1) {echo("checked");}?> /> <br/>  
      <input type="submit" value="Update Metamarks" name="Submit" />
        </form>
 </div>
<?php }
else {
	echo "The .ini file with the mark definitions was not found, or is unreadable.";}
} ?>
