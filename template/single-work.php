<?php get_header(); ?>
	<div class="col12" id="content">
		<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
		<div <?php post_class( '' ); ?> id="post-<?php the_ID(); ?>">
				<?php the_post_thumbnail( 'full' ); ?>
			<h1 class="title"><?php the_title(); ?></h1>
			<div class="postinfo">
				<?php echo get_the_term_list( $post->ID, 'work_type', 'Type: ', ', ', '' ); ?> | <?php if ( get_post_meta( get_the_ID(), '_year', true ) ) { ?>Year: <?php echo get_post_meta($post->ID, "_year", true); ?> | <?php } ?> <?php if ( get_post_meta( get_the_ID(), '_industry', true ) ) { ?>Industry: <?php echo get_post_meta($post->ID, "_industry", true); ?> | <?php } ?> <?php if ( get_post_meta( get_the_ID(), '_website', true ) ) { ?><a href="<?php echo get_post_meta($post->ID, "_website", true); ?>" target="_blank">View Site &rarr;</a><?php } ?>
			</div><!-- END .workinfo -->
			<?php the_content(); ?>
		</div><!-- END .post -->
		<?php endwhile; ?>
		<?php else : ?>
		<?php endif; ?>
	</div><!-- END .col12 #content -->
<?php get_footer(); ?>