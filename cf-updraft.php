<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       Caldera Forms - UpdraftPlus Integration
 * Description:       Upload forms via UpdraftPlus
 * Version:           1.0
 * Author:            Alex Agranov
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

add_filter('caldera_forms_get_form_processors', 'cf_updraft_register_processor');
add_filter('updraft_backupable_file_entities_final', 'cf_updraft_add_entries');
        
function cf_updraft_register_processor($pr) {
    $pr['updraft'] = array(
        "name"              =>  __('UpdraftPlus', 'cf-updraft'),
        "description"       =>  __("Save file on submission", 'cf-updraft'),
        "author"            =>  'Alex Agranov',
        "icon"              =>  plugin_dir_url(__FILE__) . "icon.png",
        "processor"         =>  'cf_updraft_save_file',
        "template"          =>  plugin_dir_path(__FILE__) . "config.php",
    );
    return $pr;
}         
         
function cf_updraft_create_file($config, $form, $filename, $dir, $date) {
    // create message
    $message = $form['mailer']['email_message']; 
    if(empty($message)) {
        $message = "{summary}";
    } 
    $message = Caldera_Forms::do_magic_tags($message);
    $message = str_replace("\r\n", "<br />\r\n", $message); 
    $message = $config['header'] . "\r\n" . $message . "\r\n" . $config['footer'] . "\r\n\r\n"; 

    // get tags
    preg_match_all("/%(.+?)%/", $message, $hastags);
    if (!empty($hastags[1])) {
        foreach($hastags[1] as $tag_key=>$tag){
            $tagval = Caldera_Forms::get_slug_data($tag, $form);
            if(is_array($tagval)){
                $tagval = implode(', ', $tagval);
            }
            $message = str_replace($hastags[0][$tag_key], $tagval, $message);
        }
    }
    
    // ifs
    preg_match_all("/\[if (.+?)?\](?:(.+?)?\[\/if\])?/", $message, $hasifs);
    if (!empty($hasifs[1])) {
        // process ifs
        foreach ($hasifs[0] as $if_key=>$if_tag) {

            $content = explode('[else]', $hasifs[2][$if_key]);
            if (empty($content[1])) {
                $content[1] = '';
            }
            $vars = shortcode_parse_atts( $hasifs[1][$if_key]);
            foreach ($vars as $varkey=>$varval) {
                if (is_string($varkey)) {
                    $var = Caldera_Forms::get_slug_data($varkey, $form);
                    if (in_array($varval, (array) $var)) {
                        // yes show code
                        $message = str_replace( $hasifs[0][$if_key], $content[0], $message);
                    } else {
                        // nope- no code
                        $message = str_replace( $hasifs[0][$if_key], $content[1], $message);
                    }
                } else {
                    $var = Caldera_Forms::get_slug_data($varval, $form);
                    if (!empty($var)) {
                        // show code
                        $message = str_replace( $hasifs[0][$if_key], $content[0], $message);
                    } else {
                        // no code
                        $message = str_replace( $hasifs[0][$if_key], $content[1], $message);
                    }
                }
            }
        }
    }

    // write file
    $file = @fopen( $dir . $filename . '_' . str_replace(' ', '_', $date) . '.html' ,'a');
    if( $file ){
        fwrite($file, $message);
        fclose($file);
    }
}


function cf_updraft_update_index($config, $form, $filename, $dir, $date) {
    $title = trim($config['title']); 
    if (empty($title)) {
        $title = $filename;
    }
    $title = Caldera_Forms::do_magic_tags($title); 

    // update body.html
    $file = @fopen($dir . 'body.html', 'a');
    if ($file) {
        fwrite($file, "  <tr valign=\"top\">\n");
        fwrite($file, "    <td nowrap=\"nowrap\">" . $date . "</td>\n");
        fwrite($file, "    <td width=\"90%\"><a href=\"" . $filename . "_" . str_replace(' ', '_', $date) . ".html\">" . $title . "</a></td>\n");
        fwrite($file, "  </tr>\n");
    }
    
    // create index.html
    $out = @fopen($dir . 'index.html', 'w');
    $in = @fopen(plugin_dir_path( __FILE__ ) . 'header.html', 'r');
    if ($in) {
        while (($line = fgets($in)) !== false) {
            fwrite($out, str_replace('{form_name}', $form['name'], $line));
        }
        fclose($in);
    }
    $in = @fopen($dir . 'body.html', 'r');
    if ($in) {
        while (($line = fgets($in)) !== false) {
            fwrite($out, $line);
        }
        fclose($in);
    }
    $in = @fopen(plugin_dir_path( __FILE__ ) . 'footer.html', 'r');
    if ($in) {
        while (($line = fgets($in)) !== false) {
            fwrite($out, $line);
        }
        fclose($in);
    }
}


function cf_updraft_save_file($config, $form) {
    $directory = trim($config['directory']); 
    $filename = trim($config['filename']); 
    if (empty($filename)) {
        $filename = "Form";
    }
    $filename = Caldera_Forms::do_magic_tags($filename);
    $filename = str_replace(' ', '%20', $filename); 

    $dir = WP_CONTENT_DIR . '/uploads/caldera-forms/';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755);
          $file = @fopen($log_directory.'.htaccess','w');
            if ($file)  {
            fwrite($file, 'deny from all');
            fclose($file);
            }
    }

    if (!empty($directory)) {
        $dir = $dir . $directory . '/'; 
    }
    if (!is_dir($dir)) {
        @mkdir($dir, 0755);
    }

    $date = date('Y-m-d H:i:s');
    
    cf_updraft_create_file($config, $form, $filename, $dir, $date);
    cf_updraft_update_index($config, $form, $filename, $dir, $date);
}


function cf_updraft_add_entries($arr, $full_info = false) {
    $arr['forms'] = array('path' => WP_CONTENT_DIR . '/uploads/caldera-forms' , 'description' => __('Caldera forms', 'cf-updraft'));
    return $arr;
}