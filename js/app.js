jQuery(document).ready(function() {
  //INTIIAL SETUP
  var current_votes = Cookies.get('voted_on');
  
  if (current_votes) {
    current_votes = JSON.parse(current_votes);
    if (current_votes.length > -1) {
      set_votes();      
    }
  } else {
    current_votes = [];
    Cookies.set('voted_on',current_votes);
  }
  
  function set_votes() {
    for (var i = 0; i < current_votes.length; i++) {
      jQuery('div.post[data-post-id="'+current_votes[i]+'"]').addClass('voted');
    }
  }
  
  //PUSH NOTIFICATIONS
  window.pusher = new Pusher('3971f6d574713b040eae', {
    encrypted: true
  });

  window.channel = pusher.subscribe('wp_control');
  channel.bind('new_post', function(data) {
    var post = new wp.api.models.Post( { id: data } );
    post.fetch().done(function(post) {
       var new_post = jQuery('div.post').eq(0).clone().hide().attr('data-post-id',data).attr('data-votes',1);
       new_post.find('h3').html(post.title.rendered);
       new_post.find('span').text(1);
       new_post.data('post-id',data).data('data-votes',1);
       new_post.removeClass('voted').insertBefore(jQuery('div.post').eq(0)).fadeIn();
    });
  });
  
  channel.bind('new_vote',function(id) {
    api_voting('current',id);
  });
  
  channel.bind('settings_change',function(data) {
    var target = (data.setting === 'questions') ? jQuery('#question_area') : jQuery('.post:not(.voted) a');
    if (data.value === "1") {
      target.fadeIn(); 
    } else {
      target.fadeOut(); 
    }
  });
  
  //VOTING
  ///api calls
  function api_voting(action,id) {
    var settings = {
      "async": true,
      "crossDomain": true,
      "url": "http://phpworld16.thedanherman.com/wp-json/phpworld/vote/"+action+"/"+id + '?'+ new Date().getTime(),
      "method": "GET",
      "headers": {
        "authorization": "Basic ZXZlcnlvbmU6UnlTeSB1c3J0IDJ4TEggUGg1cg==",
        "cache-control": "no-cache",
      },
    };

    jQuery.ajax(settings).done(function (response) {
      if (action === 'current') {
        update_post_vote_count(id,response);
      }
    });
  }
  
  ///frontend display
  function update_post_vote_count(id,vote_total) {
    var div = jQuery('div.post[data-post-id="'+id+'"]');
    div.find('span').text(vote_total);
    div.data('votes',vote_total).attr('data-votes',vote_total);
    shuffle_posts_by_vote();
  }
  
  function shuffle_posts_by_vote() {
    var divList = jQuery("div.post");
    divList.sort(function(a, b){
        return jQuery(b).data("votes")-jQuery(a).data("votes");
    });
    jQuery("#questions_list").html(divList);
  }
  
  //register local vote to make sure people don't vote in the same direction twice for the same post
  function register_vote(id) {
    current_votes.push(id);
    Cookies.set('voted_on',current_votes);
    set_votes();
  }
  
  //CLICK HANDLERS
  ///Gotta use body on click, because we remove and re-add everything when we shuffle after voting
  jQuery('body').on('click','.post a',function() {
    var type = (jQuery(this).hasClass('upvote')) ? 'up' : 'down';
    var id = jQuery(this).parents('.post').data('post-id');
    api_voting(type,id);
    register_vote(id);
  });

  jQuery('.btn.btn-primary').on('click',function() {
    if (jQuery('textarea').val()) {
     jQuery('.btn.btn-primary').attr('disabled',true);
      var settings = {
        "async": true,
        "crossDomain": true,
        "url": "http://phpworld16.thedanherman.com/wp-json/wp/v2/posts?status=publish&title="+encodeURIComponent(jQuery('textarea').val()),
        "method": "POST",
        "headers": {
          "authorization": "Basic ZXZlcnlvbmU6UnlTeSB1c3J0IDJ4TEggUGg1cg==",
          "cache-control": "no-cache",
          "postman-token": "94a75cdb-0ca9-93c8-758b-97777928c5f9"
        }
      }

      window.ajax = jQuery.ajax(settings).done(function (response) {
        jQuery('textarea').val('');
        jQuery('.btn.btn-primary').attr('disabled',false);
      });
    }
  });
});