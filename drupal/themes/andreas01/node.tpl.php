<?php
// $Id$
?>
<div class="node<?php print ($sticky) ? " sticky" : ""; ?>">
  <?php if ($page == 0) { ?>
    <h2><a href="<?php print $node_url ?>" title="<?php print $title ?>"><?php print $title ?></a></h2>
  <?php } elseif ($picture) { ?>
    <div class="left"><?php print $picture ?></div>
  <?php }; ?>
  <div class="content clearfix"><?php print $content ?></div>
  <?php if ($links) { ?>
    <div class="info clearfix">
      <?php if ($terms) { print t("Posted in") . ' ' . $terms; }; ?>
        <?php if ($submitted) { ?>
          <?php print $submitted ?>
          <br />
        <?php }; ?>
        <?php print $links ?>
    </div>
  <?php } else { ?>
    <div class="info clearfix"><br /></div>
  <?php }; ?>
</div>