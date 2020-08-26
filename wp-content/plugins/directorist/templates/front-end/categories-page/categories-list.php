<?php

/**
 * This template displays the ACADP categories list.
 */
?>

    <div id="directorist" class="atbd_wrapper atbdp atbdp-categories atbdp-text-list">
    <?php
		$span = 'col-md-' . floor( 12 /  $categories_settings['columns'] );
		--$categories_settings['depth'];
		$i = 0;
		foreach( $terms as $term ) {
            $parent = $args['parent'];
            $categories_settings['term'] = $term;
			$categories_settings['term_id'] = $term->term_id;
            $child_category = get_term_children($term->term_id,ATBDP_CATEGORY);
            $plus_icon = !empty($child_category) ? '<span class="expander">+</span>' : '';

			$count = 0;
			if( ! empty( $categories_settings['hide_empty'] ) || ! empty( $categories_settings['show_count'] ) ) {
				$count = atbdp_listings_count_by_category( $term->term_id );

				if( ! empty( $categories_settings['hide_empty'] ) && 0 == $count ) continue;
			}

			if( $i % $categories_settings['columns'] == 0 ) {
				echo '<div class="row atbdp-no-margin">';
			}

			echo '<div class="' . $span . '"><div class="atbd_category_wrapper">';
			echo '<a href=" ' .ATBDP_Permalink::atbdp_get_category_page($term) . ' " class="atbd_parent_cat">';
			echo '<span>' . $term->name . '</span>';
			if( ! empty( $categories_settings['show_count'] ) ) {
                $expired_listings = atbdp_get_expired_listings(ATBDP_CATEGORY, $term->term_id);
                $number_of_expired = $expired_listings->post_count;
                $number_of_expired = !empty($number_of_expired)?$number_of_expired:'0';
                $totat = ($count)?($count-$number_of_expired):$count;
				echo ' (' .  $totat . ')';
			}
			echo "</a>$plus_icon";
			echo atbdp_list_categories( $categories_settings );
			echo '</div></div>';

			$i++;

			if( $i % $categories_settings['columns'] == 0 || $i == count( $terms ) ) {
				echo '</div>';
			}
		}
	?>
</div>
