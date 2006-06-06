<?php
// $Id$
?>
<div class="comment <?php print ($comment->new) ? 'comment-new' : '' ?>">
  <p class="header">
    <?php if ($comment->new) { ?> 
      <div class="new"><?php print $new ?></div>
    <?php }; ?>
    <?php if ($picture) { ?>
      <div class="right"><?php print $picture ?></div>
    <?php }; ?>
    <div class="comment-title"><?php print $title ?></div>
    <div class="comment-author"><?php print $author ?> | <?php print $date ?></div>
  </p>   
  <div class="content"><?php print $content ?></div> 
  <div class="postmetadata"><?php print $links ?></div> 
</div>