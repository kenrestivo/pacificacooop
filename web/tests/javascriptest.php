
<head>
<title>TEST</title>
</head>
<body>
<?

// Load the main class
chdir('../');
require_once('HTML/QuickForm.php');
require_once('lib/advmultselect.php');

zz($_POST);

// Instantiate the HTML_QuickForm object
$form = new HTML_QuickForm();

$fruit_array = array(
        'apple'     => 'Apple',
        'orange'    =>    'Orange',
        'pear'        =>    'Pear',
        'banana'    =>    'Banana',
        'cherry'    =>    'Cherry',
        'kiwi'        =>    'Kiwi',
        'lemon'        =>    'Lemon',
        'lime'        =>    'Lime',
        'tangerine'    =>    'Tangerine',
        );

$car_array = array(
    'dodge'        =>    'Dodge',
    'chevy'        =>    'Chevy',
    'bmw'        =>    'BMW',
    'audi'        =>    'Audi',
    'porsche'    =>    'Porsche',
    'kia'        =>    'Kia',
    'subaru'    =>    'Subaru',
    'mazda'        =>    'Mazda',
    'isuzu'        =>    'Isuzu',
    );

// Add some elements to the form
$form->addElement('header', null, 'Mult select test');

$form->addElement('advmultselect', 'fruit', 'Fruit:', $fruit_array);
$form->setDefaults(array('fruit' => $_REQUEST['fruit']));

$form->addElement('advmultselect', 'cars', 'Cars:', $car_array);
$form->setDefaults(array('cars' => $_REQUEST['cars']));

$form->addElement('submit', null, 'Send');

// Output the form
$form->display();

?>
?><hr>
<? show_source(basename($_SERVER['SCRIPT_NAME'])); ?>
<hr>
</body>
</html>
<?

//////////////////////////////////////////
function zz($var)
{
    ?><hr><pre><?
    print_r($var);
    ?></pre><hr><?
}
//////////////////////////////////////////
?>
------------------------------------------------------------------------------------
