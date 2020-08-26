<?php get_header(); ?>

<div class="container">
	<div class="content-box content-404">
		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Not Found', 'directory-starter' ); ?></h1>
		</header>

		<div class="page-wrapper">
			<div class="page-content">
				<h2><?php esc_html_e( 'This is somewhat embarrassing, isn&rsquo;t it?', 'directory-starter' ); ?></h2>
				<p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try a search?', 'directory-starter' ); ?></p>

				<?php get_search_form(); ?>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>