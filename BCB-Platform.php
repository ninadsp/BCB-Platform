<?php
/*
  Plugin Name: Barcamp Bangalore Platform
  Plugin URI: http://barcampbangalore.org.com/platform
  Description: Provides Scheduling capabilities and Android App compatibility for Barcamp Bangalore Platform
  Version: 0.1
  Author: Aman Manglik
  Author URI: http://amanmanglik.com
  License: GPL2
 */

global $bcbp_db_version, $bcbp_db_prefix;
$bcbp_db_version = '0.1';
$bcbp_db_prefix = 'bcbp_';

register_activation_hook(__FILE__, 'bcbp_plugin_activate');
register_deactivation_hook(__FILE__, 'bcbp_plugin_deactivate');
register_uninstall_hook(__FILE__, 'bcb_plugin_uninstall');


add_action('admin_menu', 'bcbp_add_admin_menu');

/**
* Executed everytime the administrator activates this plugin from the Plugin menu
* Prefills slot and session information to wp_options as per BCB's need
* Also creates/upgrades the tables required for storing schedule data
* As of now a lazy hack for resetting data while testing
*
* @TODO: Remove BCB specific data from this plugin. Not the right place
*/
function bcbp_plugin_activate()
{
    global $wpdb, $bcbp_db_version, $bcbp_db_prefix;

    add_option("bcbp_num_tracks", 6);
    add_option("bcbp_num_slots", 11);
    add_option("bcbp_category", 2);
    add_option("bcbp_android_version", 0);

    $TRACKS = array('Asteroids', 'Battleship', 'Contra', 'Diablo', 'Everquest', 'Fable');
    add_option("bcbp_trackdata", $TRACKS );

    $SLOTS = array();

    $SLOTS[] = array("type" => "fixed", "start" => "800", "end" => "900", "display_string" => "8:00AM - 9:00AM", "name" => "Registration");
    $SLOTS[] = array("type" => "fixed", "start" => "900", "end" => "930", "display_string" => "9:00AM - 9:30AM", "name" => "Introduction");
    $SLOTS[] = array("type" => "session", "start" => "930", "end" => "1015", "display_string" => "9:30AM - 10:15AM", "name" => "Slot 1");
    $SLOTS[] = array("type" => "session", "start" => "1030", "end" => "1115", "display_string" => "10:30AM - 11:15AM", "name" => "Slot 2");
    $SLOTS[] = array("type" => "session", "start" => "1130", "end" => "1215", "display_string" => "11:30AM - 12:15AM", "name" => "Slot 3");
    $SLOTS[] = array("type" => "fixed", "start" => "1230", "end" => "1330", "display_string" => "12:30AM - 13:30AM", "name" => "Lunch");
    $SLOTS[] = array("type" => "fixed", "start" => "1330", "end" => "1430", "display_string" => "1:30PM - 2:30PM", "name" => "Techlash");
    $SLOTS[] = array("type" => "session", "start" => "1430", "end" => "1515", "display_string" => "2:30PM - 3:15PM", "name" => "Slot 4");
    $SLOTS[] = array("type" => "session", "start" => "1530", "end" => "1615", "display_string" => "3:30PM - 4:15PM", "name" => "Slot 5");
    $SLOTS[] = array("type" => "session", "start" => "1630", "end" => "1715", "display_string" => "4:30PM - 5:15PM", "name" => "Slot 6");
    $SLOTS[] = array("type" => "fixed", "start" => "1730", "end" => "1815", "display_string" => "5:30PM - 6:15PM", "name" => "Feedback");
    
    
    add_option("bcbp_slotdata", $SLOTS);

    $installed_version = get_option("bcbp_db_version");

    if( $installed_version != $bcbp_db_version ) {
            $selected_sessions_sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix.$bcbp_db_prefix."selected_sessions (
                id int(3) NOT NULL AUTO_INCREMENT,
                post_id int(10) NOT NULL,
                author varchar(40) NOT NULL,
                post_title varchar(1000) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY unique_post (post_id)
            );";
            $session_user_mapping_sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix.$bcbp_db_prefix."session_user_mapping (
                session int(11) NOT NULL,
                user int(11) NOT NULL,
                UNIQUE KEY unique_mapping (session, user)
            );";
            $generated_schedule_sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix.$bcbp_db_prefix."generated_schedule (
                timeslot int(11) NOT NULL,
                track int(11) NOT NULL,
                session int(11) NOT NULL
            );";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($selected_sessions_sql);
            dbDelta($session_user_mapping_sql);
            dbDelta($generated_schedule_sql);

            update_option("bcbp_db_version", $bcbp_db_version);
    }
    
}

/**
* Executed every time the administrator de-activates the plugin from the Plugin menu
* As of now, deletes most of the options from wp_options
*
* @TODO: Do we need to delete these options here?
*/
function bcbp_plugin_deactivate()
{
    
    delete_option("bcbp_num_tracks");
    delete_option("bcbp_num_slots");
    delete_option("bcbp_category");
    delete_option("bcbp_trackdata");
    delete_option("bcbp_slotdata");
    delete_option("bcbp_android_version");
    
}


/**
* Executed when the administrator deletes the plugin from the Plugin menu
* Removes all tables used by the plugin, as well as some metadata from wp_options
*
* @TODO: Test!
*/
function bcbp_plugin_uninstall()
{
    global $wpdb, $bcbp_db_version, $bcbp_db_prefix;

    $selected_sessions_sql = "DROP TABLE IF EXISTS ".$wpdb->prefix.$bcbp_db_prefix."selected_sessions;";
    $session_user_mapping_sql = "DROP TABLE IF EXISTS ".$wpdb->prefix.$bcbp_db_prefix."session_user_mapping;";
    $generated_schedule_sql = "DROP TABLE IF EXISTS ".$wpdb->prefix.$bcbp_db_prefix."generated_schedule;";

    $wpdb->query($selected_sessions_sql);
    $wpdb->query($session_user_mapping_sql);
    $wpdb->query($generated_schedule_sql);
    delete_option("bcbp_db_version");
}


/**
* Add a link to the Plugin Admin page to the WP sidebar/menu
*/
function bcbp_add_admin_menu()
{
    add_menu_page('BCB Platform Administration', 'BCB Platform Admin', 'manage_options', 'bcbp_admin', 'bcbp_admin_content_callback');
}

/**
* Render the Admin page for the plugin
* This is the meat yo!
*/
function bcbp_admin_content_callback()
{
    
    $NUM_TRACKS = get_option("bcbp_num_tracks");
    $NUM_SLOTS = get_option("bcbp_num_slots");
    $CATEGORY = get_option("bcbp_category");
    
    $TRACKS = get_option("bcbp_trackdata");
    $SLOTS = get_option("bcbp_slotdata");
    
    // Do we need to store the current phase/tab choice in an option and detect it from there?
    $bcbp_tab_choice = isset($_REQUEST['bcbp_tab_choice']) ? $_REQUEST['bcbp_tab_choice'] : 'setup';        
    ?>
    
    <div class="wrap">
    <h2>BCB Platform Settings</h2>
    
    <h2 id="bcbp_admin_tab_header" class="nav-tab-wrapper">
        <a class="nav-tab <?php echo ($bcbp_tab_choice == "setup") ? "nav-tab-active" : "" ; ?>" href="?page=bcbp_admin&bcbp_tab_choice=setup">Setup</a>
        <a class="nav-tab <?php echo ($bcbp_tab_choice == "schedule") ? "nav-tab-active" : "" ; ?>" href="?page=bcbp_admin&bcbp_tab_choice=schedule">Schedule</a>
        <a class="nav-tab <?php echo ($bcbp_tab_choice == "manual") ? "nav-tab-active" : "" ; ?>" href="?page=bcbp_admin&bcbp_tab_choice=manual">Manual</a>
        <a class="nav-tab <?php echo ($bcbp_tab_choice == "publish") ? "nav-tab-active" : "" ; ?>" href="?page=bcbp_admin&bcbp_tab_choice=publish">Publish</a>
    </h2>
    
    <?php 


        switch ($bcbp_tab_choice) {
            case 'setup':

            if(isset($_REQUEST['submit'])) {

                // Validate and save options POSTed
                $NUM_TRACKS = (int) stripslashes($_REQUEST['num_tracks']);
                $NUM_SLOTS = (int) stripslashes($_REQUEST['num_slots']);
                $CATEGORY = (int) stripslashes($_REQUEST['category']);

                $TRACKS = array();
                for($i = 0; $i < $NUM_TRACKS; $i++) {
                    $TRACKS[$i] = stripslashes($_REQUEST['track'][$i]);
                }

                $SLOTS = array();
                for($i = 0; $i < $NUM_SLOTS; $i++) {
                    $SLOTS[$i]['type'] = stripslashes($_REQUEST['slot-select'][$i]);
                    $SLOTS[$i]['name'] = stripslashes($_REQUEST['slot-name'][$i]);
                    $SLOTS[$i]['start'] = stripslashes($_REQUEST['slot-start'][$i]);
                    $SLOTS[$i]['end'] = stripslashes($_REQUEST['slot-end'][$i]);
                }
                
                update_option('bcbp_num_tracks', $NUM_TRACKS);
                update_option('bcbp_num_slots', $NUM_SLOTS);
                update_option('bcbp_category', $CATEGORY);
                update_option('bcbp_trackdata', $TRACKS);
                update_option('bcbp_slotdata', $SLOTS);
 
            }
    ?>
    <div id="bcbp_setup_container">
        <form method="POST" action="" class="wp-admin">

        	<fieldset>

        		<div class="bcbp_input_container">
                    <label for="num_tracks">Number of tracks</label>
            		<input name="num_tracks" id="num_tracks" type="number" value="<?php echo $NUM_TRACKS; ?>" />
                </div>

        		<div class="bcbp_input_container">
                    <label for="num_slots">Number of slots</label>
            		<input name="num_slots" id="num_slots" type="number" value="<?php echo $NUM_SLOTS; ?>" />
                </div>

                <div class="bcbp-_input_container">
                    <label for="category">Category</label>
                    <input name="category" id="category" type="number" value="<?php echo $CATEGORY; ?>" />
                </div>

                <div class="clear">&nbsp;</div>

                <?php for ($i = 0; $i < $NUM_TRACKS; $i++): ?>
                <div class="bcbp_input_container">
                    <div class="bcbp_track_container alignleft">
                            <label>Track <?php echo $i+1; ?></label> 
                            <input name="track[]" type="text" value="<?php echo $TRACKS[$i]; ?>" />
                    </div>
                </div>
                <?php endfor;  ?>
                
                
                <div class="clear">&nbsp;</div>
                <?php for ($i = 0; $i < $NUM_SLOTS; $i++): ?>
                
                <div class="bcbp_input_container bcbp_slot_container">
                    <label>Slot <?php echo $i+1; ?></label> 
                    
                    <label>Type</label>
                    <select name="slot-select[]">
                        <option value="fixed" <?php if ($SLOTS[$i]['type'] == "fixed") echo 'selected' ?> >Fixed</option>
                        <option value="session"  <?php if ($SLOTS[$i]['type'] == "session") echo 'selected' ?> >Session</option>
                    </select>
                    
                    <label>Name</label>
                    <input name="slot-name[]" type="text" value="<?php echo $SLOTS[$i]['name']; ?>" />
                    
                    <label>Start Time</label>
                    <input name="slot-start[]" type="number" value="<?php echo $SLOTS[$i]['start']; ?>" />
                    
                    <label>End Time</label>
                    <input name="slot-end[]" type="number" value="<?php echo $SLOTS[$i]['end']; ?>" />
                
                </div>
                <?php endfor;  ?>

                <input name="submit" id="submit" type="submit" class="button button-primary" value="Save" />
            <fieldset>
        </form>
    </div>
    <!-- End div #bcbp_setup_container -->
    
    <?php
            // End case 'setup':
            break;
            
            case 'schedule':
                ?>
    <div id="bcbp_schedule_container">
        
        <h3>Select user</h3>
        <form method="POST" id="bcbp_form_post_speaker" action="" class="wp-admin">
            <fieldset>
        		
                <div class="bcbp_input_container">
                    <label for="speaker_username">Enter Speaker username: </label>
            		<input name="speaker_username" id="speaker_username" type="text" />
                </div>

                <input name="speaker_submit" id="speaker_submit" type="submit" class="button button-primary" value="Get Sessions" />

            </fieldset>
        </form>

        <h3>Select sessions</h3>

        <h3>Current Pool</h3>
        
    </div>
    <!-- End div #bcbp_schedule_container -->
                <?php
                // End case 'schedule':
                break;

            case 'manual':
                # code...
                break;

            case 'publish':
                # code...
                break;

            default:
                # code...
                break;
        }
        //end switch/case for choosing tabs
    
    echo '</div>'; // end div class="wrap" around entire page
    
}

add_action("admin_enqueue_scripts", "bcbp_enqueue_admin_scripts");

/**
* Hook in the JS file for the admin page of the plugin
*/
function bcbp_enqueue_admin_scripts()
{
    wp_enqueue_script("bcbp_admin_script", plugin_dir_url(__FILE__)."bcbp_script.js", array('jquery'));
}

/**
* Function to return an array of users attending a session
*/
function bcbp_attending_users($post_id)
{
    return get_post_meta($post_id, 'user_attending');
}
/**
* Utility function used by uasort an array of sessions based on count of attendies
*/
function bcbp_sort_by_attendies($a, $b)
{
    if($a['attendies'] == $b['attendies'])
        return 0;

    return $a['attendies'] > $b['attendies'] ? -1 : 1 ;
}

/**
* AJAX Functions
*/

/**
* Executed for the Schedule tab, to get a list of sessions by a speaker
*/
add_action('wp_ajax_bcbp_schedule_post_speaker', 'bcbp_ajax_schedule_post_speaker');

function bcbp_ajax_schedule_post_speaker()
{
    $speaker_username =  trim($_REQUEST['speaker_username']);
    $bcbp_category = get_option('bcbp_category');

    $output = array();

    $query = new WP_Query(array ( "cat" => $bcbp_category, "author_name" => $speaker_username ) );

    if($query->have_posts())
    {
        while($query->have_posts())
        {
            $query->the_post();
            $curr_post = array();
            $curr_post['id'] = get_the_ID();
            $curr_post['title'] = get_the_title();
            $curr_post['attendies'] = count(bcbp_attending_users($curr_post['id']));

            array_push($output, $curr_post);
        }

    }
    uasort($output, 'bcbp_sort_by_attendies');

    echo json_encode($output);
    die();
//    echo $_REQUEST['speaker_username'];
  //  die();
}

?>
