
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


$fruit_number_array = array(
        '222'     => 'Apple',
        '223'    =>    'Orange',
        '224'        =>    'Pear',
        '225'    =>    'Banana',
        '226'    =>    'Cherry',
        '227'        =>    'Kiwi',
        '228'        =>    'Lemon',
        '229'        =>    'Lime',
        '230'    =>    'Tangerine',
        );

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

$form->addElement('advmultselect', 'fruit_number', 'Fruit Number:', 
				  $fruit_number_array);
$form->setDefaults(array('fruit_number' => $_REQUEST['fruit_number']));

$form->addElement('advmultselect', 'fruit', 'Fruit:', $fruit_array);
$form->setDefaults(array('fruit' => $_REQUEST['fruit']));

$form->addElement('advmultselect', 'cars', 'Cars:', $car_array);
$form->setDefaults(array('cars' => $_REQUEST['cars']));
//print serialize($_REQUEST['cars']);
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
