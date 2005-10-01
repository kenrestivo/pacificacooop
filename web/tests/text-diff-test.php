<?php

//$Id$

// unit test for my vitally required includes. return an error if
// the shit has hit the fan
chdir('../');
require_once('Mail.php');
require_once('CoopPage.php');
require_once('CoopObject.php');
require_once('Text/Diff.php');
require_once('Text/Diff/Renderer.php');
require_once('Text/Diff/Renderer/inline.php');
require_once('Text/Diff/Renderer/unified.php');

PEAR::setErrorHandling(PEAR_ERROR_PRINT);

$cp = new CoopPage(4);

$aud =& new CoopObject(&$cp, 'audit_trail', &$none);
$aud->obj->get(4994);

$sub =& $aud->obj->factory($aud->obj->table_name);
//$sub->get($aud->obj->index_id);
$field = $sub->fb_textFields[0]; // only try the first

$ugly = unserialize($aud->obj->details);
confessArray($ugly, 'ugly');


$old = explode("\n", $ugly[$field]['old']);
$new = explode("\n", $ugly[$field]['new']);

confessArray($old, 'old split');
confessArray($new, 'new split');


$diff =& new Text_Diff($old, $new);
confessObj($diff, 'the diff object');


confessArray($diff->getDiff(), 'diffs');

$rend =& new Text_Diff_Renderer_inline();
confessArray($rend->getParams(), 'inline params');
print $rend->render($diff);


$rend2 =& new Text_Diff_Renderer_unified();
confessArray($rend2->getParams(), 'unified params');
print $rend2->render($diff);

$cp->done();

?>