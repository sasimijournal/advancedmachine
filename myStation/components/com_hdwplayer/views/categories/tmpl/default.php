<?php 

/*
 * @version		$Id: default.php 3.0 2012-10-10 $
 * @package		Joomla
 * @subpackage	hdwplayer
 * @copyright   Copyright (C) 2011-2016 HDW Player
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access'); 

/**********************************************************************/
$cols         = $this->cols;
$thumb_width  = $this->thumb_width;
$thumb_height = $this->thumb_height;
/**********************************************************************/

$categories = $this->categories;
$header     = ( substr(JVERSION,0,3) != '1.5' ) ? 'page_heading' : 'page_title';
$link       = 'index.php?option=com_hdwplayer&view=category&wid=';
$qs         = JRequest::getVar('orderby') ? '&orderby=' . JRequest::getVar('orderby') : '';
$qs        .= JRequest::getVar('Itemid')  ? '&Itemid=' . JRequest::getVar('Itemid') : '';
$row        = 0;
$column     = 0;

$document = JFactory::getDocument();
$document->addStyleSheet( JURI::root() . "components/com_hdwplayer/css/hdwplayer.css",'text/css',"screen");

?>

<?php if ($this->params->get('show_'.$header, 1)) : ?>
	<h2> <?php echo $this->escape($this->params->get($header)); ?> </h2>
<?php endif; ?>

<div id="hdwplayer_gallery">
  <?php 
  	if(!count($categories)) echo JText::_('Item not Found.');
  	for ($i=0, $n=count($categories); $i < $n; $i++) { 
		$clear = '';  
  		if($column >= $cols) {
			$clear  = '<div style="clear:both;"></div>';
			$column = 0;
			$row++;		
		}
		if(!$categories[$i]->image) $categories[$i]->image = 'http://img.youtube.com/vi/default.jpg';
		$column++;
		echo $clear;
  ?>
  <div class="hdwplayer_thumb" style="width:<?php echo $thumb_width; ?>px;" onclick='javascript:location.href="<?php echo JRoute::_($link.$categories[$i]->id.$qs); ?>"'>
  	<img class="arrow" src="<?php echo JURI::root(); ?>components/com_hdwplayer/assets/play.gif" border="0" style="margin-left:<?php echo ($this->thumb_width / 2) - 15; ?>px; margin-top:<?php echo ($thumb_height / 2) - 13; ?>px;" />
    <img class="image" src="<?php echo $categories[$i]->image; ?>" width="<?php echo $thumb_width; ?>" height="<?php echo $thumb_height; ?>" title="<?php echo JText::_('Click to View').' : '.$categories[$i]->name; ?>" border="0" style="height:<?php echo $thumb_height; ?>px" />
    <span class="name"><?php echo $categories[$i]->name; ?></span>
  </div>
  <?php } ?>
  <div style="clear:both"></div>
</div>
<div id="hdwplayer_pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>