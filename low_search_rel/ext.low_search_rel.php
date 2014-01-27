<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Search Relationships
 *
 * @package   Low Search Relationships
 * @author    Scott-David Jones <sdjones1985@gmail.com>
 * @copyright Copyright (c) 2014 AutumnDev 
 */
class Low_search_rel_ext {

    var $name           = "Low Search Relationships";
    var $version        = 1.0;
    var $description    = "Added functionality to Low Search in order to seach relationship titles by keyword";
    var $docs_url       = 'www.autumndev.co.uk';
    var $settings_exist = 'n';
    var $globalVars;
    // --------------------------------------------------------------------

    /** Activate Extension
     */
    function activate_extension()
    {
        // -------------------------------------------
        //  Add the row to exp_extensions
        // -------------------------------------------

        ee()->db->insert('extensions', array(
            'class'    => __CLASS__,
            'method'   => 'search_relationships',
            'hook'     => 'low_search_update_index',
            'settings' => '',
            'priority' => 10,
            'version'  => $this->version,
            'enabled'  => 'y'
        ));
    }

    /**
     * Update Extension
     */
    function update_extension($current = '')
    {
        if ($current == '' OR $current == $this->version)
        {
            return false;
        }
       
        ee()->db->where('class', __CLASS__);
        ee()->db->update(
            'extensions',
            array('version' => $this->version)
        );
    }

    /**
     * Disable Extension
     */
    function disable_extension()
    {
        // -------------------------------------------
        //  Remove the row from exp_extensions
        // -------------------------------------------

        ee()->db->where('class', __CLASS__)
            ->delete('extensions');
    }

    /**
     * seach parent child relationships for keywords
     *
     * searches entry id for relationships and adds the 
     * relationship title to the index_text ready for low search
     * to search
     *  
     * @param  array $data  low search index data
     * @param  array $entry entry data
     * 
     * @return array modified data
     */
    function search_relationships($data, $entry){

        $site_id = ee()->config->item('site_id');
        

        //get channel based on collection channel
        ee()->db->select('c2.title')
            ->from('channel_titles c1')
            ->join('relationships r', "r.parent_id = c1.entry_id")
            ->join('channel_titles c2', 'c2.entry_id = r.child_id')
            ->where('c1.site_id', $site_id)
            ->where('c1.entry_id', $data['entry_id']);

        //get results
        $results = ee()->db->get();
        //return if no results
        if ($results->num_rows == 0)
        {
            return $data;
        }
        
        //for each result add tot he index_text param
        foreach ($results->result_array() as $key => $value) 
        {
            $data['index_text'] .= ' '.$value['title'].' |';
            
        }

        return $data;
    }

}