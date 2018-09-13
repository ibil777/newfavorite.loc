<?php

$content_arr['GALLERY'] = module_gallery();

function module_gallery() {
    global $ini_arr, $ROW_PAGE, $content_arr;

    $res_str = "";

    $param = array('id' => PAGE_ID);
    $query = "SELECT img, prop_arr
              FROM ".prdb."gallery
              WHERE page_id = :id 
                AND type = 'img'
                AND LENGTH(img) > 0
              ORDER BY p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr)) return;

    $content_arr['HEAD'] .= '
        <script src="[TPL_DIR]js/unitegallery.min.js"></script>
        <link href="[TPL_DIR]css/unite-gallery.css" rel="stylesheet" type="text/css" />
        <script src="[TPL_DIR]js/ug-theme-tilesgrid.js"></script>
    ';
    $content_arr['ONLOAD'] .= '
        jQuery(".gallery").unitegallery({
            gallery_theme: "tilesgrid",
            tile_width: 200,
            tile_height: 200,
            grid_padding: 10,
            grid_num_rows: 30,
            grid_space_between_cols: 10,
            grid_space_between_rows: 10,
            tile_enable_border:true,
            tile_enable_shadow:true,
            tile_overlay_opacity: 0.2,
            tile_enable_textpanel: true,
            tile_textpanel_source: "title",
            lightbox_textpanel_enable_description: true
        });
    ';

    $res_str .= "
    <div class='gallery-container rel'>
      <div class='gallery' id='gallery'>
    ";
    foreach($row_arr as $row) {
        $folder = substr($row['img'], 0, 1)."/";
        $prop_arr = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);
        $name = empty($prop_arr['name'][LANG]) ? "" : $prop_arr['name'][LANG];
        $content = empty($prop_arr['content'][LANG]) ? "" : $prop_arr['content'][LANG];
        if(file_exists(GALLERY_DIR."small/".$folder.$row['img']) and file_exists(GALLERY_DIR."big/".$folder.$row['img'])) {
            $res_str .= "<img alt='".$name."'
              src='".GALLERY_DIR."small/".$folder.$row['img']."'
              data-image='".GALLERY_DIR."big/".$folder.$row['img']."'
              data-description='".$content."'
            >";
        }
    }
    $res_str .= "</div>
    </div>";

    return $res_str;
}
?>