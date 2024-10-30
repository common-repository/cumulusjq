<?php
/*
Plugin Name: Cumulus Jq
Plugin URI: http://janbee-myjquery.pcriot.com/
Description: Cumulus using jQuery
Version: 1.0
Author: JanBee Angeles
Author URI: http://janbee-myjquery.pcriot.com/
License: GPL2
*/          

error_reporting(E_ALL);     
add_action("widgets_init", array('cumulus', 'register'));

register_activation_hook( __FILE__, array('cumulus', 'activate'));
register_deactivation_hook( __FILE__, array('cumulus', 'deactivate'));
register_activation_hook(__FILE__,array('cumulus', 'jal_install'));  
/*            global $wpdb;
            echo '<pre>';
            
            print_r(get_class_methods($wpdb));
            echo '</pre>';  */
              
class cumulus {
 
    function activate(){
        $data = array( 
            'title' => '',
            'width' => '',
            'height' => '',
            'radius' => '',
            'bg' => '',
            'speed' => '',            
            'cat' => '' 
        );
        if ( ! get_option('widget_cumulusjq')){
            add_option('widget_cumulusjq' , $data);
        } 
        else {
            update_option('widget_cumulusjq' , $data);
        }
    }     
    function deactivate(){
        delete_option('widget_cumulusjq');
    } 

    function control(){ 
        global $wpdb;
        $table = $wpdb->prefix.'cumulus_external';
        $data = get_option('widget_cumulusjq'); 
        $mylink = $wpdb->get_results("SELECT text FROM $table WHERE del = 1");
        if(empty($mylink[0]->text)){$mylink[0]->text = '';}
        $checkCat = !empty($data['cat']) ? '<input type="checkbox" name="cat" checked/>' : '<input type="checkbox" name="cat" />' ; 
        $html = '
        <link rel="stylesheet" type="text/css" href="../wp-content/plugins/cumulusjq/css/help.css"> 
        
        <div>
            <label>Title:<span class="cumuSpan">(required)</span></label>
            <br>
            <input class="cPanel" name="title" type="text" value="'.$data['title'].'" />
        </div>
        
        <div>
            <label>Input External link: e.g.</label>
            <br> 
            <span class="cumuSpan">goole : www.google.com ,</span>
            <br>
            <span class="cumuSpan" ">yahoo : www.yahoo.com </span>
            <br>
            <textarea name="links" class="cPanel" cols="10" rows="4">'.$mylink[0]->text.' </textarea>
            <label>Background</label>
            <br>
            <input class="bg" name="bg" type="text" value="'.$data['bg'].'" />
            <br>
            <label>Container Width</label>
            <br>
            <input class="sphere" name="width" type="text" value="'.$data['width'].'" />px
            <br> 
            <label>Container Height</label>
            <br>
            <input class="sphere" name="height" type="text" value="'.$data['height'].'" />px
            <br> 
            <label>Sphere Radius</label>
            <br>
            <input class="sphere" name="radius" type="text" value="'.$data['radius'].'" />
            <br>
            <label>Sphere Speed</label>
            <br>
            <input class="sphere" name="speed" type="text" value="'.$data['speed'].'" />
            <br>
            <label>Include Categories</label>
            <br>
            '.$checkCat.'
        </div>
        
        ';
        
        echo $html;

        if (!empty($_POST['title'])){
            $wpdb->query("DELETE FROM $table WHERE del = 1");
                      
            $data['title'] = attribute_escape($_POST['title']); 
            $data['bg'] = attribute_escape($_POST['bg']);
            $data['width'] = attribute_escape($_POST['width']);
            $data['height'] = attribute_escape($_POST['height']);
            $data['radius'] = attribute_escape($_POST['radius']);
            $data['speed'] = attribute_escape($_POST['speed']); 
            $data['cat'] = $_POST['cat'];
            
            update_option('widget_cumulusjq', $data);
        
            $aryLink = array();
            $aryLink = explode(',',$_POST['links']);
            
                                       
            for($i=0; $i<count($aryLink); $i++){
                $ary[] = explode(':',$aryLink[$i]);        
                $aryName[] = $ary[$i][0];
                $aryUrl[] = $ary[$i][1];  
                $wpdb->insert($table, array(
                    'name' => $aryName[$i],
                    'url' => $aryUrl[$i],
                    'del' => '1',
                    'text' => $_POST['links']
                ));                   
                                  
            } 
            $_POST['title'] = '';
        }      
    }
    
    function widget($args){
        global $wpdb;
        $data = get_option('widget_cumulusjq');
        
        $table = $wpdb->prefix.'cumulus_external';
        $rows = $wpdb->get_results("SELECT name,url FROM wp_cumulus_external");

        echo $args['before_widget'];
        echo $args['before_title'] . $data['title'] . $args['after_title'];
        
        $html = '
            <script type="text/javascript" src="wp-content/plugins/cumulusjq/js/jquery-1.4.2.min.js"></script>
            <script type="text/javascript" src="wp-content/plugins/cumulusjq/js/jqsphere.js"></script>
            <div id="wpTagCloud">
            <ul >';
                for($i=0;$i<count($rows);$i++){
                    $html .= '<li><a target="_blank" href="http://'.trim($rows[$i]->url).'">'.$rows[$i]->name.'</a></li>';
                }
                if(!empty($data['cat'])){    
                    $cat = get_terms( 'category' );
                    for($i=0;$i<count($cat);$i++){
                        $html .= '<li><a href="'.get_category_link($cat[$i]->term_id).'">'.$cat[$i]->name.'</a></li>';
                    }
                }
        $html .= '
            </ul>
            </div>
            <form>
                <input type="hidden" id="width" value="'.$data['width'].'" > 
                <input type="hidden" id="height" value="'.$data['height'].'" >
                <input type="hidden" id="radius" value="'.$data['radius'].'" >
                <input type="hidden" id="bg" value="'.$data['bg'].'" >
                <input type="hidden" id="speed" value="'.$data['speed'].'" > 
            </form>
            <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery("#wpTagCloud").tagSphere(jQuery("#width").val());
            })
            </script>
        ';
        echo $html;
        echo $args['after_widget'];   

    }
    function register(){          
        register_sidebar_widget('CumulusJq', array('cumulus', 'widget'));
        register_widget_control('CumulusJq', array('cumulus', 'control'));
    }
    
    
    
    /*------------------------------- database table ------------------------------*/
    function jal_install (){
        global $wpdb;     

        $table_name = $wpdb->prefix . "cumulus_external";
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE " . $table_name . " (
            id mediumint(9) NOT NULL AUTO_INCREMENT,          
            name tinytext NOT NULL,            
            url VARCHAR(55) NOT NULL,
            del VARCHAR(1) NOT NULL,
            text TEXT NOT NULL,

            UNIQUE KEY id (id)
            );";
        }
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
            
}   
?>