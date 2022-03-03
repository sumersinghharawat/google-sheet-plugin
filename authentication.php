<?php


global $wpdb;

// Authentication Table
$table_name = $wpdb->prefix . "googlesheetplugin_auth";
$charset_collate = $wpdb->get_charset_collate();
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
id mediumint(9) NOT NULL AUTO_INCREMENT,
time datetime DEFAULT '0000-00-00 00:00:00' NULL,
name text NULL,
PRIMARY KEY  (id)
) $charset_collate;";


require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Sheet Table
$table_name = $wpdb->prefix . "googlesheetplugin_sheets";
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
id mediumint(9) NOT NULL AUTO_INCREMENT,
time datetime DEFAULT '0000-00-00 00:00:00' NULL,
name text NULL,
data_counts text NULL,
PRIMARY KEY  (id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Product Table
$table_name = $wpdb->prefix . "googlesheetplugin_product";
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
id mediumint(9) NOT NULL AUTO_INCREMENT,
time datetime DEFAULT '0000-00-00 00:00:00' NULL,
name text NULL,
PRIMARY KEY  (id)
) $charset_collate;";


require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Import Credential into JSON
try {
    $table_name = $wpdb->prefix . "googlesheetplugin_auth";
    $post = $_POST['credential'];
    $data = $wpdb->get_results("SELECT * FROM " . $table_name);

    if ($wpdb->num_rows) {
        if ($_POST) {
            $wpdb->update($table_name, array('name' => stripslashes($post)), array('id' => 1));
            $data = $wpdb->get_results("SELECT * FROM " . $table_name);
            $file = file_put_contents(wp_upload_dir()['path'] . '/credentials.json', $data[0]->name);
        }
    } else {
        $wpdb->insert($table_name, array('name' => ""));
    }

    include 'config.php';
    include 'snippets.php';
    //code...
} catch (\Throwable $th) {
    // throw $th;
    echo "<div class='border'><span class='text-danger'>Wrong Format. Please copy your credential json code.</span></div>";
}
