<?php
/*
Plugin Name: Mihanblog importer
*/
class mihanblog{
	public $backup_text;
	public $errorMessage;
	function import(){
		$posts = $this->parse_backup($this->backup_text);
		if(empty($posts['body']['string']['post'])){
		    $this->errorMessage = "پستی یافت نشد";
		    return false;
		}
		else{
			echo "<br>در حال وارد کردن پست ها<br>";
			foreach ($posts['body']['string']['post'] as $post) {
				$postarr = array();;

				$postarr["post_title"]=$post["title"];
				$postarr["post_content"]=$post["body"];
				$postarr["post_date"]=$post["create_date"];
				$postarr["post_status"]="publish";

				echo $post["title"]."<br>";
				
				wp_insert_post( $postarr, $wp_error);
				if($wp_error){
					$this->errorMessage .= "خطا در ثبت پست ".$post["title"];
					return false;
				}
			} 

			return true;
		}
	}

	function parse_backup(){
		$data_array = explode("%", $this->backup_text, 3);
		
		$file_name = trim($data_array[1]);

		$json = unserialize(trim($data_array[2]));
		$sub_json = unserialize($json['body']['string']);
		$json['body']['string'] = $sub_json;
		return $json;
	}

}

add_action('admin_menu', 'my_menu');
function my_menu() {
    //add_menu_page('My Page Title', 'My Menu Title', 'manage_options', 'my-page-slug', 'my_function');
    add_menu_page( "Mihanblog importer", "میهن بلاگ", "manage_options", "import_page", 'showpage', "http://static.mihanblog.com//public/images/icon/favicon2.gif" );
}

function showpage(){
	if($_FILES["mihanblog_file"]){
		//upload 
		if ( ! function_exists( 'wp_handle_upload' ) ) {
		    require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$uploadedfile = $_FILES['mihanblog_file'];

		$upload_overrides = array( 'test_form' => 0 );

		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			$mihan = new mihanblog();
			$mihan->backup_text = file_get_contents($movefile["file"]);
			if($mihan->import() )
				echo "تمامی پست های درون فایل به وردپرس درون ریزی شدند";
			else{
				echo "<br><span style='color:red;'>".$mihan->errorMessage."</span>";
				echo file_get_contents(__DIR__. "/main.html");
			}
		} else {
		    /**
		     * Error generated by _wp_handle_upload()
		     * @see _wp_handle_upload() in wp-admin/includes/file.php
		     */
		    echo "<br><span style='color:red;'>". $movefile['error']."</span>";
		    echo file_get_contents(__DIR__. "/main.html");
		}

	}
	else
		echo file_get_contents(__DIR__. "/main.html");
}