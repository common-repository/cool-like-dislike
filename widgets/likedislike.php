<?php

class LikeDisLikeWidget extends WP_Widget {
	function LikeDisLikeWidget() {
		load_plugin_textdomain( 'LikeDisLikeWidget', false, dirname( plugin_basename( __FILE__ ) ) );
		$widget_ops = array('classname' => 'widget_LikeDisLikeWidget', 'description' => 'Виджет показывает кнопки Like и Dislike для поста');
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('widget_LikeDisLikeWidget', 'Like - DisLike', $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		echo $before_widget;
		echo self::LikeDisLikeWidget_start();			
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance = $new_instance;
		return $instance;
	}

	function form( $instance ) {

		echo '<p>Данный виджет не имеет настроек</p>';

	}
	
	
	function getcountLike($post_id, $like) {
		global $wpdb;
			$getcountLike = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}likedislike` WHERE `like_dislike`='{$like}' AND `post_id`='{$post_id}' ");
			return $getcountLike;
	}

	function getLikes($post_id) {
		global $wpdb;
		return self::getcountLike($post_id, 1);
	}

	function getDisLikes($post_id) {
		global $wpdb;
		return self::getcountLike($post_id, 2);
	}

	
	function LikeDisLikeWidget_start() {
		global $wpdb;
		$post_id =get_the_ID();

		$ip = LikeDisLike::ip2int(LikeDisLike::getRealIP());
		$likes = intval(self::getLikes($post_id));
		$DisLikes = intval(self::getDisLikes($post_id));

		$html = <<<html
<svg style="position: absolute; width: 0; height: 0;" width="0" height="0" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
<defs>
<symbol id="like-thumbs-up" viewBox="0 0 1024 1024">
	<title>thumbs-up</title>
	<path class="path1" d="M928 576c73 0 32 192-32 192 32 0 0 160-64 160 0 64-64 96-128 96-270.352 0-175.264-67.65-448-96v-512c240.922-72.268 480-253.424 480-416 53 0 192 64 0 384 0 0 160 0 192 0 96 0 64 192 0 192zM192 416v512h64v32h-128c-35.2 0-64-43.2-64-96v-384c0-52.8 28.8-96 64-96h128v32h-64z"></path>
</symbol>
<symbol id="like-thumbs-up2" viewBox="0 0 1024 1024">
	<title>thumbs-up2</title>
	<path class="path1" d="M96 448c-73 0-32-192 32-192-32 0 0-160 64-160 0-64 64-96 128-96 270.352 0 175.264 67.65 448 96v512c-240.922 72.268-480 253.424-480 416-53 0-192-64 0-384 0 0-160 0-192 0-96 0-64-192 0-192zM832 608v-512h-64v-32h128c35.2 0 64 43.2 64 96v384c0 52.8-28.8 96-64 96h-128v-32h64z"></path>
</symbol>
<symbol id="like-spinner" viewBox="0 0 1024 1024">
	<title>spinner</title>
	<path class="path1" d="M384 128c0-70.692 57.308-128 128-128s128 57.308 128 128c0 70.692-57.308 128-128 128s-128-57.308-128-128zM655.53 240.47c0-70.692 57.308-128 128-128s128 57.308 128 128c0 70.692-57.308 128-128 128s-128-57.308-128-128zM832 512c0-35.346 28.654-64 64-64s64 28.654 64 64c0 35.346-28.654 64-64 64s-64-28.654-64-64zM719.53 783.53c0-35.346 28.654-64 64-64s64 28.654 64 64c0 35.346-28.654 64-64 64s-64-28.654-64-64zM448.002 896c0 0 0 0 0 0 0-35.346 28.654-64 64-64s64 28.654 64 64c0 0 0 0 0 0 0 35.346-28.654 64-64 64s-64-28.654-64-64zM176.472 783.53c0 0 0 0 0 0 0-35.346 28.654-64 64-64s64 28.654 64 64c0 0 0 0 0 0 0 35.346-28.654 64-64 64s-64-28.654-64-64zM144.472 240.47c0 0 0 0 0 0 0-53.019 42.981-96 96-96s96 42.981 96 96c0 0 0 0 0 0 0 53.019-42.981 96-96 96s-96-42.981-96-96zM56 512c0-39.765 32.235-72 72-72s72 32.235 72 72c0 39.765-32.235 72-72 72s-72-32.235-72-72z"></path>
</symbol>
</defs>
</svg>

<div class="LikeDisLikeBlock">
	<div class="LikeDisLikeRow">
		<div class="LikeDisLikeCell"><span class="setLike"><font color="green"><svg class="like-thumbs-up"><use xlink:href="#like-thumbs-up"></use></svg></font> Нравится</span> <span class="setsLike">{$likes}</span></div>
		<div class="LikeDisLikeCell"><span class="setDisLike"><font color="red"><svg class="like-thumbs-up2"><use xlink:href="#like-thumbs-up2"></use></svg></font> Не нравится</span> <span class="setsDisLike">{$DisLikes}</span></div>
	</div>
</div>

<script>

function sendLike( like, curClass ) {
	$( "."+curClass ).html('<div class="loadingLike"><svg class="like-spinner"><use xlink:href="#like-spinner"></use></svg></div>');
	setTimeout(function () {
		$.post( "index.php", { likedislike: "1", post_id: "{$post_id}", like: ""+like }, function( data ) {
			$( "."+curClass ).html(''+data);
		});
	},1000);
}

$('.setLike').click(function () {
	sendLike( 1, "setsLike" );
});
$('.setDisLike').click(function () {
	sendLike( 2, "setsDisLike" );
});

</script>

<style>
[class^="like-"], [class*=" like-"] {
	display: inline-block;
	width: 1em;
	height: 1em;
	fill: currentColor;
}

.LikeDisLikeBlock {display: table; margin-top: 10px; border-spacing: 2px 0px;}
.LikeDisLikeRow {display: table-row;}
.LikeDisLikeCell {display: table-cell; text-align: center; padding: 5px;}
.LikeDisLikeCell:first-child { background: #DBFFD6; }
.LikeDisLikeCell:last-child { background: #FFE4E4; }
.setsLike, .setsDisLike {font-weight: 700;}
.setLike, .setDisLike {cursor: pointer;}
.loadingLike {

}
.loadingLike {
	width: 15px;	height: 15px;	margin-left: auto;	margin-right: auto;	display: inline-block;    text-align: center;   -webkit-animation: spin 2s linear 0s infinite normal;    -moz-animation: spin 2s linear 0s infinite normal;    -o-animation: spin 2s linear 0s infinite normal;    animation: spin 0.7s linear 0s infinite normal;
   }

   @-webkit-keyframes spin { from { -webkit-transform: rotate(0deg); } to { -webkit-transform: rotate(360deg); } }
   @-moz-keyframes spin { from { -moz-transform: rotate(0deg); } to { -moz-transform: rotate(360deg); } }
   @-o-keyframes spin { from { -o-transform: rotate(0deg); } to { -o-transform: rotate(360deg); } }
   @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }


</style>


html;

		return $html;
	}
	
}
