<?php

$content_arr['SLIDER'] = module_slider();

function module_slider() {
    global $ini_arr, $ROW_PAGE, $content_arr;

    $res_str = "";

    $param = array('id' => PAGE_ID);
    $query = "SELECT img, prop_arr
              FROM ".prdb."gallery
              WHERE page_id = :id 
                AND type = 'slide'
                AND LENGTH(img) > 0
              ORDER BY p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr)) return;

    $content_arr['HEAD'] .= '
        <script type="text/javascript" src="'.USER_TEMPLATE_DIR.'js/flickity.pkgd.min.js"></script>
    ';
    $content_arr['ONLOAD'] .= "
        var \$slider = $('.slider').flickity({
          wrapAround: true,
          autoPlay: 5000,
          imagesLoaded: true,
          pauseAutoPlayOnHover: false,
          cellAlign: 'left',
          contain: true,
          cellSelector: '.panel',
          prevNextButtons: false,
          setGallerySize: true,
          friction: 0.3
        });

        var flkty = \$slider.data('flickity');

    \$slider.on('select.flickity', function() {
      $('.slider-info').hide();
    })

    \$slider.on('select.flickity', function() {
      $('#info-' + flkty.selectedIndex).show();
    })
    ";

    $res_str .= "
    <div class='slider' id='slider'>
    ";
    $static_str = "";
    $counter = 0;
    foreach($row_arr as $row) {
        $prop_arr = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);
        $name = empty($prop_arr['name'][LANG]) ? "" : $prop_arr['name'][LANG];
        $content = empty($prop_arr['content'][LANG]) ? "" : $prop_arr['content'][LANG];
        if(!empty($row['img']) and file_exists(SLIDER_DIR.$row['img'])) {
            $res_str .= "
            <div class='panel oh'>
              <img src='".SLIDER_DIR.$row['img']."'>
            </div>";
              
            $static_str .= "
            <div class='slider-info' id='info-".$counter."'>
              <div class='h1'>".$name."</div>
              ".$content."
            </div>
            ";
            
            $counter++;
        }
    }
    
    $res_str .= "
      <div class='slider-bg rel'>
        <div class='mm-container'>
          <div class='page'>
            <div class='logo'></div>
            ".make_main_menu()."
          </div>
        </div>
        ".$static_str."
      </div>
    </div>";

    return $res_str;
}
?>