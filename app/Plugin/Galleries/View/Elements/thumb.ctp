<?php
/**
 * Gallery Thumb Element
 *
 * Displays the thumb nail image for a gallery.
 *
 * PHP versions 5
 *
 * Zuha(tm) : Business Management Applications (http://zuha.com)
 * Copyright 2009-2010, Zuha Foundation Inc. (http://zuhafoundation.org)
 *
 * Licensed under GPL v3 License
 * Must retain the above copyright notice and release modifications publicly.
 *
 * @copyright     Copyright 2009-2010, Zuha Foundation Inc. (http://zuha.com)
 * @link          http://zuha.com Zuha™ Project
 * @package       zuha
 * @subpackage    zuha.app.plugins.galleries.views.elements
 * @since         Zuha(tm) v 0.0.1
 * @license       GPL v3 License (http://www.gnu.org/licenses/gpl.html) and Future Versions
 */
if (!empty($model) && !empty($foreignKey)) {
	$galleryThumb = $this->requestAction('/galleries/galleries/thumb/model:'.$model.'/foreignKey:'.$foreignKey);
} else {
	$galleryThumb = (!empty($id) ? $this->requestAction('/galleries/galleries/thumb/'.$id) : null);
}
# set up the config vars
$thumbLink = !empty($thumbLink) ? $thumbLink : '/galleries/galleries/view/'.$galleryThumb['Gallery']['id'];
$thumbTitle = !empty($thumbTitle) ? ' title ="'.$thumbTitle.'"' : ' title ="'.$galleryThumb['GalleryThumb']['filename'].'"';
$thumbSize = !empty($thumbSize) ? $thumbSize : 'small';
# get width from settings table
$indexWidth = !empty($galleryThumb['Gallery']['indexImageWidth']) ? $galleryThumb['Gallery']['indexImageWidth'] : $galleryThumb['Gallery'][$thumbSize.'ImageWidth'];
$indexHeight = !empty($galleryThumb['Gallery']['indexImageHeight']) ? $galleryThumb['Gallery']['indexImageHeight'] : $galleryThumb['Gallery'][$thumbSize.'ImageHeight'];
# if the width was defined in the element call
$thumbWidth = !empty($thumbWidth) ? $thumbWidth : $indexWidth;
$thumbHeight = !empty($thumbHeight) ? $thumbHeight : $indexHeight;

$thumbAlt = !empty($thumbAlt) ? $thumbAlt : $galleryThumb['GalleryThumb']['filename'];
$thumbClass = !empty($thumbClass) ? ' class="'.$thumbClass.'"' : ' class="gallery-thumb"';
$thumbId = !empty($thumbId) ? ' id="'.$thumbId.'"' : ' id="gallery'.$galleryThumb['Gallery']['id'].'"';
$thumbDiv = isset($thumbDiv) ? ($thumbDiv==true ? true : false) : true; //added to skip the display of div on demand (true/false)
$thumbLinkClass = !empty($thumbLinkClass) ? $thumbLinkClass : ''; //can have  class="gallery-thumb-link-class"
$thumbLinkAppend = !empty($thumbLinkAppend) ? ' '.$thumbLinkAppend : ''; //to append anything to the image within the link

if (!empty($galleryThumb)) {
	if($thumbDiv)	{ echo "<div {$thumbClass} {$thumbId}>";  } 
		$imagePath = $galleryThumb['GalleryThumb']['dir'].'thumb/'.$thumbSize.'/'.$galleryThumb['GalleryThumb']['filename'];
        $image = $this->Html->image($imagePath,
			array(
				'width' => $thumbWidth, 
				'height' => $thumbHeight,
				'alt' => $thumbAlt,
				),
			array(
				'conversion' => $galleryThumb['Gallery']['conversionType'],
				'quality' => 75,
				));	
		echo $this->Html->link($image . $thumbLinkAppend,
			$thumbLink, 
			array(
				'escape' => false,
				'class' => $thumbLinkClass,
				'title' => $thumbTitle,
				)); 
	if($thumbDiv) { echo "</div>"; } 
} else {
	if($thumbDiv) {  echo "<div {$thumbClass} {$thumbId}>"; }
		$imagePath = '/img/noImage.jpg';
        $image = $this->Html->image($imagePath,
			array(
				'width' => $thumbWidth, 
				'height' => $thumbHeight,
				'alt' => 'no image',
				));	
		echo $this->Html->link($image . $thumbLinkAppend,
			$thumbLink, 
			array(
				'escape' => false,
				'class' => $thumbLinkClass,
				'title' => $thumbTitle,
				));
	if($thumbDiv) { echo '</div>'; } 
}