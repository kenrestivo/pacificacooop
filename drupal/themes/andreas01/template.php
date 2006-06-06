<?php
// $Id$
function andreas01_regions() {
  return array(
       'left' => t('left sidebar'),
       'right' => t('right sidebar'),
       'content' => t('content'),
       'footer' => t('footer')
  );
}

function _phptemplate_variables($hook, $vars = array()) {
  if ($vars['logo'] == base_path() . path_to_theme() .'/logo.png') {
    $vars['logo'] = base_path() . path_to_theme() .'/front.jpg';
  }
 
  return $vars;
}

function phptemplate_profile_listing($account, $fields = array()) {

  $output  = "<div class=\"profile clearfix\">\n";
  $output .= theme('user_picture', $account);
  $output .= ' <div class="name">'. theme('username', $account) ."</div>\n";

  foreach ($fields as $field) {
    if ($field->value) {
      $output .= " <div class=\"field\">$field->value</div>\n";
    }
  }

  $output .= "</div>\n";

  return $output;
}

/**
* Theme a gallery page
*/
function phptemplate_image_gallery($galleries, $images) {
  drupal_set_html_head(theme('stylesheet_import', base_path() . drupal_get_path('module', 'image') .'/image.css'));

  // We'll add height to keep thumbnails lined up.
  $size = _image_get_dimensions('thumbnail');
  $width = $size['width'];
  $height = $size['height'];

  $content = '';
  if (count($galleries)) {
    $content.= '<ul class="galleries">';
    foreach ($galleries as $gallery) {
      $content .= '<li style="min-height : '.$height .'px">';
      if ($gallery->count)
        $content.= l(image_display($gallery->latest, 'thumbnail'), 'image/tid/'.$gallery->tid, array(), NULL, NULL, FALSE, TRUE);
      $content.= "<h3>".l($gallery->name, 'image/tid/'.$gallery->tid) . "</h3>\n";
      $content.= '<div class="description">'. check_markup($gallery->description) ."</div>\n";
      $content.= '<p class="count">' . format_plural($gallery->count, 'There is 1 image in this gallery', 'There are %count images in this gallery') . "</p>\n";
      if ($gallery->latest->changed) {
        $content.= '<p class="last">'. t('Last updated: %date', array('%date' => format_date($gallery->latest->changed))) . "</p>\n";
      }
      $content.= "</li>\n";
    }
    $content.= "</ul>\n";
  }

  if (count($images)) {
    $height += 75;
    $content.= '<ul class="images">';
    foreach ($images as $image) {
      $content .= '<li';
      if ($image->sticky) {
        $content .= ' class="sticky"';
      }
      $content .= ' style="min-height : '.$height .'px; min-width : '.$width.'px;"';
      $content .= ">\n";
      $content .= l(image_display($image, 'thumbnail'), 'node/'.$image->nid, array(), NULL, NULL, FALSE, TRUE);
      $content .= '<h3>'.l($image->title, 'node/'.$image->nid)."</h3>";
      if (theme_get_setting('toggle_node_info_' . $image->type)) {
        $content .= '<div class="author">'. t('Posted by: %name', array('%name' => theme('username', $image))) . "</div>\n";
        if ($image->created > 0) {
          $content .= '<div class="date">'.format_date($image->created)."</div>\n";
        }
      }
      $content .= "</li>\n";
    }
    $content.= "</ul>\n";
  }

  if ($pager = theme('pager', NULL, variable_get('image_images_per_page', 6), 0)) {
    $content.= $pager;
  }

  If (count($images) + count($galleries) == 0) {
      $content.= '<p class="count">' . format_plural(0, 'There is 1 image in this gallery', 'There are %count images in this gallery') . "</p>\n";
  }

  return $content;
}

function phptemplate_system_themes($form) {
  foreach (element_children($form) as $key) {
    $row = array();
    if (is_array($form[$key]['description'])) {
      $row[] = form_render($form[$key]['description']) . form_render($form[$key]['screenshot']) ;
      $row[] = array('data' => form_render($form['status'][$key]), 'align' => 'center');
      if ($form['theme_default']) {
        $row[] = array('data' => form_render($form['theme_default'][$key]) . form_render($form[$key]['operations']), 'align' => 'center');
      }
    }
    $rows[] = $row;
  }
  $header = array(t('Name/Screenshot'), t('Enabled'), t('Default'));
  $output = theme('table', $header, $rows);
  $output .= form_render($form);
  return $output;
}

function phptemplate_system_user($form) {
  foreach (element_children($form) as $key) {
    $row = array();
    if (is_array($form[$key]['description'])) {
      $row[] = form_render($form[$key]['description']) . form_render($form[$key]['screenshot']);
      $row[] = array('data' => form_render($form['theme'][$key]), 'align' => 'center');
    }
    $rows[] = $row;
  }

  $header = array(t('Name/Screenshot'), t('Selected'));
  $output = theme('table', $header, $rows);
  return $output;
}
?>