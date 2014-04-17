<?php get_header(); ?>
	<?php if (have_posts()) : ?>
	<?php while (have_posts()) : the_post(); ?>
	<div class="col6">
		<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'work' ); ?></a>
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<div class="postinfo">
				<?php echo get_the_term_list( $post->ID, 'work_type', 'Type: ', ', ', '' ); ?> | <?php if ( get_post_meta( get_the_ID(), '_year', true ) ) { ?>Year: <?php echo get_post_meta($post->ID, "_year", true); ?> | <?php } ?> <?php if ( get_post_meta( get_the_ID(), '_industry', true ) ) { ?>Industry: <?php echo get_post_meta($post->ID, "_industry", true); ?> | <?php } ?> <?php if ( get_post_meta( get_the_ID(), '_website', true ) ) { ?><a href="<?php echo get_post_meta($post->ID, "_website", true); ?>" target="_blank">View Site &rarr;</a><?php } ?>
			</div><!-- END .workinfo -->
	</div>		
	<?php endwhile; ?>
	<div class="post" id="pagenavi">
		<?php include('inc/wp-pagenavi.php'); if(function_exists('wp_pagenavi')) { wp_pagenavi(); } ?>
	</div><!-- END .post -->
	<?php else : ?>
	<div class="post">
		<h1><?php _e( 'No results were found', 'clarity-theme' ); ?></h1>
			<p><?php _e( 'Sorry, but you are looking for something that is not here', 'clarity-theme' ); ?></p>
	</div><!-- END .post -->
	<?php endif; ?>
<?php get_footer(); ?>