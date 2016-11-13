<?php get_header(); ?>
<?php query_posts(array('posts_per_page'=>-1, 'meta_key' => 'votes', 'orderby' => 'meta_value_num', 'order' => 'DESC')); ?>
<div class="row">
  <div class="col-xs-12 col-sm-6 col-sm-offset-3">
    <h2>Questions</h2>
    <div id="question_area"<?php if (!get_option('questions_enabled')) : print ' class="hider"'; endif; ?>>
      <textarea class="form-control" placeholder="Type your question here ..."></textarea>
      <a href="javascript://" class="btn btn-primary">Submit question</a>
    </div>
  </div>
</div>
<div id="questions_list"<?php if (!get_option('voting_enabled')) : print ' class="voting_disabled"'; endif; ?>>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
  <?php $vote_total = get_post_meta( $post->ID, 'votes', true ); ?>
  <div class="row post" data-post-id="<?php print $post->ID; ?>" data-votes="<?php print $vote_total; ?>">
    <div class="col-xs-12 col-sm-8 col-sm-offset-2">
      <div class="row">
         <div class="content-centered">
           <a href="javascript://" class="downvote">-</a>
           <span><?php print $vote_total; ?></span>
           <a href="javascript://" class="upvote">+</a>
        </div>
        <h3><?php the_title(); ?></h3>
      </div>
    </div>
  </div>
<?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>
