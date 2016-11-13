<?php get_header(); ?>
<?php if ( have_posts() ) : ?>
	<h1><?php print get_the_archive_title(); ?></h1>
	<?php while ( have_posts() ) : the_post(); ?>
	<div class="row results">
		<?php if (has_post_thumbnail()) { ?>
		<div class="hidden-xs col-sm-2">
			<a href="<?php print get_permalink(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
		</div>
		<?php } ?>
		<div class="col-xs-12 <?php print (has_post_thumbnail()) ? ' col-sm-10' : ' col-sm-12'; ?>">
			<?php 
				$title = get_the_title(); 
				$keys= explode(" ",$s); 
				$title = preg_replace('/('.implode('|', $keys) .')/iu', '<strong class="search-excerpt">\0</strong>', $title); 
			?>
			<h3><a href="<?php print get_permalink(); ?>"><?php print $title; ?></a></h3>
			<?php
				$excerpt = get_the_excerpt();
			    $keys = implode('|', explode(' ', get_search_query()));
			    $excerpt = preg_replace('/(' . $keys .')/iu', '<strong class="search-highlight">\0</strong>', $excerpt);
			?>
			<p><a href="<?php print get_permalink(); ?>"><?php print $excerpt; ?></a></p>
			<p class="titlecase"><a href="<?php print get_permalink(); ?>">Click to View <?php print $post->post_title; ?></a></p>
		</div>
	</div>

	<?php endwhile; ?>
	<?php else : ?>
	<p class="results-count"><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
<?php endif; ?>

<?php get_footer(); ?>
