<?php
// $Id$
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language ?>">

  <head>
    <title><?php print $head_title ?></title>
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <?php print $head ?>
    <style type="text/css" media="screen">@import "<?php print base_path(). path_to_theme(); ?>/andreas01.css";</style>
    <style type="text/css" media="projection">@import "<?php print base_path(). path_to_theme(); ?>/andreas01.css";</style>
    <style type="text/css" media="print">@import "<?php print base_path(). path_to_theme(); ?>/print.css";</style>
    <?php print $styles ?>
  </head>

<body>
<div id="wrap">
  <div id="header">
    <?php if ($site_name) { ?><h1 class="site_name"><a href="<?php print base_path() ?>" title="<?php print t('Home') ?>"><?php print $site_name ?></a></h1><?php } ?>
    <?php if ($site_slogan) { ?><p id="subtext"><?php print $site_slogan ?></p><?php } ?>
  </div>

  <img id="frontphoto" src="<?php if ($logo) { ?><?php print $logo ?><?php } else { ?><?php print base_path(). path_to_theme(); ?>/front.jpg<?php } ?>" width="760" height="175" alt="" />

  <div id="avmenu">
    <?php if ($primary_links) { ?>
      <h2 class="hide"><?php print t("Primary Links:")?></h2>
        <ul id="primary-links">
          <?php foreach ($primary_links as $link) { ?>
            <li><?php print $link?></li>
          <?php }; ?>
        </ul>
    <?php }; ?>
    <?php if ($sidebar_left != "") { ?>
      <div class="announce"><?php print $sidebar_left ?></div><?php /* print left sidebar if any blocks enabled */ ?>
    <?php }; ?>
  </div>

  <?php if ($sidebar_right != "") { ?>
    <div id="extras"><?php print $sidebar_right ?></div><?php /* print right sidebar if any blocks enabled */ ?>
  <?php }; ?>
	
  <div id="content">
    <div id="content-wrap">
      <div id="breadcrumb"><?php print $breadcrumb ?></div>

      <?php if ($mission) { ?><div id="mission"><?php print $mission ?></div><?php } ?>
      <?php print $help ?>
      <?php print $messages ?>

      <?php if ($title) { ?><h2 class="title"><?php print $title ?></h2><?php } ?>
      <div class="tabs"><?php print $tabs ?></div>

      <?php print $content; ?>

    </div>
  </div>

  <div class="footer" id="footer"><?php print $footer_message ?></div>
  <?php print $closure ?>
</div>
</body>
</html>