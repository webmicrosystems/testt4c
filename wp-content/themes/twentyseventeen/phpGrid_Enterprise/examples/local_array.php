<?php
require_once("../conf.php");      
if (!isset($HTTP_POST_VARS) && isset($_POST)){ $HTTP_POST_VARS = $_POST;}  // backward compability when register_long_arrays = off in config 

$theme_name = (isset($_POST['_gridThemeRoller']))? $_POST['_gridThemeRoller']:'clean';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Load Grid from Local Data Array with Theme Changer</title>
</head>
<body> 

<style>
.tstyle
{
display:block;background-image:none;margin-right:-2px;margin-left:-2px;height:14px;padding:5px;background-color:red;color:whitefont-weight:bold
}
</style>

<form id="_form" name="_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

Selected Theme <select id="_gridThemeRoller" name="_gridThemeRoller" onchange="document.forms['_form'].submit();">
	<option value="dot-luv"			<?php echo ($theme_name=='dot-luv') ? ' selected' : '';  ?>>Dot-Luv</option>
	<option value="absolute"		<?php echo ($theme_name=='absolute') ? ' selected' : '';  ?>>Absolute</option>
	<option value="aristo"			<?php echo ($theme_name=='aristo') ? ' selected' : '';  ?>>Aristo</option>
    <option value="cobalt"			<?php echo ($theme_name=='cobalt') ? ' selected' : '';  ?>>Cobalt</option>
	<option value="black-beauty"	<?php echo ($theme_name=='black-beauty') ? ' selected' : '';  ?>>Black-Beauty</option>
	<option value="blackandred"		<?php echo ($theme_name=='blackandred') ? ' selected' : '';  ?>>Blackandred</option>
	<option value="clean"			<?php echo ($theme_name=='clean') ? ' selected' : '';  ?>>Clean</option>
	<option value="cupertino"		<?php echo ($theme_name=='cupertino') ? ' selected' : '';  ?>>Cupertino</option>
	<option value="dark-round"		<?php echo ($theme_name=='dark-round') ? ' selected' : '';  ?>>Dark-Round</option>
	<option value="duck"			<?php echo ($theme_name=='duck') ? ' selected' : '';  ?>>Duck</option>
	<option value="excite-bike"		<?php echo ($theme_name=='excite-bike') ? ' selected' : '';  ?>>Excite-Bike</option>
	<option value="retro"			<?php echo ($theme_name=='retro') ? ' selected' : '';  ?>>Retro</option>
    <option value="flick"			<?php echo ($theme_name=='flick') ? ' selected' : '';  ?>>Flick</option>
	<option value="overcast"		<?php echo ($theme_name=='overcast') ? ' selected' : '';  ?>>Overcast</option>
	<option value="pepper-grinder"	<?php echo ($theme_name=='pepper-grinde') ? ' selected' : '';  ?>>Pepper-Grinder</option>
	<option value="purple-haze"		<?php echo ($theme_name=='purple-haze') ? ' selected' : '';  ?>>Purple-Haze</option>
	<option value="redmond"			<?php echo ($theme_name=='redmond') ? ' selected' : '';  ?>>Redmond</option>
	<option value="smoothness"		<?php echo ($theme_name=='smoothness') ? ' selected' : '';  ?>>Smoothness</option>
	<option value="start"			<?php echo ($theme_name=='start') ? ' selected' : '';  ?>>Start</option>
	<option value="tiffany"			<?php echo ($theme_name=='tiffany') ? ' selected' : '';  ?>>Tiffany</option>
	<option value="ui-darkness"		<?php echo ($theme_name=='ui-darkness') ? ' selected' : '';  ?>>UI-Darkness</option>
	<option value="ui-lightness"	<?php echo ($theme_name=='ui-lightness') ? ' selected' : '';  ?>>UI-Lightness</option>
</select>

<?php

$name = array('Bonado', 'Sponge', 'Decker', 'Snob', 'Kocoboo');
for ($i = 0; $i < 200; $i++)
{
	$data1[$i]['id']    = $i+1;
	$data1[$i]['foo']    = md5(rand(0, 10000));
	$data1[$i]['bar1']    = 'bar'.($i+1);
	$data1[$i]['bar2']    = 'bar'.($i+1);
	$data1[$i]['cost']    = rand(0, 100);
	$data1[$i]['name']	  = $name[rand(0, 4)];
	$data1[$i]['quantity'] = rand(0, 100);
	$data1[$i]['discontinued'] = rand(0, 1);
	$data1[$i]['email']	= 'grid_'. rand(0, 100) .'@example.com';
	$data1[$i]['notes'] = '';
}

$dg = new C_DataGrid($data1, "id", "data1");
$dg->set_col_title("id", "ID")->set_col_width('id', 20);
$dg->set_col_title("foo", "Foo");
$dg->set_col_title("bar", "Bar");
$dg->set_col_title('discontinued', 'disc.')
	->set_col_width('discontinued', 35);
$dg->set_col_align('cost', 'right')
	->set_col_currency('cost', '$');
$dg->set_col_width('bar1', 40);
$dg->set_col_width('quantity', 220);
$dg->set_row_color('lightblue', 'yellow', 'lightgray');
$dg->set_databar('quantity', 'blue');
$dg->enable_search(true);
$dg->enable_edit('FORM', 'CRUD');
$dg->enable_export('EXCEL');
$dg->enable_resize(true);
$dg->set_col_format('email', 'email');
$dg->set_col_dynalink('name', 'http://example.com', array("id", "name"));
$dg->set_caption('Array Data Test');
$dg->set_col_hidden('bar2');
$dg->set_col_property('notes', array('edittype'=>'textarea','editoptions'=>array('cols'=>40,'rows'=>10)))
	->set_col_wysiwyg('notes');
$dg->set_dimension(900, 400);
//$dg->set_multiselect(true);
$dg->set_conditional_value('discontinued', '==1',  array("TCellStyle"=>"tstyle"));
$dg->set_theme($theme_name);
$dg->display();
?>

