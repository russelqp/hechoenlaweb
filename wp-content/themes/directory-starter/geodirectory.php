<?php
/**
 * Template Name: GD Page
 */
get_header();

do_action('dt_page_before_main_content'); 

$dt_blog_sidebar_position = esc_attr(get_theme_mod('dt_blog_sidebar_position', DT_BLOG_SIDEBAR_POSITION));
?>
<div class="fullwidth-sidebar-container">
	<div class="sidebar top-sidebar">
		<?php dynamic_sidebar('sidebar-gd-top'); ?>
	</div>
</div>

<div class="container">

	<div class="row">
	<?php if ($dt_blog_sidebar_position == 'left') { ?>
		<div class="col-lg-4 col-md-3">
			<div class="sidebar blog-sidebar page-sidebar">
				<?php dynamic_sidebar('sidebar-gd'); ?>
			</div>
		</div>
	<?php } ?>
	<div class="col-lg-8 col-md-9">
		<div class="content-box content-single">
			<?php if (!have_posts()) : ?>
				<div class="alert alert-warning">
					<?php _e('Sorry, no results were found.', 'directory-starter'); ?>
				</div>
				<?php get_search_form(); ?>
			<?php endif; ?>
			<?php
			while ( have_posts() ) : the_post();

				// Include the page content template.
				get_template_part( 'template-parts/content/content' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

				// End the loop.
			endwhile;
			?>
		</div>
	</div>
	<?php if ($dt_blog_sidebar_position == 'right') { ?>
		<div class="col-lg-4 col-md-3">
			<div class="sidebar blog-sidebar page-sidebar">
				<?php dynamic_sidebar('sidebar-gd'); ?>
			</div>
		</div>
	<?php } ?>
	</div>
</div>

<div class="fullwidth-sidebar-container">
	<div class="sidebar bottom-sidebar">
		<?php dynamic_sidebar('sidebar-gd-bottom'); ?>
	</div>
</div>

<?php do_action('dt_page_after_main_content'); ?>

<?php get_footer(); ?>