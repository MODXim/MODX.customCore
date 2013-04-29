<?php
/*
  on line 442 of pesudo-save-content-processor.php
  on line 521 of /manager/processors/save_content.processor.php
  line 605 here
  
  testing is at: http://dev.directory-project.com/
  
  - Alias check not working
*/
class Resource_Save {

  public function __construct()
  {
    global $modx;
    $this->modx =& $modx;
    
    $this->fields = array();
    $this->field_defaults = array();
    
    $this->now = time();
    
    $this->init();
  }
  
  public function init()
  {
    require_once MODX_BASE_PATH.'manager/includes/error.class.inc.php';
    $this->e = new errorHandler;

    require_once MODX_BASE_PATH.'manager/includes/extenders/manager.api.class.inc.php';
    $this->modx->manager = new ManagerAPI;
  }
  
  public function execute()
  {
    $this->set_action_to_take();
    $this->load_language();
    $this->can_save_document();
    $this->sanitize_content_fields();
    $this->set_table_names();

    $this->document_groups = $this->get_document_groups();
    $this->user_docgroups = $this->get_user_docgroups();
    $this->can_user_access_document();
    $this->get_document_tvs();
    $this->can_save_here();
    $this->save_document();
  }

  public function set_action_to_take()
  {
    $this->action_to_take = 'new';
    if( isset($_POST['mode']) AND ( $_POST['mode'] == 73 OR $_POST['mode'] == 27)) {
      $this->action_to_take = 'edit';
    }
  }
    
  public function load_language()
  {
    global $_lang;
    $this->_lang = $lang;
  }

  public function can_save_document() 
  {
    if( !$this->modx->hasPermission('save_document')) {
      $this->e->setError(3);
      $this->e->dumpError();
    }
  }
  
  public function set_table_names()
  {
    $table_names = array(
      'document_groups', 'documentgroup_names', 'member_groups', 'membergroup_access',
      'keyword_xref', 'site_content', 'site_content_metatags', 'site_tmplvar_access',
      'site_tmplvar_contentvalues', 'site_tmplvar_templates', 'site_tmplvars'
      );
    
    foreach($table_names as $table) {
      $this->table[$table] = $this->modx->getFullTableName($table);
    }
  }
  
  public function get_content_field_names()
  {
    $field_names = array(
      'id', 'introtext', 'content', 'description',
      'link_attributes', 'isfolder', 'richtext',
      'parent', 'template', 'menuindex', 'searchable',
      'cacheable', 'pub_date', 'unpub_date', 'published',
      'type', 'contentType',
      'editedon', 'editedby', 
      'content_dispo', 'longtitle', 'donthit', 'menutitle', 'hidemenu', 'pagetitle', 'alias', 'alias_path',
      );
    
    return $field_names;
  }
  
  public function set_content_fields($array = array())
  {
    $this->raw_fields = $array;  
  }
  
  public function sanitize_content_fields() 
  {
    $raw = (is_array($this->raw_fields)) ? $this->raw_fields : array();
    $fields = array();
    
    /* Remove unallowed fields */
    $allowed_field_names = array_flip($this->get_content_field_names());

    $array = array();
    foreach($allowed_field_names as $key => $x) {
      if( isset($raw[$key]) ) {
        $array[$key] = $raw[$key];
      }
    }

    /* if field has method, field_name = value comes from that method */
    foreach($array as $key => $value) {
      $method_name = 'set_field_' . strtolower($key);
      if( method_exists($this, $method_name)) {
        $fields[$key] = $this->$method_name($value);
      } else {
        $fields[$key] = $value;
      }
      $this->fields[$key] = $this->modx->db->escape($fields[$key]);
    }
  }
  
  public function set_field_id($value) 
  {
    return is_numeric($value) ? $value : '';
  }

  public function set_field_parent($value) 
  {
    return is_numeric($value) ? $value : '0';
  }
  
  public function set_field_type($value)
  {
    $value = ($value == 'reference') ? $value : 'document';
    return $value;
  }

  public function set_field_contentType($value)
  {
    return !empty($value) ? $value : 'text/html';
  }
  
  public function set_field_template($value)
  {
    return is_numeric($value) ? $value : $this->modx->config['default_template'];
  }

  public function set_field_menuindex($value) 
  {
    return is_numeric($value) ? $value : '0';
  }

  public function set_field_content_dispo($value) 
  {
    return is_numeric($value) ? $value : '0';
  }
  
  public function set_field_editedon($value)
  {
    return time();
  }
  
  public function set_field_editedby($value)
  {
    return $this->get_user_id();
  }

  public function set_field_donthit($value) 
  {
    return is_numeric($value) ? $value : '0';
  }

  public function set_field_hidemenu($value) 
  {
    return is_numeric($value) ? $value : '0';
  }

  public function set_field_pagetitle($value) 
  {
    if( trim($value) != '') {
      return $value;
    }
    
    if( $this->fields['type'] == 'reference') {
      return $this->_lang['untitled_weblink'];
    }

    return $this->_lang['untitled_resource'];
  }
  
  public function set_field_alias_path($value)
  {
    $parent_id = isset($this->fields['parent']) ? $this->fields['parent'] : 0;
    
    if( $parent_id != 0) {
      $parent_url = $this->modx->makeUrl($parent_id);
    } else {
      $parent_url = $this->modx->config['base_url'];
    }
    
    $alias_path = $parent_url .'/'. $this->fields['alias'];
    
    if( strpos($alias_path, $this->modx->config['base_url']) !== false ) {
      $alias_path = substr($alias_path, strlen($this->modx->config['base_url']));
    }
    
    return $alias_path;
    
    $test = array(
      'parent_id' => $parent_id,
      'parent_url' => $parent_url,
      'alias_path' => $alias_path,
      'alias' => $this->fields['alias'],
      'base_url' => $this->modx->config['base_url'],
      'would_return' => $would_return,
      );
    
    App::debug($test);
    
    return $alias_path;
  }

  public function set_field_alias($value) 
  {
    if( $this->modx->config['friendly_urls'] != 1) {
      return $this->modx->stripAlias($value);
    }
    
    App::debug($value);
    
    if( $value == '' AND $this->modx->config['automatic_alias'] == 1) {
      /* option 1 */
      return $this->autoset_alias($value);
    } elseif ($value != '' AND $this->modx->config['allow_duplicate_alias'] != 1) {
      /* option 2 */
      return $this->check_duplicate_alias($value, $this->fields['parent'], $this->fields['id']);
    } else {
      return strtolower($this->modx->stripAlias(trim($value)));
    }

  }
  
  public function autoset_alias($alias)
  {
      $alias = strtolower($this->modx->stripAlias(trim($this->fields['pagetitle'])));
      if( $this->modx->config['allow_duplicate_alias'] != 1) {
        $alias = $this->get_nonduplicate_alias($alias, '', $this->fields['id']);
        return $alias;
      }
      return $alias;
  }
  
  public function check_duplicate_alias($alias, $parent = '', $id = '')
  {
    $alias = strtolower($this->modx->stripAlias(trim($alias)));
    if($this->modx->config['use_alias_path'] == 1) {
      $query = "SELECT id from {$this->table['site_content']} WHERE id <> {$id} AND alias = {$alias} AND parent = {$parent} LIMIT 1";
    } else {
      $query = "SELECT id from {$this->table['site_content']} WHERE id <> {$id} AND alias = {$alias} LIMIT 1";
    }
    
    if( $this->modx->db->getValue($query) > 0) {
      $message = sprintf($this->_lang["duplicate_alias_found"], $id, $alias);
      $this->web_alert_error($message, $id, 'check_duplicate_alias');
      return false;
    }

    return $alias;    
  }
  
  public function get_nonduplicate_alias($alias, $count = '', $id = '')
  {
    $tmp_alias = $alias . $count;
    $query = "SELECT COUNT(id) FROM {$this->table['site_content']} WHERE id<>'{$id}' AND alias='{$tmp_alias}'";
    if($this->modx->db->getValue($query) != 0) {
      $tmp_alias = $this->get_nonduplicate_alias($alias, $count+1, $id);
    }
    
    return $tmp_alias;
  }

  public function set_field_pub_date($value) 
  {
    if( empty($value) ) {
      return 0;
    }
    
    return $this->modx->toTimeStamp($value);
  }

  public function set_field_unpub_date($value) 
  {
    if( empty($value) ) {
      return 0;
    }
    
    return $this->modx->toTimeStamp($value);
  }
  
  public function set_field_published($value)
  {
    $value = (empty($value)) ? $this->modx->config['publish_default'] : $value;
    $now = time();
    
    $pub_date = $this->fields['pub_date'];
    if( $pub_date != 0) {
      if( $pub_date < $now) {
        $value = 1;
      } elseif ($pub_date > $now) {
        $value = 0;
      }
    }
    
    $unpub_date = $this->fields['unpub_date'];
    if( $unpub_date != 0) {
      if( $unpub_date < $now) {
        $value = 0;
      }
    }
    
    return $value;
  }
  
  public function get_user_id()
  {
    return $this->modx->getLoginUserID();
  }
  
  public function get_user_role()
  {
    return $_SESSION['mgrRole'];
  }
  
  public function get_document_groups()
  { 
    if( isset($_POST['chkalldocs']) AND $_POST['chkalldocs'] == 'on') {
      return array();
    }
    
    return $_POST['docgroups'];
  }
  
  public function get_user_docgroups()
  {
    if( !isset($_SESSION['mgrDocgroups']) OR !is_array($_SESSION['mgrDocgroups']) ) {
      return null;
    }
    
    return implode(',', $_SESSION['mgrDocgroups']);
    
  }
  public function can_user_access_document()
  {
    if( $_SESSION['mgrRole'] == 1 OR !is_array($this->document_groups)) {
      return true;
    }
    
    $doc_group_list = implode(',', array_filter($this->document_groups, 'is_numeric'));
    if( empty($doc_group_list)) {
      return true;
    }
    
    $select = " SELECT COUNT(mg.id) ";
    $from   = " FROM {$this->table['membergroups_access']} mga, {$this->table['member_groups']} mg ";
    $where  = " WHERE mga.membergroup = mg.user_group ";
    $where .= " AND mga.documentgroups IN({$doc_group_list}) ";
    $where .= " AND mg.member = {$_SESSION['mgrInternalKey']};";
    
    $rs    = $this->modx->db->query($select.$from.$where);
    $count = $this->modx->db->getValue($rs);
    
    if( $count != 0 ) {
      return true;
    }
    
    $message = $this->_lang["resource_permissions_error"];
    $this->web_alert_error($message, $this->fields['id'], 'can_user_access_document');
  }
  
  public function get_sql_for_document_tvs()
  {
    $select = " SELECT DISTINCT tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value ";
    $from   = " FROM {$this->table['site_tmplvars']} as tv ";
    $join1  = " INNER JOIN {$this->table['site_tmplvar_templates']} AS tvtpl ";
    $join1 .= "   ON tvtpl.tmplvarid = tv.id ";
    $join2  = " LEFT JOIN {$this->table['site_tmplvar_contentvalues']} AS tvc ";
    $join2 .= "   ON tvc.tmplvarid = tv.id AND tvc.contentid = '{$this->fields['id']}'";
    $join3  = " LEFT JOIN {$this->table['site_tmplvar_access']} AS tva ";
    $join3 .= "   ON tva.tmplvarid = tv.id ";
    $where  = " WHERE tvtpl.templateid = '{$this->fields['template']}' ";
    /* I want to get the access level seperately */
    //$where .= " AND (1 = '{$_SESSION['mgrRole']}' OR ISNULL(tva.documentgroup) ";
    //$where .= (!$this->get_user_docgroups) ? ') ' : " OR tva.documentgroup IN ({$this->get_user_docgroups})) ";
    $order  = " ORDER BY tv.rank ";
    
    return $select.$from.$join1.$join2.$join3.$where.$order;
  }
  
  public function insert_tvs($doc_id, $tvs)
  {
    if($doc_id == '' OR $doc_id < 1 OR !is_array($tvs) ) {
      return;
    }
    
    foreach($tvs as $tv) {
      $tv = array_map(array($this->modx->db, 'escape'), $tv);
      $tv['contentid'] = $doc_id;
      $this->modx->db->insert($tv, $this->table['site_tmplvar_contentvalues']);
    }
  }

  public function update_tvs($doc_id, $tvs)
  {
    if($doc_id == '' OR $doc_id < 1 OR !is_array($tvs) ) {
      return;
    }
    
    foreach($tvs as $tv) {
      $tv = array_map(array($this->modx->db, 'escape'), $tv);
      $tv['contentid'] = $doc_id;
      $where = "tmplvarid = '{$tv['tmplvarid']}' AND contentid = '{$tv['contentid']}' ";
      $this->modx->db->update($tv, $this->table['site_tmplvar_contentvalues'], $where);
    }
  }
    
  public function delete_tvs($doc_id, $tvs)
  {
    if($doc_id == '' OR $doc_id < 1 OR !is_array($tvs) OR empty($tvs) ) {
      return;
    }
    
    $where = sprintf("id IN (%s)", implode(',', $tvs));
    $this->modx->db->delete($this->table['site_tmplvar_contentvalues'], $where);
  }
  
  public function get_document_tvs()
  {
    $this->tvs = array();
    $this->tvs_to_save = array();
    $this->tvs_to_update = array();
    $this->tvs_to_delete = array();

    $current_tvs = $this->modx->db->makeArray($this->modx->db->select('id, tmplvarid', $this->table['site_tmplvar_contentvalues'], "contentid = '{$this->fields['id']}'"));
    foreach($current_tvs as $current) {
      $this->current_tvs[$current['tmplvarid']] = $current['id'];
    }
    
    $sql = $this->get_sql_for_document_tvs();
    $results = $this->modx->db->makeArray($this->modx->db->query($sql));
    /* I want to get the access level seperately */
    
    foreach($results as $row) {
      $method_name = 'get_tvtype_' . strtolower($row['type']);
      if( !method_exists($this, $method_name)) {
        $method_name = 'get_tvtype_default';
      }
      $tv = $this->$method_name($row);
      if( strlen($tv) > 0 AND $tv != $row['default_text']) { 
        if( isset($this->current_tvs[$row['id']]) ) {
          /* We are updating the value */
          $this->tvs_to_update[$row['id']] = array( 'tmplvarid' => $row['id'], 'value' => $tv);        
        } else {
          /* Save value if not modified */
          $this->tvs_to_save[$row['id']] = array( 'tmplvarid' => $row['id'], 'value' => $tv);
        }
      } else { 
        /* Mark for deletion */
        $this->tvs_to_delete[$row['name']] = $row['id'];
      }
      $this->tvs[] = $tv;
    }
    
/*
    $test = array(
      'current' => $this->current_tvs,
      'to_save' => $this->tvs_to_save,
      'to_update' => $this->tvs_to_update,
      'to_delete' => $this->tvs_to_delete,
      );
    
    App::debug($test);
    App::debug($results);
    App::debug($this->tvs)->kill();
*/
  }
  
  public function get_tvtype_url($row)
  {
    $tv = $_POST['tv'.$row['id']];
    
    if( $_POST['tv'.$row['id'].'_prefix'] != '--' ) {
      $tv = str_replace( array('feed://', 'ftp://', 'http://', 'https://', 'mailto:'), '', $tv);
      $tv = $_POST['tv'.$row['id'].'_prefix'.$tv];
    }
    
    return $tv;
  }

  public function get_tvtype_file($row)
  {
    return $_POST['tv'.$row['id']];
  }
    
  public function get_tvtype_default($row)
  {
    $tv = $_POST['tv'.$row['id']];
    if( !is_array($tv) ) {
      return $tv;
    }
    
    $to_implode = array();
    while( list( $value, $item) = each($tv)) {
      $to_implode[count($to_implode)] = $item;
    }
    return implode('||', $to_implode);
  }
  
  public function can_save_here()
  {
    if( $this->modx->config['use_udperms'] != 1) {
      return true;
    }
    
    include_once MODX_BASE_PATH . 'manager/processors/user_documents_permissions.class.php';
    $udperms = new udperms;
    $udperms->user = $this->get_user_id();
    $udperms->document = $this->fields['parent'];
    $udperms->role = $this->get_user_role();
    
    if( $udperms->checkPermissions() ) {
      return true;
    } else {
      $language = sprintf($this->_lang['access_permission_parent_denied'], $this->fields['id'], $this->fields['alias']);
      $this->web_alert_error($language, $this->fields['id'], 'can_save_here');
    }
  }
  
  public function save_document()
  {
    /* Need to do: use_udperms (maybe for new too. i think so) */
    switch($this->action_to_take)
    {
      case 'new':
        $this->new_document();
        break;
      case 'edit':
        /* Must make sure document already exists */
        $this->edit_document();
        break;
      default:
        header("Location: index.php?a=7");
        exit();
    }
  }
  
  public function new_document()
  {
    $this->invoke_event('OnBeforeDocFormSave', array('mode' => 'new', 'id' => ''));
    
    if( !$this->can_publish_document() ) {
      $this->fields['pub_date'] = 0;
      $this->fields['unpub_date'] = 0;
      $this->fields['published'] = 0;
    }
    
    $this->fields['publishedon'] = ($this->fields['published'] == 1) ? time() : 0;
    $this->fields['publishedby'] = ($this->fields['published'] == 1) ? $this->get_user_id() : 0;

    $this->fields['createdon'] = time();
    $this->fields['createdby'] = $this->get_user_id();
    
    unset($this->fields['id']);
    
    $insert_id = $this->modx->db->insert($this->fields, $this->table['site_content']);
    if( !$insert_id ) {
      $this->modx->manager->saveFormValues(27);
      echo 'An error occured while attempting to save the new document: ' . $this->modx->db->getLastError();
      exit;
    }
    
    if( !empty($this->tvs_to_save) ) {
      $this->insert_tvs($insert_id, $this->tvs_to_save);
    }
    
    $this->save_docgroups_for_new_doc($insert_id, $this->fields['parent']);
    
    $this->make_parent_folder($this->fields['parent']);
    
    $this->save_meta_keywords($insert_id);
    
    $this->invoke_event('OnDocFormSave', array('mode' => 'new', 'id' => $insert_id));
    
    $this->secure_document($insert_id);
    
    $this->syncsite();
    
    $this->redirect_or_stay($insert_id, $this->fields['parent']);
  }
  
  public function edit_document()
  {

    $this->existing_doc = $this->modx->db->makeArray($this->modx->db->select('*', $this->table['site_content'], "id='{$this->fields['id']}'"));
    
    if( count($this->existing_doc) > 1) {
      $this->e->setError(6);
      $this->e->dumpError();
    } elseif ( count($this->existing_doc) < 1 ) {
      $this->e->setError(7);
      $this->e->dumpError();      
    }

    if( $this->fields['id'] == $this->modx->config['site_start'] AND $this->fields['published'] == 0 ) {
      $language = 'Document is linked to site_start variable and cannot be unpublished!';
      $this->web_alert_error($language, $this->fields['id'], 'edit_document');    
    }
    
    if( $this->fields['id'] == $this->modx->config['site_start'] AND ($this->fields['pub_date'] != 0 OR $this->fields['unpub_date'] != 0) ) {
      $language = 'Document is linked to site_start variable and cannot have publish or unpublish dates set!';
      $this->web_alert_error($language, $this->fields['id'], 'edit_document');    
    }
    
    if( $this->fields['parent'] == $this->fields['id'] ) {
      $language = "Document can not be it's own parent!";
      $this->web_alert_error($language, $this->fields['id'], 'edit_document');    
    }
    
    /* BEGIN: I can remove this. because it's handled in the make_parent_folder method */
    $children_rs = $this->modx->db->select('COUNT(id)', $this->table['site_content'], "parent = '{$this->fields['id']}'");
    if( !$children_rs) {
      $language = "An error occured while attempting to find the document's children.";
      $this->web_alert_error($language, $this->fields['id'], 'edit_document');      
    }
    
    $child_count = $this->modx->db->getRow($children_rs);
    if( $child_count['COUNT(id)'] > 0 ) {
      $this->fields['isfolder'] == 1;
    }
    /* END: I can remove this. because it's handled in the make_parent_folder method */    
    
    if( !$this->has_permission('publish_document') ) {
      $this->fields['published']  = $this->existing_doc['published'];
      $this->fields['pub_date']   = $this->existing_doc['pub_date'];
      $this->fields['unpub_date'] = $this->existing_doc['unpub_date'];
    }
    
    if( $this->existing_doc['published'] == 0 AND $this->fields['published'] != 0 ) {
      $this->fields['publishedon'] = time();
      $this->fields['publishedby'] = $this->get_user_id();
    } elseif ( $this->existing_doc['published'] != 0 AND $this->fields['published'] == 0 ) {
      $this->fields['publishedon'] = 0;
      $this->fields['publishedby'] = 0;
    }
    
    $this->invoke_event('OnBeforeDocFormSave', array( 'mode' => 'upd', 'id' => $this->fields['id'] ));
    
    $result = $this->modx->db->update($this->fields, $this->table['site_content'], "id = '{$this->fields['id']}'");
    if( !$result) {
      echo "An error occured while attempting to save the edited document. The generated SQL is: <i> $sql </i>.";
    }

    $this->delete_tvs($this->fields['id'], $this->tvs_to_delete);
    $this->insert_tvs($this->fields['id'], $this->tvs_to_save);
    $this->update_tvs($this->fields['id'], $this->tvs_to_update);
    
    $this->edit_docgroups_for_doc($this->fields['id'], $this->fields['parent']);
    
    $this->make_parent_folder($this->fields['parent']);
    $this->update_old_parent_folder($this->existing_doc['parent']);

    $this->save_meta_keywords($this->fields['id']);
    
    $this->invoke_event('OnDocFormSave', array('mode' => 'upd', 'id' => $this->fields['id']));
    
    $this->secure_document($this->fields['id']);
    
    $this->syncsite();
    
    $this->redirect_or_stay($this->fields['id'], $this->fields['parent']);
  }
  
  public function has_permission($action)
  {
    return $this->modx->hasPermission($action);
  }
  
  public function update_old_parent_folder($parent_id)
  {
    if( $parent_id == 0) {
      return;
    }
    $parent_id = $this->modx->db->escape($parent_id);
    
    $rs = $this->modx->db->select('COUNT(id)', $this->table['site_content'], "parent = '$parent_id'");
    if( !$rs) {
      echo "An error occured while attempting to find the old parents' children.";
    }
    
		$row = $modx->db->getRow($rs);
		$limit = $row['COUNT(id)'];
		
		if( $limit != 0) {
		  return;
		}
		
    $fields = array('isfolder' => 0);
    if( !$rs = $this->modx->db->update($fields, $this->table['site_content'], "id = '$parent_id'")) {
      echo "An error occured while attempting to change the old parent to a regular document.";
    }
  }
  
  public function make_parent_folder($parent_id)
  {
    if( $parent_id == 0) {
      return;
    }
    $parent_id = $this->modx->db->escape($parent_id);
    
    $fields = array('isfolder' => 1);
    if( !$rs = $this->modx->db->update($fields, $this->table['site_content'], "id = '$parent_id'")) {
      echo "An error occured while attempting to change the document's parent to a folder.";
    }
  }
  
  public function edit_docgroups_for_doc($doc_id, $parent_id)
  {
    $results['docgrp_save_attempt'] = false;
    $doc_groups = $this->get_document_groups();
    
    if( $this->modx->config['use_udperms'] != 1 OR !is_array($doc_groups) ) {
      return;
    }
    
    $new_groups = array();
    foreach($doc_groups as $pair) {
      list($group, $link_id) = explode(',', $pair);
      $new_groups[$group] = $link_id;
    }
    
    /* Existing groups for document that user has access to change */
    $is_manager = (int) $this->has_permission('access_permissions');
    $is_web     = (int) $this->has_permission('web_access_permissions');
    
    $select = " SELECT groups.id, groups.document_group ";
    $from   = " FROM {$this->table['document_groups']} AS groups ";
    $join   = " LEFT JOIN {$this->table['documentgroup_names']} AS dgn ";
    $join  .= "   ON dgn.id = groups.document_group ";
    $where  = " WHERE ( ";
    $where .= "   ( 1 = '{$is_manager}' AND dgn.private_memgroup) OR ";
    $where .= "   ( 1 = {$is_web} AND dgn.private_webgroup ) ";
    $where .= " ) AND groups.document = {$this->fields['id']} ";
    
    $old = $this->modx->db->makeArray($this->modx->db->query($select.$from.$join.$where));
    $old_groups = array();
    foreach($old as $row) {
      $old_groups[$row['document_group']] = $row['id'];
    }
    /* Existing groups for document that user has access to change */
    
    $insertions = $deletions = array();
    
    foreach($new_groups as $group_id => $link_id) {
      if( isset($old_groups[$group_id]) ) {
        unset($old_groups[$group_id]);
        continue;
      } elseif ( $link_id == 'new') {
        $insertions[] = sprintf('(%s, %s)', (int)$group_id, (int)$doc_id);
      }
    }
    
    if( !empty($insertions) ) {
      $insert = " INSERT INTO {$this->table['document_groups']} (document_group, document) ";
      $values = " VALUES " . implode(',', $insertions);
      $saved  = $this->modx->db->query($insert.$values);
    }
    
    if( !empty($old_groups) ) {
      $delete = " DELETE FROM {$this->table['document_groups']} ";
      $where  = " WHERE id IN (" .implode(',', $old_groups). ")";
      $saved  = $this->modx->db->query($delete.$where);
    }
    
    /* Remove all permissions if document is public. Should Do This Earlier Then Return */
    if( isset($_POST['chkalldocs']) AND $_POST['chkalldocs'] == 'on' ) {
      $delete = " DELETE FROM {$this->table['document_groups']} ";
      $where  = " WHERE document = '{$this->fields['id']}'";
      $saved  = $this->modx->db->query($delete.$where);      
    }
    
    if( !$saved ) {
      $this->modx->manager->saveFormValues(27);
			echo "An error occured while saving document groups.";
			exit;
    }
  }
  
  public function save_docgroups_for_new_doc($doc_id, $parent_id)
  {
    $results['docgrp_save_attempt'] = false;
    $doc_groups = $this->get_document_groups();
    if( $this->modx->config['use_udperms'] == 1 AND is_array($doc_groups) ) {
      $results = $this->udperms_and_doc_groups_array($doc_id, $doc_groups);
    } else {
      $is_manager = $this->modx->hasPermission('access_permissions');
      $is_web     = $this->modx->hasPermission('web_access_permissions');
      
      if( $this->modx->config['use_udperms'] == 1 AND $parent_id != 0 AND ($is_manager OR $is_web) ) {
        $sql  = "INSERT INTO {$this->table['document_groups']} (document_group, document) ";
        $sql .= " SELECT document_group, {$doc_id} FROM {$this->table['document_groups']} WHERE document == $parent_id ";
        $results['saved'] = $this->modx->db->query($sql);
        $results['docgrp_save_attempt'] = true;
      }
    }
    
    if($results['docgrp_save_attempt'] AND !$results['saved'] ) {
      $this->modx->manager->saveFormValues(27);
			echo 'An error occured while attempting to add the document to a document_group.';
			exit;
    }
  }
  
  public function udperms_and_doc_groups_array($doc_id, $doc_groups)
  {
    $saved = false;
    $new_groups = array();
    if( !empty($doc_groups) ) {
      foreach($doc_groups as $pair) {
        list($group) = explode(',', $pair);
        $new_groups[] = sprintf('(%s, %s)', (int)$group, (int)$doc_id);
      }
      $saved = true;
      if( !empty($new_groups) ) {
        $imploded_groups = implode(',', $new_groups);
        $sql = "INSERT INTO {$this->table['document_groups']} (document_group, document) VALUES {$imploded_groups}";
        $saved = $this->modx->db->query($sql) ? $saved : false;
        $docgrp_save_attempt = true;
      }
    }
    
    return array('saved' => $saved, 'docgrp_save_attempt' => $docgrp_save_attempt);
  }

  public function secure_document($doc_id)
  {
		// secure web documents - flag as private
		$this->secureWebDocument($doc_id);

		// secure manager documents - flag as private
		$this->secureMgrDocument($doc_id);
  }
  
  public function syncsite()
  {
    $do_sync = isset($_POST['syncsite']) ? $_POST['syncsite'] : 0;
		if ($do_sync == 1) {
			// empty cache
      include_once MODX_BASE_PATH.'manager/processors/cache_sync.class.processor.php';
			$sync = new synccache();
			$sync->setCachepath(MODX_BASE_PATH.'assets/cache/');
			$sync->setReport(false);
			$sync->emptyCache();
		}
  }
  
  public function redirect_or_stay($doc_id, $parent_id)
  {
    return;
    
    if( $_POST['stay'] == '') {
      $header = "Location: index.php?r=1&id={$doc_id}&a=7&dv=1";
      header($header);
      exit();
    }
    
    if( $_POST['mode'] == 72) {
      $key = 72;
      $a = ($_POST['stay'] == 2) ? "27&id={$doc_id}" : "72&pid={$parent_id}";
    } elseif ($_POST['mode'] == 4) {
      $a = ($_POST['stay'] == 2) ? "27&id={$doc_id}" : "4&pid={$parent_id}";    
    }
    $header = "Location: index.php?a=" . $a . "&r=1&stay=" . $_POST['stay'];
    header($header);
    exit();
    
  }
  
  public function can_publish_document()
  {
    if( $this->modx->hasPermission('publish_document') ) {
      return true;
    }
    
    return false;
  }
  
  public function invoke_event($event_name, $options = array())
  {
    if( method_exists($this, $event_name)) {
      $this->$method_name($options);
    }
    
    $this->modx->invokeEvent($event_name, $options);
  }


  public function web_alert_error($language, $docid = '', $method)
  {
    if($this->action_to_take == 'edit') {
      $this->modx->manager->saveFormValues(27);
      $url = "index.php?a=27&id=".$docid;
    } else {
      $this->modx->manager->saveFormValues(4);
      $url = "index.php?a=4";
    }
    
    if( IN_MANAGER_MODE == 'true') {
      include_once MODX_BASE_PATH.'/manager/includes/header.inc.php';
      $this->modx->webAlert($language, $url);
      include_once MODX_BASE_PATH.'/manager/includes/footer.inc.php';
      exit();
    } else {
      echo 'Error: '.$language . '('.$method.')';
      exit();
    }
  }
    
  public function validate_fields($array = array()) 
  {
    $validation = array();
    foreach($array as $key => $value) {
      $method_name = 'validate_field_' . strtolower($key);
      if( method_exists($this, $method_name)) {
        $validation[$key] = $this->$method_name($value);
      } else {
        $validation[$key] = true;
      }
    }
    $this->validation = $validation;
  }
  
  public function save_meta_keywords($doc_id)
  {
    $keywords = $_POST['keywords'];
    $metatags = $_POST['metatags'];
    
  	if ($this->modx->hasPermission('edit_doc_metatags')) {
  		// keywords - remove old keywords first
  		$this->modx->db->delete($this->table['keyword_xref'], "content_id = '{$doc_id}'");
  		for ($i = 0; $i < count($keywords); $i++) {
  			$kwid = $keywords[$i];
  			$flds = array (
  				'content_id' => $id,
  				'keyword_id' => $kwid
  			);
  			$this->modx->db->insert($flds, $this->table['keyword_xref']);
  		}
  		// meta tags - remove old tags first
  		$this->modx->db->delete($this->table['site_content_metatags'], "content_id = '{$doc_id}'");
  		for ($i = 0; $i < count($metatags); $i++) {
  			$kwid = $metatags[$i];
  			$flds = array (
  				'content_id' => $id,
  				'metatag_id' => $kwid
  			);
  			$this->modx->db->insert($flds, $this->table['site_content_metatags']);
  		}
  		$flds = array (
  			'haskeywords' => (count($keywords) ? 1 : 0),
  			'hasmetatags' => (count($metatags) ? 1 : 0)
  		);
  		$this->modx->db->update($flds, $tbl_site_content, "id = '{$doc_id}'");
  	}
  }

  public function secureWebDocument($docid='') {
  	
    $sql = "UPDATE ".$this->modx->getFullTableName("site_content")." SET privateweb = 0 WHERE ".($docid > 0 ? "id='$docid'":"privateweb = 1");
  	$this->modx->db->query($sql);
  	
  	$sql =  "SELECT DISTINCT sc.id 
  			 FROM ".$this->modx->getFullTableName("site_content")." sc
  			 LEFT JOIN ".$this->modx->getFullTableName("document_groups")." dg ON dg.document = sc.id
  			 LEFT JOIN ".$this->modx->getFullTableName("webgroup_access")." wga ON wga.documentgroup = dg.document_group
  			 WHERE ".($docid>0 ? " sc.id='$docid' AND ":"")."wga.id>0";
  	$ids = $this->modx->db->getColumn("id",$sql);
  	if(count($ids)>0) {
  		$this->modx->db->query("UPDATE ".$modx->getFullTableName("site_content")." SET privateweb = 1 WHERE id IN (".implode(", ",$ids).")");	
  	}
  }

  public function secureMgrDocument($docid='') {
    
    $sql = "UPDATE ".$this->modx->getFullTableName("site_content")." SET privatemgr = 0 WHERE ".($docid>0 ? "id='$docid'":"privatemgr = 1");
  	$this->modx->db->query($sql);
  	
  	$sql =  "SELECT DISTINCT sc.id 
  			 FROM ".$this->modx->getFullTableName("site_content")." sc
  			 LEFT JOIN ".$this->modx->getFullTableName("document_groups")." dg ON dg.document = sc.id
  			 LEFT JOIN ".$this->modx->getFullTableName("membergroup_access")." mga ON mga.documentgroup = dg.document_group
  			 WHERE ".($docid>0 ? " sc.id='$docid' AND ":"")."mga.id>0";
  	$ids = $this->modx->db->getColumn("id",$sql);
  	if(count($ids)>0) {
  		$this->modx->db->query("UPDATE ".$this->modx->getFullTableName("site_content")." SET privatemgr = 1 WHERE id IN (".implode(", ",$ids).")");	
  	}
  }
}