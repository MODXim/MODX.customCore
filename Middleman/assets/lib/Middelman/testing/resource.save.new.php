<?php
echo 'hello<br/><br/>';
error_reporting(E_ALL);
ini_set('display_errors', '1');

//define('IN_MANAGER_MODE', 'true');
//App::debug(get_defined_constants(true));

$path = realpath('/../' . dirname(__FILE__));
require_once $path.'/Resource/Save.php';
$crud = new Resource_Save;
$crud->set_content_fields($_POST['test']);
if( !empty($_POST) ) {
  $crud->execute();
}

//unset($crud->modx);
//App::debug($crud);

$fields = $crud->get_content_field_names();

$tvs_to_test = array(
  'tv_one'   => 'tv1',
  'tv_two'   => 'tv2',
  'tv_three' => 'tv3',
  'tv NA'    => 'tv99',
  );

foreach ($tvs_to_test as $label => $id) {
  $tv_val[$id] = isset($_POST[$id]) ? $_POST[$id] : '';
}
?>

<style type="text/css">
  .demo-form {
    width: 600px;
    margin: 20px auto;
  }
  
  .demo-form table {
    width: 100%;
    border-collapse: collapse;
    border-top: 1px solid #e3e3e3;
    border-left: 1px solid #e3e3e3;
  }
  
  .demo-form td {
    border-bottom: 1px solid #e3e3e3;
    border-right: 1px solid #e3e3e3;
    padding: 4px 10px;
  }
  
  .demo-form input {
    width: 300px;
  }
  .demo-form tr.submit input {
    width: auto;
  }
  
  .demo-form li input {
    width: 25px;
  }
</style>
<form class="demo-form" method="post">
  <ul>
    <li>
      <input type="checkbox" class="checkbox" name="chkalldocs" id="groupall" checked="checked" onclick="makePublic(true);" />
      <label for="groupall" class="warning">All Resource Groups (Public)</label></li>

    <li>
      <input type="checkbox" class="checkbox" name="docgroups[]" onclick="makePublic(false);" id="group-1" value="1,new" />
      <label for="group-1">Administrator</label></li>

    <li>
      <input type="checkbox" class="checkbox" name="docgroups[]" onclick="makePublic(false);" id="group-2" value="2,new" />
      <label for="group-2">Manager</label></li>
  </ul>
  <table>
    <?php foreach ( $tvs_to_test as $label => $id ): ?>
    <tr>
      <td class="label"><?php echo $label ?>:</td>
      <td class="value"><input type="text" name="<?php echo $id ?>" value="<?php echo $tv_val[$id] ?>" /></td>
    </tr>
    <?php endforeach; ?>
    <?php foreach ( $fields as $field ): ?>
    <?php $value = isset($crud->fields[$field]) ? $crud->fields[$field] : ''; ?>
    <tr>
      <td class="label"><?php echo $field ?>:</td>
      <td class="value"><input type="text" name="test[<?php echo $field ?>]" value="<?php echo $value ?>" /></td>
    </tr>
    <?php endforeach; ?>
    <tr class="submit">
      <td class="label">&nbsp;</td>
      <td class="value"><input type="submit" name="test[sneaky snake]" value="Post form" /></td>
    </tr>
  </table>
</form>