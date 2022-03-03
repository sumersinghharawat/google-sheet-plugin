<?php

/**
 
 * @package Google Spreadsheet Plugin
 
 */

use WC_my_custom_plugin as GlobalWC_my_custom_plugin;

/*
 
Plugin Name: Google Spreadsheet Plugin
 
Plugin URI: https://webtechbug.com/
 
Description: 
 
Version: 0.0.1
 
Author: WebTech Bug
 
Author URI: https://webtechbug.com/
 
License: later 
*/

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

add_action('woocommerce_after_register_post_type', 'google_sheet_plugin_checkout', 11);


function google_sheet_plugin_checkout()
{
    include("config.php");
    if (isset($_GET['key'])) {
        $order =  $_GET['key'];
        $orderId = wc_get_order_id_by_order_key($order);
        $orders = new WC_Order($orderId);

        global $wpdb;
        $table_name = $wpdb->prefix . "googlesheetplugin_sheets";
        $data = $wpdb->get_results("SELECT * FROM " . $table_name);

        $spreadsheetID = $data[0]->name;

        $range = "A:Z";

        $response = $service->spreadsheets_values->get($spreadsheetID, $range);

        $values = $response->getValues();

        if (empty($values)) {
            print "No data found.\n";
        } else {
            $totalcount = count($values);
            $max = 0;
            $bookedcount = 0;
            foreach ($values as $record) {
                if ($max < count($record) - 1) {
                    $max = count($record) - 1;
                }
                if ($record[$max] == "Booked") {
                    // echo "Booked";
                    $bookedcount = $bookedcount + 1;
                }
            }
            
            $totalpurchase = $orders->get_item_count();
            
            if ($orders->get_status() == "processing") {
            $records = getdata($service, $spreadsheetID, $bookedcount + 1, $totalpurchase + $bookedcount + 1);

            $table = '<table>';
            foreach ($records as $recordkey1 => $recordvalue1) {
                $table .=  '<tr>';
                foreach ($recordvalue1 as $recordkey2 => $recordvalue2) {

                    $table .=  '<td>' . $recordvalue2 . '</td>';
                }
                $table .= '</tr>';
            }
            $table .= '</table>';

            
                
                echo $table;
                if (wp_mail($orders->get_billing_email(), "Email send", $table)) {
                    echo "EMail sent";
                } else {
                    echo "EMail not sent";
                }
                $orders->update_status("wc-completed");
                print_r(highlight($spreadsheetID, $service, $bookedcount + 1, $totalpurchase + $bookedcount + 1));
            }
        }
    }
}

add_action('admin_menu', 'google_sheet_plugin');

function google_sheet_plugin()
{
    $page_title = 'Google Sheet Plugin';
    $menu_title = 'Google Sheet Plugin';
    $capability = '1';
    $menu_slug  = 'excel-import';
    $function   = 'excel_import';
    $icon_url   = 'dashicons-media-code';
    $position   = 4;
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
    add_submenu_page($menu_slug, 'Authentication', 'Authentication', 'manage_options', 'authentication', 'authentication');
    add_submenu_page($menu_slug, 'Product Manage', 'Product Manage', 'manage_options', 'product-manage', 'product_manage');
    add_submenu_page($menu_slug, 'Data', 'Data', 'manage_options', 'crud-data', 'crud_data');
    $hook = add_submenu_page(
        null,
        __('Welcome', 'textdomain'),
        __('Welcome', 'textdomain'),
        'manage_options',
        'xmltoexcel-api',
        'xmltoexcel_api'
    );

    add_action('load-' . $hook, function () {
        // add your xml code here, 
        // you will get a blank page to start with
        xmltoexcel_api();
        exit;
    });

    $hook = add_submenu_page(
        null,
        __('Welcome', 'importurl'),
        __('Welcome', 'importurl'),
        'manage_options',
        'importurl-api',
        'importurl'
    );

    add_action('load-' . $hook, function () {
        // add your xml code here, 
        // you will get a blank page to start with
        importurl();
        exit;
    });



    $hook = add_submenu_page(
        null,
        __('Welcome', 'showsheetdata'),
        __('Welcome', 'showsheetdata'),
        'manage_options',
        'show-sheetdata',
        'showsheetdata'
    );

    add_action('load-' . $hook, function () {
        // add your xml code here, 
        // you will get a blank page to start with
        showsheetdata();
        exit;
    });
}
// Create Sheet
function crud_data()
{

    global $wpdb;
    $table_name = $wpdb->prefix . "googlesheetplugin_auth";
    $data = $wpdb->get_results("SELECT * FROM " . $table_name);

    $spreadsheetID = $data[0]->name;

    if ($spreadsheetID == null) {
        header("location:admin.php?page=authentication");
    }

    include 'config.php';

    $table_name = $wpdb->prefix . "googlesheetplugin_sheets";
    $data = $wpdb->get_results("SELECT * FROM " . $table_name);

    $spreadsheetID = $data[0]->name;

    if ($spreadsheetID == null) {
        header("location:admin.php?page=excel-import");
    }

    $range = "A:Z";

    $response = $service->spreadsheets_values->get($spreadsheetID, $range);

    $values = $response->getValues();

    if (empty($values)) {
        print "No data found.\n";
    } else {
?>
        <link href="https://cdn.datatables.net/1.11.1/css/jquery.dataTables.min.css" rel="stylesheet" />
        <script src="//cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>
        <script>
            jQuery(document).ready(function() {
                jQuery('#table_id').DataTable();
            });
        </script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">

        <div class="col p-5 border">
            <table class="table table-striped " id="table_id">
                <?php
                foreach ($values as $datakey => $datavalue) {
                    if ($datakey == 0) {
                ?>
                        <thead>
                            <tr>
                                <?php
                                foreach ($datavalue as $childdatakey => $childdatavalue) {
                                ?>
                                    <th>
                                        <?php print_r($childdatavalue); ?>
                                    </th>
                                <?php
                                }
                                ?>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <?php
                                foreach ($datavalue as $childdatakey => $childdatavalue) {
                                ?>
                                    <th>
                                        <?php print_r($childdatavalue); ?>
                                    </th>
                                <?php
                                }
                                ?>
                            </tr>
                        </tfoot>

                    <?php
                    } else {
                    ?>
                        <tr>
                            <?php
                            foreach ($datavalue as $childdatakey => $childdatavalue) {
                            ?>
                                <td>
                                    <?php print_r($childdatavalue); ?>
                                </td>
                            <?php
                            }
                            ?>
                        </tr>
                <?php
                    }
                }
                ?>
            </table>
        </div>
    <?php
    }
}

// Authantication
function authentication()
{
    require 'authentication.php';

    $table_name = $wpdb->prefix . "googlesheetplugin_auth";
    $data = $wpdb->get_results("SELECT * FROM " . $table_name);
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <div>
        <div style="width: 100%;">
            <div class="row mx-0 m-5">
                <div class="col-md-12">
                    <div class="col-md-12">
                        <small><a href="https://console.developers.google.com">Create Google Sheet API Key</a></small>
                        <form method="post">
                            <div class="m-2">
                                <textarea rows="10" class="form-control" placeholder="Enter your google api client_secret" id="floatingTextarea" name="credential"><?php print_r($data[0]->name); ?></textarea>
                            </div>
                            <button class="m-2 btn btn-primary">Save</button>
                        </form>
                    </div>
                    <div class="col-md-12 bg-dark text-white disabled" style="-moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;" unselectable="on" onselectstart="return false;" onmousedown="return false;">

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}

function excel_import()
{
    global $wpdb;

    $table_name = $wpdb->prefix . "googlesheetplugin_auth";
    $data = $wpdb->get_results("SELECT * FROM " . $table_name);

    $spreadsheetID = $data[0]->name;

    if ($spreadsheetID == null) {
        header("location:admin.php?page=authentication");
    }

    if (class_exists('WooCommerce')) {
        // code that requires WooCommerce
    } else {
        echo "Woocommerce not installed";
        exit();
        // you don't appear to have WooCommerce activated
    }
    $table_name = $wpdb->prefix . "googlesheetplugin_sheets";
    $data = $wpdb->get_results("SELECT * FROM " . $table_name);

    if ($wpdb->num_rows) {
        if ($data[0]->name == "") {
            $data = "";
        }
    } else {
        $wpdb->insert($table_name, array('name' => ""));
    }
?>
    <!-- Latest compiled and minified CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <div>
        <div style="width: 100%;">
            <div class="row mx-0 m-5">
                <div class="col-md-6">
                    <div class="col-md-12">
                        <h4>Add your sheet</h4>
                    </div>
                    <div class="col-md-12">
                        <form class="card" id="import-excel" method="post">
                            <div class="mb-3">
                                <input type="hidden" name="type" value="google_sheet" />
                                <label class="form-label">Your Google Sheet</label>
                                <input type="text" value="<?php echo $data[0]->name; ?>" class="form-control" name="google_sheet">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                                <div class="col-md-6">
                                    <a class="btn btn-primary" href="admin.php?page=crud-data">Show Data</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4>Data</h4>
                    <?php if ($data[0]->name != "") { ?>
                        <div class="row m-0">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Excel Name</th>
                                        <th>Open</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>
                                            <?php echo $data[0]->name; ?>
                                        </td>
                                        <td>
                                            <a href="https://docs.google.com/spreadsheets/d/<?php echo $data[0]->name; ?>/edit#gid=0" class="btn btn-primary">Open</a>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>No.</th>
                                        <th>Excel Name</th>
                                        <th>Open</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php } else {
                        echo "No Records";
                    } ?>
                </div>
            </div>

            <div class="row mx-0 m-5">
                <div class="col-md-12">
                    <pre class="bg-dark text-white" id="response"></pre>
                </div>
            </div>
        </div>
    </div>
    <script>
        jQuery("#import-excel").submit(function(event) {
            // alert();

            jQuery("#response").html("Please wait");
            event.preventDefault();
            jQuery.ajax({
                type: "POST",
                url: "<?php echo get_dashboard_url(); ?>admin.php?page=importurl-api",
                data: jQuery(this).serialize(),
                success: function(n) {
                    jQuery("#response").html(n);
                }
            });
        });


        // jQuery("#show_sheetdata").click(function(event) {
        //     // alert();

        //     jQuery("#response").html("Please wait");
        //     event.preventDefault();
        //     jQuery.ajax({
        //         type: "get",
        //         url: "<?php echo get_dashboard_url(); ?>admin.php?page=show-sheetdata",
        //         data: jQuery(this).serialize(),
        //         success: function(n) {
        //             jQuery("#response").html(n);
        //         }
        //     });
        // });
    </script>
<?php

    register_deactivation_hook(__FILE__, 'google_sheet_plugin_remove_database');
    function google_sheet_plugin_remove_database()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "googlesheetplugin_auth";
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "googlesheetplugin_sheets";
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);

        $table_name = $wpdb->prefix . "googlesheetplugin_product";
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);

        delete_option("google_sheet_plugin_db_version");
    }
}

function importurl()
{

    global $wpdb;

    $post = $_POST;
    switch ($post['type']) {
        case 'google_sheet':
            if (empty($post['google_sheet'])) {
                echo "Empty field";
                exit();
            }

            $table_name = $wpdb->prefix . "googlesheetplugin_sheets";
            $wpdb->update($table_name, array('name' => $post['google_sheet']), array('id' => 1));

            echo "Google Sheet ID Updated";
            break;
        case 'status':

            $table_name = $wpdb->prefix . "googlesheetplugin_product";
            $data = $wpdb->get_results("SELECT * FROM " . $table_name);
            wp_update_post(array('ID' => $data[0]->name, 'post_status' => $post['status']));
            echo "Product Updated";
            break;

        case 'productdata':
            $table_name = $wpdb->prefix . "googlesheetplugin_product";
            $data = $wpdb->get_results("SELECT * FROM " . $table_name);

            wp_update_post(array('ID' => $data[0]->name, 'post_title' => $post['post_title']));
            update_post_meta($data[0]->name, '_price', $post['price']);
            echo "Product Updated";
            break;

        default:
            echo "Something is wrong";
            break;
    }
}


function showsheetdata()
{
    include 'config.php';

    global $wpdb;
    $table_name = $wpdb->prefix . "googlesheetplugin_sheets";
    $data = $wpdb->get_results("SELECT * FROM " . $table_name);

    $spreadsheetID = $data[0]->name;

    $range = "A:Z";

    $response = $service->spreadsheets_values->get($spreadsheetID, $range);

    $values = $response->getValues();

    if (empty($values)) {
        print "No data found.\n";
    } else {
        print_r($values);
    }
}

function product_manage()
{
    include('create_product.php');
}

?>