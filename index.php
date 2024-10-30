<?php
/**
Plugin Name: Cool Like - DisLike
Plugin URI: https://wordpress.org/plugins/cool-like-dislike/
Description: Плагин организует систему кнопок "Нравится" и "Не нравится"
Version: 1.0.0
Author: Boris Kotlyarov
Author URI: https://github.com/BorisKotlyarov
*/

define('LDIS_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('LDIS_PLUGIN_FILE', __FILE__);

class LikeDisLike {
	function __construct() { }
	# Функция преобразования числа в ip
	function int2ip($i) {
	   $d[0]=(int)($i/256/256/256);
	   $d[1]=(int)(($i-$d[0]*256*256*256)/256/256);
	   $d[2]=(int)(($i-$d[0]*256*256*256-$d[1]*256*256)/256);
	   $d[3]=$i-$d[0]*256*256*256-$d[1]*256*256-$d[2]*256;
	   return "$d[0].$d[1].$d[2].$d[3]";
	}
	# Функция преобразования ip в число
	function ip2int($ip) {
	   $a=explode(".",$ip);
	   return $a[0]*256*256*256+$a[1]*256*256+$a[2]*256+$a[3];
	}
	# Функция подсчета лайков или дизлайков
	function getcountLike($post_id, $like, $ip="") {
		global $wpdb;
		if($ip!=""){ $ip_sql = " AND `ip`='{$ip}'"; } else { $ip_sql =""; }
		if($like==0){ $like_sql ="";} else { $like_sql = " `like_dislike`='{$like}' AND";  }
		$getcountLike = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}likedislike` WHERE{$like_sql} `post_id`='{$post_id}'{$ip_sql} ");
		return intval($getcountLike);
	}
	# Функция добавления лайка или дизлайка
	function add_vote($post_id, $vote, $ip){
		global $wpdb;
		
		$userVote = self::getcountLike($post_id, 0, $ip);
		if($userVote==0) {
			$add_vote = $wpdb->insert(
				"{$wpdb->prefix}likedislike",
				array( 'post_id' => $post_id, 'like_dislike' => $vote, 'ip' => $ip   ),
				array( '%d', '%s', '%d'  )
			);
			$TVote = self::getcountLike($post_id, $vote);
		} else {
			$TVote = self::getcountLike($post_id, $vote);
		}
		return $TVote;
	}
	# Функция определения реального ip адреса
	function getRealIP() {
		if( $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) {
			$client_ip = ( !empty($_SERVER['REMOTE_ADDR']) ) ? $_SERVER['REMOTE_ADDR']:( ( !empty($_ENV['REMOTE_ADDR']) ) ? $_ENV['REMOTE_ADDR'] : "unknown" );
			$entries = @split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
			reset($entries);
			while (list(, $entry) = each($entries)) {
				$entry = trim($entry);
				if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list) ) { 
					$private_ip = array(
						'/^0\./',
						'/^127\.0\.0\.1/',
						'/^192\.168\..*/',
						'/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
						'/^10\..*/');
					$found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);
					if ($client_ip != $found_ip) {
						$client_ip = $found_ip;
						break;
					}
				}
			}
		} else {
			$client_ip = ( !empty($_SERVER['REMOTE_ADDR']) ) ? $_SERVER['REMOTE_ADDR'] : ( ( !empty($_ENV['REMOTE_ADDR']) ) ? $_ENV['REMOTE_ADDR'] : "unknown" );
		}
		return $client_ip;
	}
	# Функция установки плагина
	function install(){
		global $wpdb;
		/*Если не устанавливается таблица проверь константу LDIS_PLUGIN_FILE в register_activation_hook(LDIS_PLUGIN_FILE, array('LikeDisLike', 'install')); */
		$sql_path_file=LDIS_PLUGIN_DIR.'install.sql';
		
		if(file_exists($sql_path_file)){
			$sql = file_get_contents($sql_path_file);
		} else {
			echo 'Сбой при установке плагина. Не найден файл SQL.'; exit;
		}
		
		if (empty($sql)) { die('Сбой при установке плагина. SQL Файл пуст.'); }
			$sql = str_replace('_PREFIX_', $wpdb->prefix, $sql);
			$sql = explode(';', trim($sql, ';'));
			if(count($sql)) {
				foreach ($sql as $_sql) {
					if (!$wpdb->query($_sql)) {
						echo 'Сбой при установке плагина. Ошибка SQL.'; exit;
					}
				}
			}
	}
	# Функция удаления плагина
	function uninstall(){
		global $wpdb;
		$sql = "DROP TABLE `{$wpdb->prefix}likedislike` ;";
		$wpdb->query($sql);
	}
}

# Активация и загрузка виджетов
include( LDIS_PLUGIN_DIR.'/widgets/likedislike.php' );
add_action('widgets_init', create_function('', 'return register_widget("LikeDisLikeWidget");'));

# POST события
if($_POST['likedislike']!="" AND $_POST['post_id'] AND $_POST['like']){
	$post_id = intval($_POST['post_id']);
	$vote = intval($_POST['like']);
	$ip = LikeDisLike::ip2int(LikeDisLike::getRealIP());
	echo LikeDisLike::add_vote($post_id, $vote, $ip);
	exit();
}

# Хуки
register_activation_hook(LDIS_PLUGIN_FILE, array('LikeDisLike', 'install'));
register_deactivation_hook(plugin_basename(LDIS_PLUGIN_FILE), array('LikeDisLike', 'uninstall'));
