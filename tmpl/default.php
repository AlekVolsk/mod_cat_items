<?php defined('_JEXEC') or die;
/*
 * @package     mod_cat_items
 * @copyright   Copyright (C) 2016 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */
?>
<div class="mod_cat_items catitems_list <?php echo $moduleclass_sfx; ?>">
	<?php foreach ( $list as $item ) { ?>
	<div class="catitems_item">
		<?php if ( $showImage && $item->images->image_intro ) echo '<img class="catitems_item_image" src="' . $item->images->image_intro . '" alt="' . $item->images->image_intro_alt . '"/>'; ?>
		<?php if ( $showDate ) echo '<span class="catitems_item_date">' . JHtml::_( 'date', $item->created, JText::_( 'DATE_FORMAT_LC3' ) ) . '</span>'; ?>
		<h3 class="catitems_item_title"><a class="catitems_item_link" href="<?php echo $item->route; ?>"><?php echo $item->title; ?></a></h3>
	</div>
	<?php } ?>
</div>
