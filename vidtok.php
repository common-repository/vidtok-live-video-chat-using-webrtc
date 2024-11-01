<?php
/*
Plugin Name: 	Vidtok Live Video Chat using WebRTC
Plugin URI: 	http://vidtok.com/
Description: 	Vidtok allows any wordpress install to add live video chat to their website or blog. Vidtok will allow you to start video chats from three separate view types: overlay, widget or embeded 
Version: 		2.0 
Author: 		the Blacc Spot Media team
Author URI: 	http://blaccspot.com
License: 		GPLv3 http://www.gnu.org/licenses/gpl.html
*/


/*  DEFINE CONSTANTS
/*---------------------------*/	

	/*VARIABLES*/
		$url = str_replace('www.', '', parse_url(site_url())); 

	/*WORDPRESS VERSION*/
		define("WORDPRESS_VERSION", get_bloginfo('version'));
		
	/*VIDTOK VERSION*/
		define("VIDTOK_VERSION", "2.0");

	/*DOMAIN*/	
		define("DOMAIN", $url['host']);
	
	/*PLUGIN PATH*/
		define("VIDTOK_PLUGINPATH", "/" . plugin_basename(dirname(__FILE__)) . "/");
	
	/*PLUGIN FULL URL*/
		define("VIDTOK_PLUGINFULLURL", trailingslashit(plugins_url(null, __FILE__ )));
	
	/*PLUGIN FULL DIRECTORY*/
		define("VIDTOK_PLUGINFULLDIR", WP_PLUGIN_DIR . VIDTOK_PLUGINPATH);
		
	/*PLUGIN WWW PATH*/
		define("VIDTOK_WWWPATH", str_replace($_SERVER['DOCUMENT_ROOT'], '', VIDTOK_PLUGINFULLDIR));	
		

/* ACTIVATION
/*---------------------------*/

	/*INSTALLATION*/
		register_activation_hook(__FILE__,'vidtok_install');

	/*PLUGIN ACTIVATION IMPLEMENATION*/
		function vidtok_install()
			{
				
				/*GLOBALS*/
					global $current_user;
				
				/*TRACK INSTALLATION*/
					/*VARIABLES*/
						$wp_theme 	= wp_get_theme();
						$domain		= DOMAIN;
						$url		= 'https://api.vidtok.com/wordpress/installs';
						$args		= array(
										'method' 	=> 'POST', 
										'body' 		=> array(
															
															'email'				=> strtolower($current_user->user_email),
															'platform'			=> 'wordpress',
															'domain' 			=> $domain,
															'wp_theme_name' 	=> strtolower($wp_theme->get('Name')),
															'wp_theme_version' 	=> $wp_theme->get('Version'), 
															'wp_theme_uri'		=> strtolower($wp_theme->get('ThemeURI')), 
															'status'			=> 'install'
														)
													);
				
				/*POST DATA*/	
					wp_remote_post($url, $args); 
					  
			} 
	
	/*ACTIVATION NOTICE*/
		add_action('admin_notices', 'vidtok_settings_notice');
		
	/*PLUGIN ACTIVATION IMPLEMENATION*/
		function vidtok_settings_notice()
			{
				
				/*OPTIONS*/
					$options = get_option('vidtok_options');
				
				/*CHECK IF SET*/
					if($options['vapi'] == ''){
						
						/*VIDTOK API KEY*/
							echo '<div class="error"><p><strong>VIDTOK PLUGIN INSTALLATION:</strong> Your <code style="text-transform:uppercase">Vidtok API KEY</code> is missing, you will need to <a href="options-general.php?page=vidtok_settings">save your Vidtok api key</a> before the plugin will work properly.</p></div>';
							
					}
				
			}


/*  DEACTIVATION
/*---------------------------*/
	
	/*PLUGIN REMOVAL*/
		register_deactivation_hook( __FILE__, 'vidtok_uninstall' );
	
	/*PLUGIN REMOVAL IMPLEMENATION*/
		function vidtok_uninstall()
			{
				
				/*GLOBALS*/
					global $current_user;
				
				/*GET OPTIONS*/
					$options = get_option('vidtok_options');

				/*TRACK INSTALLATION*/
					/*VARIABLES*/
						$wp_theme 	= wp_get_theme();
						$domain		= DOMAIN;
						$url		= 'http://api.vidtok.com/wordpress/installs';
						$args		= array(
										'method' 	=> 'POST', 
										'body' 		=> array(
															
															'api_key'			=> $options['vapi'], 
															'email'				=> strtolower($current_user->user_email),
															'platform'			=> 'wordpress',
															'domain' 			=> $domain,
															'wp_theme_name' 	=> strtolower($wp_theme->get('Name')),
															'wp_theme_version' 	=> $wp_theme->get('Version'), 
															'wp_theme_uri'		=> strtolower($wp_theme->get('ThemeURI')), 
															'status'			=> 'uninstall'
														)
													);
				
				/*POST DATA*/	
					wp_remote_post($url, $args);				
				
				/*DELETE OPTIONS*/
					delete_option('vidtok_options');
				
			}


/*  ADMIN MENUS
/*---------------------------*/
	
	/*ADD VIDTOK ADMIN MENU*/
		add_action('admin_menu', 'vidtok_admin_menu'); 
		
	/*ADMIN MENU*/
		function vidtok_admin_menu()
			{	
			
				/*VARIABLES*/
					$icon_url	= VIDTOK_PLUGINFULLURL . '/images/vidtok-admin-logo.png';
				
				/*ADD MENU PAGE*/
					add_menu_page('Vidtok Settings', 'Vidtok Settings', 'activate_plugins', 'vidtok_settings', 'vidtok_settings', $icon_url);
		
			}
	
	/*ADMIN PAGE*/
		function vidtok_settings()
			{


				/*GET OPTIONS*/
					if(get_option('vidtok_options') !== false){
						
						/*OPTIONS*/
							$options = get_option('vidtok_options');
						
						/*GET PLUGIN SETTINGS*/
							if(ini_get('allow_url_fopen')){ 
												
								$json 	= file_get_contents('https://api.vidtok.com/wordpress/read-settings?api_key=' . $options['vapi']);
								$obj 	= json_decode($json);
									
							}else{
								
								$ch 		= curl_init(); 
								$timeout 	= 0; 
								curl_setopt ($ch, CURLOPT_URL, 'https://api.vidtok.com/wordpress/read-settings?api_key=' . $options['vapi']); 
								curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
								$json	 	= curl_exec($ch); 
								$obj		= json_decode($json);
								curl_close($ch); 
									
							}
						
					} ?>
				
				
				<!--VIDTOK SETTINGS-->
					<style type="text/css">
						.wrap, .wrap p { font-size:14px; }
						.vidtok-wp img { width:100%; height:100%; max-width:615px; max-height:380px; }
						.vidtok-wp label { text-transform:uppercase; }
					</style>
					
					<div class="wrap vidtok-wp" style="font-size:14px;"> 
						<br/><br/>
						<a href="http://www.vidtok.com?utm_source=wordpress&utm_medium=wordpress%20dashboard&utm_campaign=visit%20vidtok
" target="_blank"><img src="<?php echo VIDTOK_PLUGINFULLURL.'images/vidtok.png'; ?>" alt="Vidtok" style="max-width:400px;" /></a>
						<hr />
						<br/>
						<h2>Account Settings</h2>
						<?php if ( isset( $_GET['message'])&& $_GET['message'] == '1'){ ?>
							<div id='message' class='updated fade'><p>Vidtok account settings updated.</p></div>
						<?php } ?>
						<p>You will need a <code style="text-transform:uppercase">Vidtok api key</code> to access this plugin. If you have your <code style="text-transform:uppercase">Vidtok api key</code>, enter it below and click save settings. If you don't have a <code style="text-transform:uppercase">Vidtok api key</code> please visit <a href="http://www.vidtok.com/pricing?utm_source=wordpress&utm_medium=wordpress%20dashboard&utm_campaign=register%20with%20vidtok
" target="_blank">vidtok.com/pricing</a> and create an account.</p>
						<form method="POST"  action="admin-post.php">  
							<table class="form-table">  
								<tr valign="top">  
									<th scope="row">  
										<label for="vapi">VIDTOK API KEY</label>  
									</th>  
									<td width="327"> 
										<input type="text" name="vapi" size="32" value="<?php echo esc_html($options['vapi']); ?>" />   
									</td>
									<td><?php if(($_GET['message'] == '1') && ($obj->status->code == 8304)){ ?> <span style="font-style:italic; color:#C00;">Your <code style="text-transform:uppercase">API KEY</code> is incorrect, please make sure you entered the <code style="text-transform:uppercase">API KEY</code> correctly.</span> <?php } ?></td>  
								</tr>
                                
                                <?php if(($options['vapi']) && ($obj->status->code == 200)){ ?>
								<tr valign="bottom">  
									<th scope="row">  
										<label for="view">DEFAULT VIEW</label>  
									</th>  
									<td style="vertical-align:top;" width="327">  
										<select name="view" style="width:315px;">
											<option value="widget" <?php if($options['view'] == 'widget'){ ?> selected <?php } ?>>Widget</option>
											<?php if($obj->stype == 'advanced'){ ?>
											<option value="overlay" <?php if($options['view'] == 'overlay'){ ?> selected <?php } ?>>Overlay</option>
											<?php } ?>
										</select>  
									</td>
									<td>Select the view that you want the Vidtok plugin to display. <br/> <span style="color:#C00; font-style:italic;">Note that Embed Mode is only available using the shortcode, see below for example of implementation.</span></td>  
								</tr>
								<tr valign="top">  
									<th scope="row">  
										<label for="pages">PAGES</label>  
									</th>  
									<td width="327">
										<input type="text" name="pages" size="32" value="<?php echo esc_html($options['pages']); ?>" />  
									</td>
									<td>Enter the ids of the pages you want the Vidtok plugin to display. Separate ids by a comma. Enter <code><strong>all</strong></code> if you want the plugin to display on all pages.</td>    
								</tr>
								<tr valign="top">  
									<th scope="row">  
										<label for="posts">POSTS</label>  
									</th>  
									<td width="327"> 
										<input type="text" name="posts" size="32" value="<?php echo esc_html($options['posts']); ?>" />  
									</td>
									<td>Enter the ids of the posts you want the Vidtok plugin to display. Separate ids by a comma. Enter <code><strong>all</strong></code> if you want the plugin to display on all posts.</td>  
								</tr>
                                <?php } ?>
								<?php if($obj->status->code != 200 || $obj->stype == 'basic'){ ?>
								<tr valign="top">
									<th scope="row">&nbsp;</th>
									<td><input type="submit" value="Save Settings" class="button-primary"/></td>
									<td>&nbsp;</td>
								</tr>
								<?php } ?>                                     
							</table>
							
							<?php if($obj->stype == 'advanced'){ ?>
								<hr/>
								<br/>
								<h2>Plugin Message Text - Overlay & Embed Mode</h2>
								<p></p>
								<table class="form-table">  
									<tr valign="top">  
										<th scope="row">  
											<label for="">Title</label>  
										</th>  
										<td width="327">  
											<input type="text" name="title" size="32" value="<?php echo $obj->{0}->vidtok->content->title; ?>" />  
										</td>
										<td>Enter a friendly title for your video chat.</td>  
									</tr>
									<tr valign="top">  
										<th scope="row">  
											<label for="">Header</label>  
										</th>  
										<td width="327">  
											<input type="text" name="header" size="32" value="<?php echo $obj->{0}->vidtok->content->header; ?>" />  
										</td>
										<td>Enter header text above your message to users.</td>  
									</tr>
									<tr valign="top">  
										<th scope="row">  
											<label for="">Message</label>  
										</th>  
										<td width="327">  
											<textarea name="msg" cols="32" rows="8"><?php echo $obj->{0}->vidtok->content->msg; ?></textarea>   
										</td>
										<td valign="top">Message for to your users about your video chat. You are allowed to use html tags. <br/><br/> <code>"h1", "h2", "h3", "h4", "h5", "h6", "b", "hr", "strong", "br", "em"</code></td>  
									</tr>
									<tr valign="top">  
										<th scope="row">  
											<label for="">Button</label>  
										</th>  
										<td width="327">  
											<input type="text" name="button" size="32" value="<?php echo $obj->{0}->vidtok->content->button; ?>" />  
										</td>
										<td>Enter button start text message.</td>   
									</tr>
									<?php if($obj->status->code == 200){ ?>
									<tr valign="top">
										<th scope="row">&nbsp;</th>
										<td><input type="submit" value="Save Settings" class="button-primary"/></td>
										<td>&nbsp;</td>
									</tr>
									<?php } ?> 																
								</table>
							<?php } ?>

							<input type="hidden" name="action" value="save_vidtok_options" />
               				<?php wp_nonce_field('vidtok'); ?> 
						</form>		
						<hr/>
                        
						<h2>Usage Instructions</h2>
						<p>You can setup your Vidtok plugin to accommodate a wide range of implementations within your WordPress website. Below are some instructions on how to best use the Vidtok plugin.</p>
						<br/>
						
						<h4 style="text-transform:uppercase; margin-bottom:5px;">Widget View</h3>
						<p>Widget view is the default view for all Vidtok plugins. The Vidtok widget will appear in the lower right hand corner of your screen. If you have an advanced subscription you will be able to expand and collapse from the overlay view and widget view.<br/><br/> Shortcode Example:<br/> <code style="font-size:18px;">[vidtok plugin="vidtok" view="widget"]</code><br/><br/></p>
						
						<img src="<?php echo VIDTOK_PLUGINFULLURL.'images/widget.png'; ?>" style="border:2px #000 solid;" />


						<p>&nbsp;</p>
						<h4 style="text-transform:uppercase; margin-bottom:5px;">Overlay View <span style="color:#C00; font-style:italic;">(Available in Advanced Subscriptions Only)</span></h3>
						<p>In overlay view, the Vidtok plugin will overlay ontop of your website and dynamically adjust for each connection that is added or removed from your video chat. <br/><br/> Shortcode Example:<br/> <code style="font-size:18px;">[vidtok plugin="vidtok" view="overlay"]</code><br/><br/></p> 
						<img src="<?php echo VIDTOK_PLUGINFULLURL.'images/overlay.png'; ?>"  style="border:2px #000 solid;" />
						
						
						<p>&nbsp;</p>
						<h4 style="text-transform:uppercase; margin-bottom:5px;">Embed View <span style="color:#C00; font-style:italic;">(Available in Advanced Subscriptions Only)</span></h3>
						<p>Embeded view allows for you to integrate directly into your website's content via a shortcode. For any post or page you want to display the embeded view drop in the following shortcode:<br/><br/> <code style="font-size:18px;">[vidtok plugin="vidtok" view="embed"]</code><br/><br/>
						Make sure that need to have at least <strong style="color:#C00;">600px</strong> for the Vidtok plugin to embed properly. <strong style="color:#C00;">900px</strong> is the recommended size.</p><p>You also have the option to hide the <strong>"Invite a Friend to Chat"</strong> button in embed mode using the following shortcode. <br/><br/> <code style="font-size:18px;">[vidtok plugin="vidtok" view="embed" invite="hide"]</code><br/><br/></p> 
						<img src="<?php echo VIDTOK_PLUGINFULLURL.'images/embed.png'; ?>" style="border:2px #000 solid;" />

						
						<p>&nbsp;</p>
						<h4 style="text-transform:uppercase; margin-bottom:5px;">Invite Friends to  Video Chat</h3>
						<p>You can invite friends to join you for a video chat easily using our integrated share controls. You can invite friends via Facebook, Twitter, Google Plus or email.</p>
						<img src="<?php echo VIDTOK_PLUGINFULLURL.'images/invite.png'; ?>" style="border:2px #000 solid;" />
						
						<hr/>
						
					</div>
					
				
		<?php }

	/*SAVE SETTINGS*/
		add_action('admin_init', 'vidtok_admin_options');
	
	/*SAVE OPTIONS*/
		function vidtok_admin_options(){
			
			/*ADD ACTION*/	
				add_action('admin_post_save_vidtok_options', 'procss_vidtok_options');
			
		}
	
	/*PROCESS OPTIONS*/	
		function procss_vidtok_options()
			{	
			
				/*GLOBALS*/
					global $current_user;
				
				/*OPTIONS*/	
					$new_options['vapi'] 	= $_POST['vapi'];
					$new_options['view'] 	= $_POST['view'];
					$new_options['pages']	= strtolower($_POST['pages']);
					$new_options['posts'] 	= strtolower($_POST['posts']);
				
				/*SET OPTIONS*/
					if(get_option('vidtok_options') === false){ 
	
						/*ADD OPTIONS*/
							add_option('vidtok_options', $new_options);
							
					}else{
						
						/*DELETE OPTIONS*/
							$existing_options = get_option('vidtok_options');
							
							delete_option('vidtok_options');
							
						/*ADD OPTIONS*/
							add_option('vidtok_options', $new_options);
						
						
					} 
				
				/*POST PLUGIN MESSAGE SETTINGS*/
					/*VARIABLES*/
						$surl 		= 'https://api.vidtok.com/wordpress/update-settings';
						$sargs		= array(
										'method' 	=> 'POST', 
										'body' 		=> array(
															
															'api_key'	=> $_POST['vapi'],
															'title'		=> $_POST['title'],
															'header'	=> $_POST['header'],
															'msg'		=> $_POST['msg'],
															'button'	=> $_POST['button']
														)
													);
						/*POST DATA*/	
							wp_remote_post($surl, $sargs); 
						
				/*LOG USER DEFINED SETTINGS*/
					/*VARIABLES*/
						$wp_theme 	= wp_get_theme();
						$domain		= DOMAIN;
						$url		= 'https://api.vidtok.com/wordpress/settings';
						$args		= array(
										'method' 	=> 'POST', 
										'body' 		=> array(
															
															'api_key'			=> $_POST['vapi'],
															'email'				=> strtolower($current_user->user_email),
															'platform'			=> 'wordpress',
															'default_view' 		=> $_POST['view'],
															'posts'				=> (strtolower($_POST['posts']) == 'all') ? 'all' : 'specific',
															'pages'				=> (strtolower($_POST['pages']) == 'all') ? 'all' : 'specific',
															'domain' 			=> $domain,
															'wp_theme_name' 	=> strtolower($wp_theme->get('Name')),
															'wp_theme_version' 	=> $wp_theme->get('Version'), 
															'wp_theme_uri'		=> strtolower($wp_theme->get('ThemeURI')), 
															'status'			=> 'install'
														)
													);
				
				/*POST DATA*/	
					wp_remote_post($url, $args); 
				
				/*REDIRECT*/	
					wp_redirect( add_query_arg(array('page' => 'vidtok_settings', 'message' => '1'), admin_url('options-general.php')));  
					
				/*EXIT FUNCTION*/	
					exit;					
						
						
			}
	
/*  DEFINE SHORTCODES
/*---------------------------*/

	/*ADD SHORTCODE*/
		add_shortcode('vidtok', 'vidtok_shortcode');


/*  DEFINE SHORTCODE FUNCTIONS
/*---------------------------*/

	function vidtok_shortcode($atts)
		{
			
			
			/*VARIABLES*/
				extract(shortcode_atts(array(
					'plugin'	=> '',
					'view'		=> '',
					'invite'	=> ''  
				), $atts));	
			
			/*GLOBALS*/	
				define('VT_PLUGIN', 'vidtok');
				define('VT_VIEW', $view);
				define('VT_THEME', $theme);
				define('VT_PLATFORM', 'wordpress');
				define('VT_INVITE', $invite);

			/*CHECK VIEW*/
				if($view == 'embed'){
						
					/*CREATE VIDTOK DIV*/
						if(is_page() || is_single()){ 	
							echo '<div id="vt-embed"></div>';
						}
				}
	
			
	
			/*ADD VIDTOK JS*/
				function add_vidtok()
					{ if(is_page() || is_single()){ $options = get_option('vidtok_options');  ?>

						<!--VIDTOK [START]-->
							<script type="text/javascript" src="https://api.vidtok.com/static/js/stable/vidtok.min.js"></script>  
							<script type="text/javascript">
								vapi 		= '<?php echo $options['vapi']; ?>';
								debug		= true;
								view		= '<?php echo VT_VIEW; ?>';
								invite		= '<?php echo VT_INVITE; ?>';
								platform	= '<?php echo VT_PLATFORM; ?>';
								plugin		= '<?php echo VT_PLUGIN; ?>';   
							</script>  
						<!--VIDTOK [END]-->
					
				<? } }	
			
			/*ADD ACTION*/	
				add_action('wp_footer', 'add_vidtok', 1000000);
			
		}
			
			
				
/*  VIDTOK PLUGIN
/*---------------------------*/
	
	/*ADD ACTION*/
		add_action('wp', 'vidtok_plugin');
		
	
	/*VIDTOK PLUGIN IMPLMENTATION*/
		function vidtok_plugin()
			{

				/*GET ID OF POST OR PAGE*/
					global $post; 
					$ID = $post->ID;
				
				/*CHECK IF EMBED SHORTCODE USED*/
					if(is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'vidtok')) {
					
					
					}else{
					
						/*GET OPTIONS*/
							if(get_option('vidtok_options') !== false){
								
								/*OPTIONS*/
									$options = get_option('vidtok_options');
									
								/*GLOBALS*/	
									define('VT_PLUGIN', 'vidtok');
									define('VT_VIEW', $options['view']);
									define('VT_THEME', $options['theme']);
									define('VT_PLATFORM', 'wordpress');
															
							} 
						
						/*TEMPLATE TYPES*/	
							$functions = array( 
											'is_404()',
											'is_admin()',
											'is_archive()',
											'is_attachment()',
											'is_author()',
											'is_category()',
											'is_date()',
											'is_day()',
											'is_feed()',
											'is_front_page()',
											'is_home()',
											'is_local_attachment()',
											'is_month()',
											'is_new_day()',
											'is_page_template()',
											'is_paged()',
											'is_search()',
											'is_singular()',
											'is_sticky()',
											'is_tag()',
											'is_tax()',
											'is_time()',
											'is_year()'
										);
						
						/*LOOP THROUGH EACH TEMPLATE TYPE*/	
							foreach($functions as $f){
								if($f){
									$search = 'none';	
								}	
							}

						/*PAGES & POSTS*/
							if(is_page()){ 			
								$search = ($options['pages'] == 'all') ? 'all' : $options['pages'];
								
							}
							
							if(is_single()){
								$search = ($options['posts'] == 'all') ? 'all' : $options['posts'];
							}

						/*ADD ACTION*/	
							if($search == 'all'){
		
								/*ADD VIDTOK JS*/
									function add_vidtok()
										{ $options = get_option('vidtok_options'); ?>
					
												<!--VIDTOK [START]-->
													<script type="text/javascript" src="https://api.vidtok.com/static/js/stable/vidtok.min.js"></script>  
													<script type="text/javascript">
														vapi 		= '<?php echo $options['vapi']; ?>';
														debug		= true;
														view		= '<?php echo VT_VIEW; ?>';
														platform	= '<?php echo VT_PLATFORM; ?>';
														plugin		= '<?php echo VT_PLUGIN; ?>';   
													</script>  
												<!--VIDTOK [END]-->
												
										<?  }	
											
								/*ADD ACTION*/
									add_action('wp_footer', 'add_vidtok', 1000000);
										
							}else{
									
									
								/*VARIABLES*/
									$ids = explode(',', $search);
									
								/*SEARCH ARRAY*/	
									if(in_array($ID, $ids)){
			
										/*ADD VIDTOK JS*/
											function add_vidtok()
												{ $options = get_option('vidtok_options');  ?>
							
														<!--VIDTOK [START]-->
															<script type="text/javascript" src="https://api.vidtok.com/static/js/stable/vidtok.min.js"></script>   
															<script type="text/javascript">
																vapi 		= '<?php echo $options['vapi']; ?>';
																debug		= true;
																view		= '<?php echo VT_VIEW; ?>';
																platform	= '<?php echo VT_PLATFORM; ?>';
																plugin		= '<?php echo VT_PLUGIN; ?>';   
															</script>  
														<!--VIDTOK [END]-->
														
												<?  }	
													
										/*ADD ACTION*/
											add_action('wp_footer', 'add_vidtok', 1000000);
														
										
									}	
										
							}
					
					}
		
			}



