<?php
   /*
   Plugin Name: Vocabulary
   Plugin URI: https://super-teacher.com/vocabulary/
   description: A tool to memorize texts mainly vocabulary
   Version: 0.0
   Author: Rafi
   Author URI: http://super-teacher.com
   License: GPL2
   */

// limits the search function to the voc_words cpt
function searchfilter($query) { 
    if ($query->is_search && !is_admin() ) {
        $query->set('post_type',array('voc_words'));
    }
    return $query;
}
add_filter('pre_get_posts','searchfilter');

//set the logo of the original wordpress login dialog to my logo
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo "https://super-teacher.com/vocabulary/wp-content/uploads/sites/6/2021/06/HatManSmall1.png"?>);
		height:65px;
		width:320px;
		background-size: 85px 75px;
		background-repeat: no-repeat;
        	padding-bottom: 30px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );

//set the link behind the logo image
function my_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

//change the text of the log
function my_login_logo_url_title() {
    return 'Super-teacher Vocabulary';
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );

//Priority of a post 0 or 1 (low or high)
function getpriority($vid){
    $priority = get_the_terms( $vid, 'priorities' );
if (!$priority){//if priority is not set set it to 0
    wp_set_object_terms( $vid,  52 , 'priorities');
}
return(get_the_terms( $vid, 'priorities' )[0]->term_id);
}


// For including js in the front end templates
/**    Returns the offset from the origin timezone to the remote timezone, in seconds.
*    @param $remote_tz;
*    @param $origin_tz; If null the servers current timezone is used as the origin.
*    @return int;
*/

// Using WP ajax localizer to pass php variables (functions) to jquery
function my_frontend_script() {
    wp_enqueue_script( 'voc_scripts', plugin_dir_url( __FILE__ ) . 'js/voc_scripts.js',array( 'jquery' ), '1.0.0', true  );
    wp_localize_script( 'voc_scripts', 'MyAjax',
    array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),
    'rate'=> get_srate(),
    'pitch'=> get_spitch(),
    'dicu'=> get_dicu(),
    ) );
}
add_action( 'wp_enqueue_scripts', 'my_frontend_script' );

//enque style for the plugin
function utm_user_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'style',  $plugin_url . "style.css");
}
add_action( 'admin_print_styles', 'utm_user_scripts' );
    
// server side ajax function to delete the post
add_action( 'wp_ajax_my_delete_post', 'my_delete_post' );
function my_delete_post(){
    $permission = check_ajax_referer( 'my_delete_post_nonce', 'nonce', false );
    if( $permission == false ) {
        echo 'error';
    }
    else {
        wp_delete_post( $_REQUEST['id'] );
        echo 'success';
    }
    die();
}
function days($tmstmp1,$tmstmp2){
# calculates the number of days between the earlier timestamp $tmstmp2 and the later timestamp tmstmp1
  $dt1 = new DateTime();
  $dt1->setTimestamp($tmstmp1);
  $dt2 = new DateTime();
  $dt2->setTimestamp($tmstmp2);
  $diff=date_diff($dt1,$dt2,true);
  if (($diff->d==0)and(date("ymd",$tmstmp1)!=date("ymd",$tmstmp2))) 
	  return (1);
	else
      return($diff->d);
#  return($diff->d);//integer
 } 
// User setup parameter: Maximum texts per day 
function get_max_txts(){
    $uid=get_current_user_id();
    $maxtexts=get_user_meta( $uid, 'maxtexts',true );
    if (!$maxtexts){return(30);}else{return($maxtexts);}
}
// User setup parameter: Second dictionary URL 
function get_dicu(){
    $uid=get_current_user_id();
    $dicu=get_user_meta( $uid, 'dicu',true );
    if (!$dicu){return('http://translate.google.com/#ge|en|<word>');}else{return($dicu);}
}

// User setup parameter: Speed of speech.
function get_srate(){
    $uid=get_current_user_id();
    $srate=get_user_meta( $uid, 'srate',true );
    if (!$srate){return('0.7');}else{return($srate);}
}

// User setup parameter: The pitch of the speech voice
function get_spitch(){
    $uid=get_current_user_id();
    $spitch=get_user_meta( $uid, 'spitch',true );
    if (!$spitch){return('1.0');}else{return($spitch);}
}

// User setup parameter: The delay before the speech starts
function get_sdelay(){
    $uid=get_current_user_id();
    $sdelay=get_user_meta( $uid, 'sdelay',true );
    if (!$sdelay){return('0.5');}else{return($sdelay);}
}

// User setup parameter: The time zone of the user 
function get_tz_offset(){
  $uid=get_current_user_id();
  $tzoffset=get_user_meta( $uid, 'tzoffset', true );
  if (!$tzoffset){return(3600*9);}else{return($tzoffset);}
}

// the current time of the user.
function getnow(){
    $tzoffset=get_tz_offset();
    $tme=time();
    return($tme+$tzoffset);
}    

// Get the date a user clicked on a thumb button specific to a post
function get_thumbdate($pid){
    $tme=get_post_meta( $pid, 'thumb_date', true );
    if (!$tme){return(0);}else{return($tme);}
} 

// test if thumb is clicked today
function istoday($ts){
  $tzoffset=get_tz_offset();
  return( date('Ymd',time()+$tzoffset)==date('Ymd',$ts));
}

// the time of a post thumb click
function getttime($pid){
    $tzoffset=get_tz_offset();
    $tme=get_thumbdate($pid);
    if( !$tme  ) return(0); else return($tme+$tzoffset);
}

// server side thumb down click
add_action( 'wp_ajax_my_thumb_dn', 'my_thumb_dn' );
function my_thumb_dn(){
    $pid=$_REQUEST['id'];
    setpri21($pid);
    if (istoday(getttime($pid))) {
        die;
    }
    $permission = check_ajax_referer( 'my_thumb_dn_nonce', 'nonce', false );
    if( $permission == false ) {
        echo 'error';
    }
    else {
// the action: change the number of thumbs up if it is bigger than the upgrade trigger 
// upgrade the phase and zero the number of thumbs up.
        // the triggers for upgrading phase are the ids of the term of number of days remembered In the future these should be adjusted according to the ability of each student. 16 is three consequtive times 15 is two 14 is one 17 is four 18 is five.
        $pid=$_REQUEST['id'];
        $phases = get_the_terms( $pid, 'phases' );
        $phase=$phases[0]->term_id; // the term_id member of the first item in the array
        $terms = get_the_terms( $pid, 'days_rmmbrd' );//an array of objects
        $term=$terms[0]->term_id; // the term_id member of the first item in the array
        if (empty($term) or ($term==0) or ($term==49)) $term = 13;//one
        if (empty($phase) or ($phase==0)) $phase = 42;//one
            switch($phase) {
            case 42: $term = 13;
                 break;
            case 43: $term = 13;if ($term<14){$phase=42;} 
                 break;
            case 44:  $term = 13;if ($term<14){$phase=43;}
                 break;
            case 45:   $term = 13;if ($term<14){$phase=44;}
                 break;
            case 46: $term = 13;if ($term<15){$phase=42;}
                 break;
            case 47: $term = 13;$phase=42;
                 break;
            case 48: $term = 13;$phase=42;
                 break;
        }
        //set last thumb to down    
        wp_set_object_terms( $pid,  51 , 'last_thumbs');
        // set the date the action took place
        wp_set_object_terms( $pid,  $phase , 'phases');
        // set the date the action took place
        $rslt1=update_post_meta( $pid, 'thumb_date', time ( ) );
        //   $rslt1=update_post_meta( $pid, 'thumb_date', $term ( ) );
        wp_set_object_terms( $pid, $term, 'days_rmmbrd' );
        if(! is_wp_error( $return ) ) echo 'success';
    }
    die();
}

function voc_filter($phase,$last_thumb){
//this function returns true if the item should be shown.
// phase is the number of days between showing.
// the timestamp shown last time.
get_tz_offset();
$days=days($last_thumb,time()+$tzoffset);
$b=($phase=='Daily');
$b=($b or($days==0));
$b=($b or (($phase=='Tridaily')and($days>=3)));
$b=($b or (($phase=='Weekly')and($days>=7)));
$b=($b or (($phase=='Biweekly')and($days>=14)));
$b=($b or (($phase=='Monthly')and($days>=30)));
$b=($b or (($phase=='Bimonthly')and($days>=60)));
$b=($b or (($phase=='Trimonthly')and($days>=90)));
return($b);
}

// server side ajax function thumbup the post
add_action( 'wp_ajax_my_thumb_up', 'my_thumb_up' );
function my_thumb_up(){
        $pid=$_REQUEST['id'];
        setpri21($pid);
        if (istoday(getttime($pid))) {
            die;
        }
    $permission = check_ajax_referer( 'my_thumb_up_nonce', 'nonce', false );
    if( $permission == false ) {
        echo 'error';
    }
    else {
// the action: change the number of thumbs up if it is bigger than the upgrade trigger 
// upgrade the phase and zero the number of thumbs up.
        // the triggers for upgrading phase are the ids of the term of number of days remembered In the future these should be adjusted according to the ability of each student. 16 is three consequtive times 15 is two 14 is one 17 is four 18 is five.
        $pid=$_REQUEST['id'];
        $trig_daily = 15;$trig_tridaily = 15;$trig_weekly = 15;$trig_biweekly=15;$trig_monthly = 14;$trig_bimonthly = 14; $trig_trimonthly=15;
        $phases = get_the_terms( $pid, 'phases' );
        $phase=$phases[0]->term_id; // the term_id member of the first item in the array
        $terms = get_the_terms( $pid, 'days_rmmbrd' );//an array of objects
        $term=$terms[0]->term_id; // the term_id member of the first item in the array
        if (empty($term) or ($term==0) or ($term==49)) $term = 12;//one
        ++$term ;
        if (empty($phase) or ($phase==0)) $phase = 42;//one
            switch($phase) {
            case 42: if ($term>=$trig_daily){ $phase=43;$term = 49;}
                 break;
            case 43: if ($term>=$trig_tridaily){ $phase=44;$term = 49;}
                 break;
            case 44: if ($term>=$trig_weekly){ $phase=45;$term = 49;}
                 break;
            case 45: if ($term>=$trig_biweekly){ $phase=46;$term = 49;}
                 break;
            case 46: if ($term>=$trig_monthly){ $phase=47;$term = 49;}
                 break;
            case 47: if ($term>=$trig_bimonthly){ $phase=48;$term = 49;}
                 break;
            case 48: if ($term>=$trig_trimonthly){ $phase=49;$term = 49;}
                 break;
        }
        //set last thumb to up    
        wp_set_object_terms( $pid,  50 , 'last_thumbs');
        //set the phase
        wp_set_object_terms( $pid,  $phase , 'phases');
        // set the date the action took place
        $rslt1=update_post_meta( $pid, 'thumb_date', time ( ) );
        //   $rslt1=update_post_meta( $pid, 'thumb_date', $term ( ) );
        wp_set_object_terms( $pid, $term, 'days_rmmbrd' );
        if(! is_wp_error( $return ) ) echo 'success';
    }
    die();
}


/**
 * Add custom taxonomiess
 *
 * Additional custom taxonomies can be defined here
 * http://codex.wordpress.org/Function_Reference/register_taxonomy
 */
//custome taxonomies
function add_custom_taxonomies() {
    register_taxonomy('phases', 'voc_words', array(
    // Hierarchical taxonomy (like categories)
    'hierarchical' => false,
    // This array of options controls the labels displayed in the WordPress Admin UI
    'labels' => array(
      'name' => _x( 'phases', 'taxonomy general name' ),
      'singular_name' => _x( 'phase', 'taxonomy singular name' ),
      'search_items' =>  __( 'Search phases' ),
      'all_items' => __( 'All phases' ),
      'parent_item' => __( 'Parent phase' ),
      'parent_item_colon' => __( 'Parent phase:' ),
      'edit_item' => __( 'Edit phase' ),
      'update_item' => __( 'Update phase' ),
      'add_new_item' => __( 'Add New phase' ),
      'new_item_name' => __( 'New phase Name' ),
      'menu_name' => __( 'Phases' ),
    ),
    // Control the slugs used for this taxonomy
    'rewrite' => array(
      'slug' => 'phases', // This controls the base slug that will display before each term
      'with_front' => false, // Don't display the category base before "/phase/"
      'hierarchical' => true // This will allow URL's like "/phase/daily/"
    ),
  ));
   register_taxonomy('days_rmmbrd', 'voc_words', array(
    // Hierarchical taxonomy (like categories)
    'hierarchical' => false,
    // This array of options controls the labels displayed in the WordPress Admin UI
    'labels' => array(
      'name' => _x( 'days_rmmbrd', 'taxonomy general name' ),
      'singular_name' => _x( 'day_rmmbrd', 'taxonomy singular name' ),
      'search_items' =>  __( 'Search days_rmmbrd' ),
      'all_items' => __( 'All days_rmmbrd' ),
      'parent_item' => __( 'Parent day_rmmbrd' ),
      'parent_item_colon' => __( 'Parent day_rmmbrd:' ),
      'edit_item' => __( 'Edit day_rmmbrd' ),
      'update_item' => __( 'Update day_rmmbrd' ),
      'add_new_item' => __( 'Add New day_rmmbrd' ),
      'new_item_name' => __( 'New day_rmmbrd Name' ),
      'menu_name' => __( 'days_rmmbrd' ),
    ),
    // Control the slugs used for this taxonomy
    'rewrite' => array(
      'slug' => 'days_rmmbrd', // This controls the base slug that will display before each term
      'with_front' => false, // Don't display the category base before "/phase/"
      'hierarchical' => true // This will allow URL's like "/phase/daily/"
    ),
  ));

   register_taxonomy('last_thumbs', 'voc_words', array(
    // Hierarchical taxonomy (like categories)
    'hierarchical' => false,
    // This array of options controls the labels displayed in the WordPress Admin UI
    'labels' => array(
      'name' => _x( 'last_thumbs', 'taxonomy general name' ),
      'singular_name' => _x( 'last_thumb', 'taxonomy singular name' ),
      'search_items' =>  __( 'Search last_thumbs' ),
      'all_items' => __( 'All last_thumbs' ),
      'parent_item' => __( 'Parent last_thumb' ),
      'parent_item_colon' => __( 'Parent last_thumb:' ),
      'edit_item' => __( 'Edit last_thumb' ),
      'update_item' => __( 'Update last_thumb' ),
      'add_new_item' => __( 'Add New last_thumb' ),
      'new_item_name' => __( 'New last_thumb Name' ),
      'menu_name' => __( 'last_thumbs' ),
    ),
    // Control the slugs used for this taxonomy
    'rewrite' => array(
      'slug' => 'last_thumbs', // This controls the base slug that will display before each term
      'with_front' => false, // Don't display the category base before "/phase/"
      'hierarchical' => true // This will allow URL's like "/phase/daily/"
    ),
   ));
  
   register_taxonomy('priorities', 'voc_words', array(
    // Hierarchical taxonomy (like categories)
    'hierarchical' => false,
    // This array of options controls the labels displayed in the WordPress Admin UI
    'labels' => array(
      'name' => _x( 'priorities', 'taxonomy general name' ),
      'singular_name' => _x( 'priority', 'taxonomy singular name' ),
      'search_items' =>  __( 'Search priorities' ),
      'all_items' => __( 'All priorities' ),
      'parent_item' => __( 'Parent priority' ),
      'parent_item_colon' => __( 'Parent priority:' ),
      'edit_item' => __( 'Edit priority' ),
      'update_item' => __( 'Update priority' ),
      'add_new_item' => __( 'Add New priority' ),
      'new_item_name' => __( 'New priority Name' ),
      'menu_name' => __( 'priorities' ),
    ),
    // Control the slugs used for this taxonomy
    'rewrite' => array(
      'slug' => 'priorities', // This controls the base slug that will display before each term
      'with_front' => false, // Don't display the category base before "/phase/"
      'hierarchical' => true // This will allow URL's like "/phase/daily/"
    ),
  ));
    
  
}
add_action( 'init', 'add_custom_taxonomies', 0 );


// Our custom post type function
function create_posttype() {
 
    register_post_type( 'voc_words',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'voc_words' ),
                'singular_name' => __( 'voc_word' )
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'vocabulary'),
            'show_in_rest' => true,
 
        )
    );
//    unregister_taxonomy_for_object_type( 'phases', 'voc_words' );
}
// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );

/*
* Creating a function to create our CPT
*/
 
function custom_post_type() {
 
// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'voc_words', 'Post Type General Name', 'Enfold' ),
        'singular_name'       => _x( 'voc_word', 'Post Type Singular Name', 'Enfold' ),
        'menu_name'           => __( 'voc_words', 'Enfold' ),
        'parent_item_colon'   => __( 'Parent word', 'Enfold' ),
        'all_items'           => __( 'All words', 'Enfold' ),
        'view_item'           => __( 'View words', 'Enfold' ),
        'add_new_item'        => __( 'Add New word', 'Enfold' ),
        'add_new'             => __( 'Add New', 'Enfold' ),
        'edit_item'           => __( 'Edit word', 'Enfold' ),
        'update_item'         => __( 'Update word', 'Enfold' ),
        'search_items'        => __( 'Search word', 'Enfold' ),
        'not_found'           => __( 'Not Found', 'Enfold' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'Enfold' ),
    );
     
// Set other options for Custom Post Type
     
    $args = array(
        'label'               => __( 'Word', 'Enfold' ),
        'description'         => __( 'Translation', 'Enfold' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'custom-fields', ),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        'taxonomies'          => array( 'group' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */ 
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
 
    );
     
    // Registering your Custom Post Type
    register_post_type( 'voc_words', $args );
 
}
 
/* Hook into the 'init' action so that the function
* Containing our post type registration is not 
* unnecessarily executed. 
*/
 
add_action( 'init', 'custom_post_type', 0 );

//Register the vid as a query parameter
add_filter('query_vars', 'add_my_var');
function add_my_var($public_query_vars) {
    $public_query_vars[] = 'vid';
    $public_query_vars[] = 'rtnurl';
    return $public_query_vars;
}
/**
 * Disable comments on pages.
 *
 * @param bool $open    Whether comments should be open.
 * @param int  $post_id Post ID.
 * @return bool Whether comments should be open.
 */
function wpdocs_comments_open( $open, $post_id ) {
    $post = get_post( $post_id );
    if ( 'voc_words' == $post->post_type )
        $open = false;
    return $open;
}
add_filter( 'comments_open', 'wpdocs_comments_open', 10, 2 );

?>