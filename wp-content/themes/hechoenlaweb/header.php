<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equip="X-UA-Compatible" content="ie=edge">
	<?php wp_head() ?>
</head>
<body>
	<header>
		<section class="container">
			<div class="logo"><img src="<?php echo get_template_directory_uri()?>/assets/img/logo.jpg" alt="logo-hechoenlaweb"></div>
			<div class="nav">
				<?php wp_nav_menu(
					array(
						'theme_location' => 'top_menu',
						'menu_class' => 'menu-principal',
						'container_class' => 'container-menu',
					)
				); ?>
			</div>
		</section>
	</header>