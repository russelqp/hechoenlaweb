<?php

/**
 * CSV mapping
 *
 * @package Directorist
 */

if (!defined('ABSPATH')) {
	exit;
}
$file = isset($_GET['file']) ? wp_unslash($_GET['file']) : '';
$delimiter = isset($_GET['delimiter']) ? wp_unslash($_GET['delimiter']) : '';
$posts = csv_get_data($file, true, $delimiter);
$total = count($posts);
$delimiter = isset($_GET['delimiter']) ? wp_unslash($_GET['delimiter']) : ',';
$update_existing = isset($_GET['update_existing']) ? sanitize_key($_GET['update_existing']) : false;
$headers = $args['data'];
$fields = $args['fields'];
?>
<div class="csv-wrapper">
	<div class="csv-center csv-fields">
		<form class="atbdp-progress-form-content directorist-importer" id="atbdp_csv_step_two" method="post">
			<header>
				<h2><?php esc_html_e('Map CSV fields to listings', 'directorist'); ?></h2>
				<p><?php esc_html_e('Select Directorist fields to map it against your CSV file fields, leave it as "Do not import" to skip certain fields.', 'directorist'); ?></p>
			</header>
			<div class="form-content">
				<section class="atbdp-importer-mapping-table-wrapper">
					<h3><?php printf(__('Total %s items selected ', 'directorist'), $total); ?></h3>
					<table class="widefat atbdp-importer-mapping-table">
						<thead>
							<tr>
								<th><?php esc_html_e('Column name', 'directorist'); ?></th>
								<th><?php esc_html_e('Map to field', 'directorist'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($headers as $index => $name) : ?>
								<tr>
									<td class="atbdp-importer-mapping-table-name">
										<p><?php echo esc_html($index); ?></p>
										<?php if (!empty($name)) : ?>
											<span class="description"><?php esc_html_e('Sample:', 'directorist'); ?> <code><?php echo esc_html($name); ?></code></span>
										<?php endif; ?>
									</td>
									<td class="atbdp-importer-mapping-table-field">
										<input type="hidden" name="map_from[<?php echo esc_attr($index); ?>]" value="<?php echo esc_attr($name); ?>" />
										<select class="atbdp_map_to" name="<?php echo esc_attr($index); ?>">
											<option value=""><?php esc_html_e('Do not import', 'woocommerce'); ?></option>
											<option value="">--------------</option>
											<?php foreach ($fields as $key => $value) : ?>
												<option value="<?php echo esc_attr($key); ?>" <?php // selected( $mapped_value, $key );
																								?>><?php echo esc_html($value); ?></option>
											<?php endforeach ?>
										</select>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</section>
			</div>
			<div class="atbdp-actions">
				<button type="submit" class="button btn-run-importer" value="<?php esc_attr_e('Run the importer', 'directorist'); ?>" name="save_step_two"><?php esc_html_e('Run the importer', 'directorist'); ?></button>
				<input type="hidden" name="file" value="<?php echo esc_attr($file); ?>">
				<input type="hidden" name="delimiter" value="<?php echo esc_attr($delimiter); ?>" />
				<input type="hidden" name="update_existing" value="<?php echo $update_existing; ?>" />
				<?php wp_nonce_field('directorist-csv-importer'); ?>
			</div>
		</form>
	</div>
	<div class="csv-center">
		<div class="directorist-importer__importing" style="display: none;">
			<header>
				<span class="spinner is-active"></span>
				<h2><?php esc_html_e( 'Importing', 'directorist' );
					?></h2>
				<p><?php esc_html_e( 'Your listing are now being imported...', 'directorist' );
					?></p>
			</header>
			<section>
				<span class="importer-notice"><?php esc_html_e('Please don\'t reload the page', 'directorist')?></span>
				<progress class="directorist-importer-progress" max="100" value="0"></progress>
				<span class="importer-details"></span>
			</section>
		</div>
	</div>
</div>