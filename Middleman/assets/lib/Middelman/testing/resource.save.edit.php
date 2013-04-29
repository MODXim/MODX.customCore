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

<ul>
  <?php foreach($modx->documentListing as $alias => $id): ?>
  <li><?php echo $id ?> - <a href="[~[*id*]~]?edit_id=<?php echo $id ?>"><?php echo $alias ?></a></li>
  <?php endforeach; ?>
</ul>

<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//define('IN_MANAGER_MODE', 'true');
//App::debug(get_defined_constants(true));

$path = realpath('/../' . dirname(__FILE__));
require_once $path.'/Resource/Save.php';
$crud = new Resource_Save;
$crud->set_content_fields($_POST);
if( !empty($_POST) ) {
  $crud->execute();
}

unset($crud->modx);
//App::debug($crud);

if( !isset($_GET['edit_id']) ) {
  echo 'no id is set';
  return;
}

$edit_id = $_GET['edit_id'];
$doc = $modx->getTemplateVars('*', '*', $edit_id);

//App::debug($doc);
$result['content'] = $result['tv'] = $result = array();
foreach($doc as $val) {
  if( isset($val['description'])) {
    $result['tv'][$val['id']] = $val;
  } else {
    $result['content'][$val['name']] = $val['value'];  
  }
}

$doc = $modx->getTemplateVarOutput('*', $edit_id, $result['content']['published']);

//App::debug($modx->getUserDocGroups());
//App::debug($result);
//App::debug($doc);
?>



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
    <tr>
      <td class="label">Mode</td>
      <td class="value"><input name="mode" value="27" /></td>
    </tr>
    <?php foreach ( $result['tv'] as $tv ): ?>
    <tr>
      <td class="label"><?php echo $tv['name'] ?>:</td>
      <td class="value"><input type="text" name="tv<?php echo $tv['id'] ?>" value="<?php echo $tv['value'] ?>" /></td>
    </tr>
    <?php endforeach; ?>
    <?php foreach ( $result['content'] as $name => $value ): ?>
    <tr>
      <td class="label"><?php echo $name ?>:</td>
      <td class="value"><input type="text" name="<?php echo $name ?>" value="<?php echo $value ?>" /></td>
    </tr>
    <?php endforeach; ?>
    <tr class="submit">
      <td class="label">&nbsp;</td>
      <td class="value"><input type="submit" name="test[sneaky snake]" value="Post form" /></td>
    </tr>
  </table>
</form>