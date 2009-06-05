<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @subpackage ImageAnnotation
 **/
 
add_plugin_hook('install', 'image_annotation_install');
add_plugin_hook('uninstall', 'image_annotation_uninstall');
add_plugin_hook('admin_theme_header', 'image_annotation_javascripts');
add_plugin_hook('admin_theme_footer', 'image_annotation_admin_theme_footer');

function image_annotation_install()
{
    $db = get_db();
    $db->exec("CREATE TABLE `{$db->prefix}image_annotation_annotations` (
      `id` int(11) unsigned NOT NULL auto_increment,
      `user_id` int(11) unsigned NOT NULL,
      `file_id` int(11) unsigned NOT NULL,
      `top` mediumint(8) unsigned NOT NULL,
      `left` mediumint(8) unsigned NOT NULL,
      `width` mediumint(8) unsigned NOT NULL,
      `height` mediumint(8) unsigned NOT NULL,
      `text` text character set utf8 collate utf8_unicode_ci NOT NULL,
      `added` timestamp NOT NULL default CURRENT_TIMESTAMP,
      `modified` timestamp NOT NULL default '0000-00-00 00:00:00',
      `public` tinyint(4) NOT NULL default '1',
      PRIMARY KEY  (`id`),
      KEY `file_id` (`file_id`),
      KEY `added` (`added`),
      KEY `modified` (`modified`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");
}

function image_annotation_uninstall()
{
    $db = get_db();
    $db->exec("DROP TABLE {$db->prefix}image_annotation_annotations");
}

function image_annotation_javascripts()
{
    echo js('jquery');
    echo '<script type="text/javascript">jQuery.noConflict();</script>';
    echo js('jquery-ui-1.7.1');
    echo js('jquery.annotate');
    echo '<link rel="stylesheet" media="screen" href="', css('annotation'), '" />';
}    

function image_annotation_admin_theme_footer()
{
	echo '<div id="annotated-images">';
	
	echo '<ul id="annotated-images-thumbs">';
	$i = 0;
	while(loop_files_for_item()) {
        $file = get_current_file();
        if ($file->hasThumbnail()) {
			$i++;
			echo '<li><a href="#annotated-images-'.$i.'">';
			echo display_file($file, array('imageSize' => 'square_thumbnail', 'linkToFile' => false));
			echo '</a></li>';
        }
    }
	echo '</ul>';
	echo '<div id="annotated-images-fullsize">';
    $i = 0;
	while(loop_files_for_item()) {
        $file = get_current_file();
        if ($file->hasThumbnail()) {
			$i++;
			echo '<div id="annotated-images-'.$i.'">';
            image_annotation_display_annotated_image($file,true);
			echo '</div>';
        }
    }
	echo '</div>';
	echo '</div>';
?>
<script type="text/javascript" charset="utf-8">
Event.observe(window,'load',function(){
$$('#annotated-images-thumbs').each(function(tab_group){  
     new Control.Tabs(tab_group);  
 });
});

</script>

<?php
}

function image_annotation_display_annotated_image($imageFile, $isEditable=false, $imageSize='fullsize')
{        
    echo '<div class="annotated-image">';
    echo display_file($imageFile, array('imageSize' => $imageSize, 'linkToFile'=>false));
    echo '</div>';
    // specify the file annotations
    $useAjax = false;
    $imageId = $imageFile->id;
    $ajaxPath = CURRENT_BASE_URL . '/image-annotation/ajax/';
    $fileAnnotations = array( 
        'editable' => ($isEditable ? 'true': 'false'),
        'addNoteButtonText' => 'Add Annotation',
        'imageId' => $imageId,
        'getUrl' => $ajaxPath . "get-annotation/file_id/" . $imageId . '/',  
        'saveUrl' => $ajaxPath . "save-annotation/file_id/" . $imageId . '/',  
        'deleteUrl' => $ajaxPath . "delete-annotation/file_id/" . $imageId . '/',  
        'useAjax' => ($useAjax ? 'true': 'false')   
    );
?>
    <script language="javascript">
      jQuery(window).load(function() {
            jQuery("img[src$='files/display/<?php echo $imageId; ?>/<?php echo $imageSize; ?>']").annotateImage(<?php echo json_encode($fileAnnotations); ?>);        
      });

    </script>
<?php    
}