<?php

include 'config.php';
global $wpdb;

$table_name = $wpdb->prefix . "googlesheetplugin_auth";
$data = $wpdb->get_results("SELECT * FROM " . $table_name);

$spreadsheetID = $data[0]->name;

if ($spreadsheetID == null) {
	header("location:admin.php?page=authentication");
}





$table_name = $wpdb->prefix . "googlesheetplugin_product";
$data = $wpdb->get_results("SELECT * FROM " . $table_name);


if ($wpdb->num_rows) {
	if ($_POST) {

		// Then we use the product ID to set all the posts meta
		wp_set_object_terms($post_id, 'simple', 'product_type'); // set product is simple/variable/grouped
		update_post_meta($post_id, '_visibility', 'visible');
		update_post_meta($post_id, '_stock_status', 'instock');
		update_post_meta($post_id, 'total_sales', '0');
		update_post_meta($post_id, '_downloadable', 'no');
		update_post_meta($post_id, '_virtual', 'yes');
		update_post_meta($post_id, '_regular_price', '');
		update_post_meta($post_id, '_sale_price', '');
		update_post_meta($post_id, '_purchase_note', '');
		update_post_meta($post_id, '_featured', 'no');
		update_post_meta($post_id, '_weight', '');
		update_post_meta($post_id, '_length', '');
		update_post_meta($post_id, '_width', '');
		update_post_meta($post_id, '_height', '');
		update_post_meta($post_id, '_sku', 'google_sheet_data');
		update_post_meta($post_id, '_product_attributes', array());
		update_post_meta($post_id, '_sale_price_dates_from', '');
		update_post_meta($post_id, '_sale_price_dates_to', '');
		update_post_meta($post_id, '_sold_individually', '');
		update_post_meta($post_id, '_manage_stock', 'yes'); // activate stock management
		update_post_meta($post_id, '_backorders', 'no');

		// Price
		update_post_meta($post_id, '_price', '11');
		// Stock
		wc_update_product_stock($post_id, 100, 'set'); // set 1000 in stock
	}
} else {
	$post_id = create_Products_Programmatically();
	$wpdb->insert($table_name, array('name' => $post_id));
}
$post_id = $data[0]->name;
if ($post_id) {
	$postmetadata = get_post_meta($post_id);
	$postdata = get_post($post_id);
} else {
	echo "Something is wrong";
	exit();
}
// print_r($postmetadata);
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
<div class="container">
	<div class="row m-0">
		<div class="col-12">
			<h1>Sheet Product</h1>
		</div>
		<div class="col-12">
			<div class="bg-dark text-white col-6" id="response"></div>
		</div>
		<div class="col-6">
			<div class="offset-col-6 card">
				<form id="productstatus" method="post">
					<input type="hidden" name="type" value="status" />
					<?php if ($postdata->post_status == 'draft') { ?>
						<input type="hidden" name="status" value="publish" />
						<button class="btn btn-danger">Deactived</button>
					<?php } else { ?>
						<input type="hidden" name="status" value="draft" />
						<button class="btn btn-success">Activated</button>
					<?php } ?>
				</form>
				<form id="productdata">
					<input type="hidden" name="type" value="productdata" />
					<div class="mb-3">
						<label for="InputPostTitle" class="form-label">Product Name</label>
						<input type="text" value="<?php echo $postdata->post_title; ?>" name="post_title" class="form-control" id="InputPostTitle">
					</div>
					<div class="mb-3">
						<label for="InputPostTitle" class="form-label">Price</label>
						<input type="text" name="price" class="form-control" id="InputPostTitle" value="<?php echo $postmetadata['_price'][0]; ?>" />
					</div>
					<div class="mb-3">
						<label for="InputPostTitle" class="form-label">Stock</label>
						<input type="text" disabled value="<?php echo (int) $postmetadata['_stock'][0]; ?>" name="stock" class="form-control" id="InputPostTitle">
					</div>
					<button type="submit" class="btn btn-primary">Submit</button>
				</form>
				<script>
					jQuery("#response").hide();
					jQuery("#productstatus").submit(function(event) {
						jQuery("#response").show();
						jQuery("#response").html("Please wait");
						event.preventDefault();
						jQuery.ajax({
							type: "POST",
							url: "<?php echo get_dashboard_url(); ?>admin.php?page=importurl-api",
							data: jQuery(this).serialize(),
							success: function(n) {
								if (n == "Product Updated") {
									setTimeout(() => {
										window.location.href = window.location.href;
									}, 1000);
								}
								jQuery("#response").html(n);
							}
						});
					});


					jQuery("#productdata").submit(function(event) {
						jQuery("#response").show();
						jQuery("#response").html("Please wait");
						event.preventDefault();
						jQuery.ajax({
							type: "POST",
							url: "<?php echo get_dashboard_url(); ?>admin.php?page=importurl-api",
							data: jQuery(this).serialize(),
							success: function(n) {
								if (n == "Product Updated") {
									setTimeout(() => {
										window.location.href = window.location.href;
									}, 1000);
								}
								jQuery("#response").html(n);
							}
						});
					});
				</script>
			</div>
		</div>
	</div>
	<?php

	function create_Products_Programmatically()
	{

		// Set number of products to create
		$number_of_products = 1;

		for ($i = 1; $i <= $number_of_products; $i++) {
			// First we create the product post so we can grab it's ID 
			$post_id = wp_insert_post(
				array(
					'post_title' => 'Product ' . $i,
					'post_type' => 'product',
					'post_status' => 'publish'
				)
			);

			// Then we use the product ID to set all the posts meta
			wp_set_object_terms($post_id, 'simple', 'product_type'); // set product is simple/variable/grouped
			update_post_meta($post_id, '_visibility', 'visible');
			update_post_meta($post_id, '_stock_status', 'instock');
			update_post_meta($post_id, 'total_sales', '0');
			update_post_meta($post_id, '_downloadable', 'no');
			update_post_meta($post_id, '_virtual', 'yes');
			update_post_meta($post_id, '_regular_price', '');
			update_post_meta($post_id, '_sale_price', '');
			update_post_meta($post_id, '_purchase_note', '');
			update_post_meta($post_id, '_featured', 'no');
			update_post_meta($post_id, '_weight', '11');
			update_post_meta($post_id, '_length', '11');
			update_post_meta($post_id, '_width', '11');
			update_post_meta($post_id, '_height', '11');
			update_post_meta($post_id, '_sku', 'SKU11');
			update_post_meta($post_id, '_product_attributes', array());
			update_post_meta($post_id, '_sale_price_dates_from', '');
			update_post_meta($post_id, '_sale_price_dates_to', '');
			update_post_meta($post_id, '_price', '11');
			update_post_meta($post_id, '_sold_individually', '');
			update_post_meta($post_id, '_manage_stock', 'yes'); // activate stock management
			wc_update_product_stock($post_id, 100, 'set'); // set 1000 in stock
			update_post_meta($post_id, '_backorders', 'no');
		}

		return $post_id;
	}
