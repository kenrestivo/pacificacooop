
<?php

require_once "HTML/QuickForm.php";



$uploadForm = new HTML_QuickForm('upload_form', 'post');
$uploadForm->setMaxFileSize(8388608); // 8MB s/b as big as i need
$file =& $uploadForm->addElement('file', 'filename', 'File:');
$uploadForm->addRule('filename', 'You must select a file', 'uploadedfile');
$uploadForm->addElement('submit', 'btnUpload', 'Upload');
if ($uploadForm->validate()) {
    $uploadForm->process('process', true);
}
else {
    $uploadForm->display();
}

function process($values) 
{
    global $file;
    $path = "../../files"; // XXX remember to make it just ../ for live!

    print"<pre>";
    print_r($file);
    print_r($values);
    print"</pre>";

    $unique_filename = sprintf("%d-%s", rand(1,200), 
                               $values['filename']['name']);
    if ($file->isUploadedFile()) {
        $file->moveUploadedFile($path, $unique_filename);
        print "file uploaded!";
    }
    else {
        print "No file uploaded";
    }
}

?>
