<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use ExpressionEngine\Service\Addon\Installer;

class Lettori_ext extends Installer
{
    public $settings = [];
    public $version = "1.0.0";
    public $member_ch_name = 'membro';
    public $scaffale_ch_name = 'scaffale';

    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    public function activate_extension()
    {
        $data = [
            [
                'class'     => __CLASS__,
                'method'    => 'after_member_insert',
                'hook'      => 'after_member_insert',
                'settings'  => serialize($this->settings),
                'priority'  => 10,
                'version'   => $this->version,
                'enabled'   => 'y'
            ],
            [
                'class'     => __CLASS__,
                'method'    => 'after_member_update',
                'hook'      => 'after_member_update',
                'settings'  => serialize($this->settings),
                'priority'  => 10,
                'version'   => $this->version,
                'enabled'   => 'y'
            ],
            [
                'class'     => __CLASS__,
                'method'    => 'after_member_delete',
                'hook'      => 'after_member_delete',
                'settings'  => serialize($this->settings),
                'priority'  => 10,
                'version'   => $this->version,
                'enabled'   => 'y'
            ]

        ];

        foreach ($data as $hook) {
            ee()->db->insert('extensions', $hook);
        }
        
        $site_id = ee()->config->item('site_id');
        
        //import channel set
        //$set_path = PATH_THIRD . 'lettori/ChannelSet';
        //$set = ee('ChannelSet')->importDir($set_path);
        //$set->setSiteId($site_id);
        //$result = $set->validate();
        
        //if ($result->isValid()) {
            //$set->save();
            //$set->cleanUpSourceFiles();
		//} else {
			//$erroracci = $result->getRecoverableErrors();
			//$this->_log_object($erroracci);
		//}
		
        // create groups and channels
        $membro_chn_id = $this->_make_channel($this->member_ch_name);
		$scaffale_chn_id = $this->_make_channel($this->scaffale_ch_name);
        $membro_grp_id = $this->_make_field_group($this->member_ch_name);
        $scaffale_grp_id = $this->_make_field_group($this->scaffale_ch_name);
        $this->_add_channel_to_group($membro_chn_id,$membro_grp_id);
        $this->_add_channel_to_group($scaffale_chn_id,$scaffale_grp_id);
        // fields
        
		// crea field e assegna a gruppi di fields.
		
		/*
		 * membro
		*/
		
		//identificativo membro - number input
		$membroid_properties = array(
				'field_label' => 'identificativo membro lettori',
				'field_name' => 'IDmembro_lettori',
				'field_type' => 'number',
				'field_list_items' => ''
		);		
		$membroid_settings = array(
				'field_min_value' => 1,
				'field_max_value' => null, //max user ID
				'field_step' => null,
				'datalist_items' => null,
				'field_content_type' => 'integer'
		);
		$membroid_field = $this->_make_field($membroid_properties,$membroid_settings);
		$this->_add_field_to_group($membroid_field,$membro_grp_id);
		
		//username - text
		$membroname_properties = array(
				'field_label' => 'username membro lettori',
				'field_name' => 'username_membro_lettori',
				'field_type' => 'text',
				'field_list_items' => ''
		);		
		$membroname_settings = array(
				'field_maxl' => 75,
				'field_content_type' => 'all'
		);
		$membroname_field = $this->_make_field($membroname_properties,$membroname_settings);
		$this->_add_field_to_group($membroname_field,$membro_grp_id);
		
		//scaffali - relationship del membro con gli scaffali personali
		//all properties are required
		$scaffali_properties = array(
				'field_label' => 'scaffali',
				'field_name' => 'scaffali',
				'field_type' => 'relationship',
				'field_list_items' => ''
		);
		
		//all settings are optional
		$scaffali_settings = array(
			'channels' => array($scaffale_chn_id), // if missing relates to all channels
			'expired' => true, // if missing defaults to false
			'future' => true, // if missing defaults to false
			'categories' => array(), // if missing or empty allows relationships with all categories
			'authors' => array(), // if missing or empty allows relationships with all authors
			'statuses' => array('open'), // if missing or empty allows relationships with all statuses
			'order_field' => 'entry_date', // default is by title
			'order_dir' => 'desc', // default is ascending
			'display_entry_id' => null, //default is false
			'allow_multiple' => true, // default is true
			'rel_min' => 0,  //Minimum number of related entries
			'rel_max' => null  // Maximum number of related entries
		);
		
		// cfr. https://github.com/aleppax/ExpressionEngine-User-Guide/blob/7.dev/docs/development/models/channel-field.md
		$scaffali_field = $this->_make_field($scaffali_properties,$scaffali_settings);
		$this->_add_field_to_group($scaffali_field,$membro_grp_id);
		
		
		//
		/*
		 * scaffale(pubblico-toggle, contatti-toggle, taccuino-richtexteditor, titoli-relationship, uso-radiobutton)
		*/
		
		// pubblico - toggle
		$scaffale_pubblico_properties = array(
				'field_label' => 'scaffale pubblico',
				'field_name' => 'scaffale_pubblico',
				'field_type' => 'toggle',
				'field_list_items' => ''
		);		
		$scaffale_pubblico_settings = array(
				'field_default_value' => 0, // set default value of the field (0) "not pubblic"
		);
		$scaffale_pubblico_field = $this->_make_field($scaffale_pubblico_properties,$scaffale_pubblico_settings);
		$this->_add_field_to_group($scaffale_pubblico_field,$scaffale_grp_id);
		// contatti - toggle
		$scaffale_contatti_properties = array(
				'field_label' => 'scaffale contatti',
				'field_name' => 'scaffale_contatti',
				'field_type' => 'toggle',
				'field_list_items' => ''
		);		
		$scaffale_contatti_settings = array(
				'field_default_value' => 0, // set default value of the field (0) "not visible to contacts"
		);
		$scaffale_contatti_field = $this->_make_field($scaffale_contatti_properties,$scaffale_contatti_settings);
		$this->_add_field_to_group($scaffale_contatti_field,$scaffale_grp_id);
		//taccuino - richtexteditor
		$scaffale_taccuino_properties = array(
				'field_label' => 'taccuino scaffale',
				'field_name' => 'taccuino_scaffale',
				'field_type' => 'rte',
				'field_list_items' => ''
		);
		$scaffale_taccuino_settings = array(
				'toolset_id' => 3,
				'defer' => 'n',
				'db_column_type' => 'text',
				'field_wide' => 1,
				'field_fmt' => 'none',
				'field_show_fmt' => 'n'
		);
		$scaffale_taccuino_field = $this->_make_field($scaffale_taccuino_properties,$scaffale_taccuino_settings);
		$this->_add_field_to_group($scaffale_taccuino_field,$scaffale_grp_id);
		
		//uso - radiobuttons
		$scaffale_uso_properties = array(
				'field_label' => 'uso scaffale',
				'field_name' => 'uso_scaffale',
				'field_type' => 'radio',
				'field_list_items' => 'letti\nposseduti\nche voglio leggere\nche sto leggendo\nabbandonati\nscaffale personale'
		);
		
		
		$scaffale_uso_field = $this->_make_field($scaffale_uso_properties);
		$this->_add_field_to_group($scaffale_uso_field,$scaffale_grp_id);

		// set settings[channels] of the field to an array containing the channels ids used in the relationship field
		// in this case the relationship is with channels containing works, books or items.
		// nel caso specifico la relationship punta, per ora, solo al canale chiamato "opera" 
		$opera_channel_object = ee('Model')
                ->get('Channel')
                ->filter('channel_name', 'opera')
                ->first();
        if (empty($opera_channel_object)) {
			file_put_contents(__DIR__."/log.txt", "missing opera channel. \n\r", FILE_APPEND);
			return;
		}
		$opera_ID = $opera_channel_object->channel_id;

		$relation_titoli_custom_settings = Array(
			'channels' => Array
				(
				$opera_ID
				),
		    'expired' => null,
		    'future' => null,
		    'categories' => Array
		        (
		        ),
		
		    'authors' => Array
		        (
		        ),
		
		    'statuses' => Array
		        (
		        ),
		    'limit' => 100,
		    'order_field' => 'title',
		    'order_dir' => 'asc',
		    'display_entry_id' => null,
		    'allow_multiple' => 1,
		    'rel_min' => 0,
		    'rel_max' => null
		);
		$scaffale_titoli_field = $this->_make_field('titoli lettori','relationship',array($scaffale_field_group->group_id),$relation_titoli_custom_settings);
		/**/

		// ------popola i membri------
		// dai i permessi al role 'members' NOTA: non basta, verificare quali permessi deve avere per creare una entry, deve avere accesso al CP?
		$role_members = ee('Model')->get('Role')->filter('name','Members')->first();
		$perm1 = 'can_create_entries_channel_id_'.$membro_chn_id;
		$perm2 = 'can_edit_self_entries_channel_id_'.$membro_chn_id;
		
		$p1_member = ee('Model')->make('Permission', [
			'site_id' => $site_id,
			'permission' => $perm1
		]);
		$p2_member = ee('Model')->make('Permission', [
			'site_id' => $site_id,
			'permission' => $perm2
		]);
		$role_members->Permissions->getAssociation()->add($p1_member);
		$role_members->Permissions->getAssociation()->add($p2_member);		
		
		$role_result = $role_members->validate();
		if ($role_result->isValid())
			{
			  $role_members->save();
			}
		// reperisci elenco dei membri
		$membri = ee('Model')->get('Member')->all();
		// aggiungi record, autore è il membro stesso, nel channel 'membro' con member_id (IDmembro_lettori) e username ()
		foreach ($membri as $unmembro) {
			if ($unmembro->in_authorlist == 'n') continue; //esclude anche il super admin che di default non è in authorlist.
			$this->_add_member($unmembro);
		}		
		
		//file_put_contents(__DIR__."/log.txt", "updated extension. \n\r", FILE_APPEND);

    }

    public function disable_extension()
    {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');
        $membro_chn_id = $this->_get_channel($this->member_ch_name)->channel_id;
		$perm1 = 'can_create_entries_channel_id_'.$membro_chn_id;
		$perm2 = 'can_edit_self_entries_channel_id_'.$membro_chn_id;        
        $this->_delete_channel($this->member_ch_name);
        $this->_delete_channel($this->scaffale_ch_name);
        $this->_delete_field_group($this->member_ch_name);
        $this->_delete_field_group($this->scaffale_ch_name);
        $this->_delete_field('IDmembro_lettori');
        $this->_delete_field('username_membro_lettori');
        $this->_delete_field('scaffale_pubblico');
        $this->_delete_field('scaffale_contatti');
        $this->_delete_field('taccuino_scaffale');
        $this->_delete_field('uso_scaffale');
        $this->_delete_permission($perm1);
        $this->_delete_permission($perm2);
        return true;
    }

    public function update_extension($current = '')
    {
        return true;
    }


    public function after_member_insert()
    {
        // Make magic, my friend
    }
    
    public function after_member_update($membro, $valori, $modificato)
    {
        //foreach ($modificato as $field => $value) {
            //if (!empty($valori) && !in_array($field, ['total_entries', 'last_entry_date'])) {
                //$auto_flush = ee('gb_member_select:MemberSelectService')->getSettings()['auto_flush'];
                //if ($auto_flush == 'y') {
                    //ee('gb_member_select:MemberSelectService')->flushCache();
                //} else {
                    //ee('gb_member_select:MemberSelectService')->setOutOfSync($roles);
                //}
                //break;
            //}
        //}
    }
        
    public function after_member_delete()
    {
        // Make magic, my friend
    }
    
    private function _make_channel_entry($entry_data)
    {
		$entry = ee('Model')->make('ChannelEntry');
		$entry->set($entry_data);
		$result = $entry->validate();
		if ($result->isValid()) {
			$entry->save();
		} else {
			$errori = $result->getAllErrors();
			$this->_log_object($errori);
		}
	}
	
	private function _add_member($amember)
	{
		$membro_chn_id = $this->_get_channel($this->member_ch_name)->channel_id;
		$membroid_field = $this->_get_field('IDmembro_lettori')->field_id;
		$membroname_field = $this->_get_field('username_membro_lettori')->field_id;
		// (NOTA che se lo si aggiunge dopo non viene automaticamente 
		// creata la entry, bisognerà prevederlo nell'hook after_member_update)
		//crea channel entry per ogni membro
		$entry_data1 = array (
			'author_id' => $amember->member_id, // the member is the author NOTA: se è in_authorlist (y) la validazione ha successo, 
			'channel_id' => $membro_chn_id,
			'title' => 'lettore '.$amember->username,
			'url_title' => 'lettore-'.$amember->username,
			'status' => 'open',
			'entry_date' => ee()->localize->now,
		);
		// imposta i custom fields identificativo membro lettori, username membro lettori
		$entry_data1['field_id_'.$membroid_field] = $amember->member_id;
		$entry_data1['field_id_'.$membroname_field] = $amember->username;
		$this->_make_channel_entry($entry_data1);
		
		// TODO: crea liste modificabili (libri letti-posseduti-voglio leggere-sto leggendo-abbandonati) con rispettivi privilegi
		
	}
    
    private function _make_field($custom_properties, $custom_settings = [])
    {
		$field = ee('Model')->make('ChannelField');
		
		if (!is_array($custom_properties)) {
			// error, missing properties, exit
			return false;
		}
		// do some checks, the field requires at least some properties
		foreach (['field_name','field_label','field_type','field_list_items'] as $el) {
			if (!array_key_exists($el,$custom_properties)) return false;
		}

		// Set required site wide field.
		$site_id = ee()->config->item('site_id');
		$field->site_id     = $site_id;
		// field_order increment the last field order number of fields 
		// belonging to this site
		// if there are no fields yet for this site, the query would result in an error 
		$sitefields = ee('Model')->get('ChannelField')->filter('site_id',$site_id);
		if ($sitefields->count() == 0) {
			$ordernumber = 1;
		} else {
			$ordernumber = 1 + $sitefields->order('field_order', 'DESC')->first()->field_order;
		}
		$custom_properties['field_order'] = $ordernumber;
		// setting custom properties
		foreach ($custom_properties as $p => $p_valore) {
			$field->setProperty($p, $p_valore);
		}
		// field settings
		$changing_settings = $field->getSettingsValues()['field_settings'];
		foreach ($custom_settings as $s => $s_value) {
			$changing_settings[$s] = $s_value;
		}
		$field->setProperty('field_settings', $changing_settings);
		// Validate and Save.
		$result = $field->validate();
		
		if ($result->isValid())
		{
		  $field->save();
		  //return $field->getId();
		  return $field->field_id;
		}
	}
	
	private function _make_field_group($field_group_name)
	{
		$group = ee('Model')->make('ChannelFieldGroup');
		// Set Required Fields
		$group->site_id     = ee()->config->item('site_id');
		$group->group_name  = $field_group_name;
		
		// Validate and Save.
		$result = $group->validate();
		
		if ($result->isValid())
		{
		  $group->save();
		  return $group->group_id;
		}
	}
	
	private function _add_field_to_group($field_id,$group_id)
	{
		$_grp = ee('Model')->get('ChannelFieldGroup', $group_id)->first();
		//don't overwrite exixting fields
		$existing_fields = $_grp->ChannelFields->pluck('field_id');
		array_push($existing_fields,$field_id);
		$_grp->ChannelFields = ee('Model')->get('ChannelField', $existing_fields)->all();
		$this->_validate_and_save($_grp);
	}
	
	private function _add_channel_to_group($_chn_id,$_grp_id)
	{
		$group = ee('Model')->get('ChannelFieldGroup', $_grp_id)->first();
		$existing_channels = $group->Channels->pluck('channel_id');
		array_push($existing_channels,$_chn_id);
		$group->Channels = ee('Model')->get('Channel', $existing_channels)->all();
		$this->_validate_and_save($group);
	}
	
	private function _validate_and_save($element)
	{
		$result = $element->validate();
		//print_r($result->getAllErrors());

		if ($result->isValid())  {
		        $element->save();
		        return True;
		}
	}
	
    private function _make_channel($channel_name)
    {
		$canale = ee('Model')->make('Channel', array(
            'channel_title' => $channel_name,
            'channel_name' => $channel_name,
            'FieldGroups' => null,
            'CustomFields' => null,
            'Statuses' => null,
            'title_field_label' => 'Title'
        ));
        $canale->site_id = ee()->config->item('site_id');
        $result = $canale->validate();
        if ($result->isValid()) {
                $canale->save();
                return $canale->channel_id;
        }
	}
	
	private function _get_channel($chname)
	{
		return ee('Model')->get('Channel')->filter('channel_name', $chname)->first();
	}
	
	private function _get_field($fldname)
	{
		return ee('Model')->get('ChannelField')->filter('field_name',$fldname)->first();
	}

	private function _get_field_group($fldname)
	{
		return ee('Model')->get('ChannelFieldGroup')->filter('group_name',$fldname)->first();
	}
		
	private function _delete_channel($channel_name)
	{
		$_name = strtolower(str_replace(" ","_",$channel_name));
		$cnl = $this->_get_channel($_name);
        if (!is_null($cnl)) {
			$cnl->delete();
		}
	}

	private function _delete_field_group($group_name)
	{
		$_name = strtolower(str_replace(" ","_",$group_name));
		$grp = $this->_get_field_group($_name);
        if (!is_null($grp)) {
			$grp->delete();
		}
	}
	
	private function _delete_field($field_name)
	{
		$_name = strtolower(str_replace(" ","_",$field_name));
		$fld = $this->_get_field($_name);
        if (!is_null($fld)) {
			$fld->delete();
		}
	}
	
	private function _delete_permission($perm)
	{
		ee('Model')->get('Permission')
            ->filter('permission', 'IN', $perm)
            ->filter('site_id', ee()->config->item('site_id'))
            ->delete();
	}
	
	private function _log_object($obj)
	{
		ob_start();
		var_dump($obj);
		$outStringVar = ob_get_contents() ;
		file_put_contents(__DIR__."/log.txt", "Printing object properties \n\r".$outStringVar, FILE_APPEND);
	}

}
