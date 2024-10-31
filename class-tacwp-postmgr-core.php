<?php
/** SEO Bulk Admin compiled project class file.
 *
 * @package AMP Publisher
 * @subpackage SEO Bulk Admin
 * @since 1.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || die();

/**
 * Base class for project (theme or plugin).
 *
 * All project functionality gets compiled into a single class object, allowing for compatibility across projects and method inclusion from core based on project dependencies.
 *
 * @since 1.0
 */

#[AllowDynamicProperties]
class Tacwp_Postmgr_Core {

	/**
	 * Basic construct method native to all PHP classes. It sets up all project and class variables then runs the extends_construct() method for the project if it is included.
	 *
	 * @since 1.0
	 *
	 * @param string $prefix .
	 * @param string $dir .
	 * @source core
	 */
	public function __construct( $prefix = '', $dir = '' ) {
		if ( '' === $dir ) {
			$dir = __DIR__;}
		$this->self['TITLE']        = 'SEO Bulk Admin';
		$this->self['VERSION']      = '1.0.0';
		$this->self['DIR']          = $dir . '/';
		$this->self['FOLDER']       = 'seo-bulk-admin';
		$this->self['PRODUCT_TYPE'] = 'plugin';
		if ( 'plugin' === $this->self['PRODUCT_TYPE'] ) {
			$this->self['PATH'] = plugins_url( $this->self['FOLDER'] ) . '/';
		} else {
			$this->self['PATH'] = esc_url( get_template_directory_uri() ) . '/';
		}
		$this->self['prefix'] = $prefix;
	}

	/**
	 * Intercept ajax requests and pass them through a handler.
	 *
	 * @since 1.0
	 *
	 * @requires : sanitize_ajax_value, is_checked
	 * @usage : ajax_setup
	 * @source ext
	 */
	public function admin_ajax_handler() {
		$how = $this->sanitize_ajax_value( 'how' );
		if ( 'process' === $how ) {
			$method = $this->sanitize_ajax_value( 'method' );
			$target = $this->sanitize_ajax_value( 'target' );
			if ( '' === $method ) {
				die( 'ERROR: You must choose a batch Process to run.' );}
			$page       = $this->sanitize_ajax_value( 'topage' );
			$post       = $this->sanitize_ajax_value( 'topost' );
			$tags       = $this->sanitize_ajax_value( 'tags' );
			$categories = $this->sanitize_ajax_value( 'categories' );
			$category   = $this->sanitize_ajax_value( 'category' );
			$tab        = $this->sanitize_ajax_value( 'tab' );
			$append     = $this->is_checked( $this->sanitize_ajax_value( 'append', false ) );
			$force      = $this->is_checked( $this->sanitize_ajax_value( 'force', false ) );
			$version    = $this->sanitize_ajax_value( 'version' );
			$posts      = $this->sanitize_ajax_value( 'posts', array() );
			if ( count( $posts ) > 0 ) {
				foreach ( $posts as $post_id ) {
					if ( 'category' === $method ) {
						$bytype = $this->sanitize_ajax_value( 'post_type' );
						if ( 'product' === $bytype ) {
							$woocategory = $this->sanitize_ajax_value( 'woocategory' );
							if ( '' !== $woocategory ) {
								wp_set_post_terms( $post_id, $woocategory, 'product_cat', $append );
							}
						} elseif ( '' !== $categories ) {
								wp_set_post_categories( $post_id, $categories, $append );
						}
					}
					if ( 'tags' === $method ) {
						wp_set_post_tags( $post_id, $tags, $append );}
					if ( 'delete' === $method ) {
						wp_delete_post( $post_id, $force );}
					if ( function_exists( 'tacwp_postmgr_process_post' ) ) {
						tacwp_postmgr_process_post( $post_id );
					}
				}
			}
			die( 'OK' );
		}
		die();
	}

	/**
	 * Handle enqueue action for admin scripts and styles.
	 *
	 * @since 1.0
	 *
	 * @requires : ajax_data_object
	 * @usage : admin_init
	 * @source ext
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'seobulkadmin-admin-stylesheet', $this->self['PATH'] . 'admin_style.css', array(), time() );
		wp_enqueue_script( 'seobulkadmin-admin', $this->self['PATH'] . 'admin_script.js', array( 'jquery' ), time(), false );
		wp_localize_script( 'seobulkadmin-admin', 'tacwp_postmgr_data_object', $this->ajax_data_object() );
	}

	/**
	 * Action hook method for "admin_init".
	 *
	 * @since 1.0
	 *
	 * @requires : admin_enqueue_scripts
	 * @usage : init
	 * @source ext
	 */
	public function admin_init() {
		global $pagenow;
		if(isset($_GET['page'])){// phpcs:ignore
			$getpage = sanitize_text_field( wp_unslash( $_GET['page'] ) );// phpcs:ignore
			if('admin.php'===$pagenow && 'seobulkadmin'===$getpage){// phpcs:ignore
				add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ), 9 );
			}
		}
	}

	/**
	 * Action hook method for "admin_menu".
	 *
	 * @since 1.0
	 *
	 * @requires : admin_page
	 * @usage : init
	 * @source ext
	 */
	public function admin_menu() {
		if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
			add_menu_page( esc_html__( 'SEO Bulk Admin', 'seo-bulk-admin' ), 'SEO Bulk Admin', 'manage_options', 'seobulkadmin', array( &$this, 'admin_page' ), 'dashicons-superhero', 28 );
		}
	}

	/**
	 * Output content for the plugin admin page register by admin_menu().
	 *
	 * @since 1.0
	 *
	 * @requires : sanitize_get_variable, pro, batch_processing
	 * @usage : admin_menu
	 * @source ext
	 */
	public function admin_page() {
		echo '<h1>' . esc_html__( 'SEO Bulk Admin', 'seo-bulk-admin' ) . '</h1>';
		$bytype = $this->sanitize_get_variable( 'bytype', 'post' );
		$tab    = $this->sanitize_get_variable( 'tab' );
		echo '<div class="pmgr" data-post-type="' . esc_attr( $bytype ) . '" data-tab="' . esc_attr( $tab ) . '">';
			$tab = $this->sanitize_get_variable( 'tab' );
			$bat = '';
			$pro = '';
		if ( 'pro' === $tab ) {
			$pro = ' active';
		} elseif ( '' === $tab ) {
			$bat = ' active';
		}
			echo '<div class="tabs frow">';
				$batchtab = add_query_arg( array( 'page' => 'seobulkadmin' ), admin_url( 'admin.php' ) );
				echo '<a href="' . esc_url( $batchtab ) . '" class="tab batch' . esc_attr( $bat ) . '">' . esc_html__( 'Batch Processing', 'seo-bulk-admin' ) . '</a>';
		if ( function_exists( 'tacwp_postmgr_tabs' ) ) {
			tacwp_postmgr_tabs();
		} else {
			$protab = add_query_arg(
				array(
					'page' => 'seobulkadmin',
					'tab'  => 'pro',
				),
				admin_url( 'admin.php' )
			);
			echo '<a href="' . esc_url( $protab ) . '" class="tab batch' . esc_attr( $pro ) . '">' . esc_html__( 'Upgrade to Pro', 'seo-bulk-admin' ) . '</a>';
		}
			echo '</div>';
		if ( 'pro' === $tab ) {
			$this->pro();
		} elseif ( '' === $tab ) {
			$this->batch_processing();
		} elseif ( function_exists( 'tacwp_postmgr_tab' ) ) {
				tacwp_postmgr_tab();
		}
		echo '</div>';
		die();
	}

	/**
	 * Localize inline script for ajax handler object.
	 *
	 * @since 1.0
	 *
	 * @requires : nonce, is_woo
	 * @usage : admin_enqueue_scripts
	 * @source ext
	 */
	public function ajax_data_object() {
		$arr                 = array();
		$arr['ajaxurl']      = admin_url( 'admin-ajax.php' );
		$arr['adminPostURL'] = admin_url( 'post.php', '' );
		$arr['adminURL']     = add_query_arg( array( 'page' => 'seobulkadmin' ), admin_url( 'admin.php' ) );
		$arr['ajax_nonce']   = $this->nonce();
		$arr['iswoo']        = $this->is_woo();
		return $arr;
	}

	/**
	 * Initialize actions for ajax processes.
	 *
	 * @since 1.0
	 *
	 * @requires : admin_ajax_handler
	 * @usage : init
	 * @source ext
	 */
	public function ajax_setup() {
		add_action( 'wp_ajax_tacwp_postmgr_ajax_handler', array( &$this, 'admin_ajax_handler' ) );
	}

	/**
	 * Output the form used in batch processing and redirects.
	 *
	 * @since 1.0
	 *
	 * @requires : sanitize_get_variable, query_url, is_woo, query_posts, get_value, dom_load
	 * @usage : admin_page
	 * @source ext
	 */
	public function batch_processing() {
		$tab       = $this->sanitize_get_variable( 'tab', 'post' );
		$before    = $this->sanitize_get_variable( 'before' );
		$after     = $this->sanitize_get_variable( 'after' );
		$column    = $this->sanitize_get_variable( 'column', 'post_date' );
		$bytype    = $this->sanitize_get_variable( 'bytype', 'post' );
		$bytarget  = $this->sanitize_get_variable( 'bytarget' );
		$bycode    = $this->sanitize_get_variable( 'bycode' );
		$category  = $this->sanitize_get_variable( 'category' );
		$search    = $this->sanitize_get_variable( 'search' );
		$searchby  = $this->sanitize_get_variable( 'searchby' );
		$orderby   = $this->sanitize_get_variable( 'orderby' );
		$exporturl = $this->query_url(
			array(
				'tab'       => $tab,
				'exportcsv' => true,
			)
		);
		echo '<div class="srch fcol">';
			echo '<div class="date-items frow" style="align-items:unset;">';
				echo '<div class="search-item fcol sbox">';
					$searchtitle = esc_html__( 'Keyword Search', 'seo-bulk-admin' );
					$placeholder = '';
					echo '<label for="search" class="frow" style="justify-content:space-between;align-items:end;">' . esc_html( $searchtitle ) . '</label>';
					echo '<input id="search" type="text" value="' . esc_attr( $search ) . '" class="search-field" placeholder="' . esc_attr( $placeholder ) . '" autocomplete="off">';
					echo '<div class="frow">';
						echo '<label for="searchby">' . esc_html__( 'Method', 'seo-bulk-admin' ) . '</label>';
						echo '<select id="searchby" onchange="tacwp_postmgr.selectSearchBy(this);" style="margin-left:auto;" autocomplete="off">';
							$opts            = array();
							$opts['']        = 'WP Search';
							$opts['title']   = 'Title';
							$opts['content'] = 'Content';
							$opts['tag']     = 'Tag';
		foreach ( $opts as $k => $v ) {
			$sel = '';
			if ( $k === $searchby ) {
				$sel = ' selected';}
			echo '<option' . esc_attr( $sel ) . ' value="' . esc_attr( $k ) . '">' . esc_attr( $v ) . '</option>';
		}
						echo '</select>';
					echo '</div>';
				echo '</div>';
				echo '<div class="select-item fcol sbox">';
					echo '<label for="bytype">' . esc_html__( 'Post Type', 'seo-bulk-admin' ) . '</label>';
					echo '<select id="bytype" class="search-field" onchange="tacwp_postmgr.selectPostType(this);" autocomplete="off">';
						$opts         = array();
						$opts['']     = esc_html__( 'Post', 'seo-bulk-admin' );
						$opts['page'] = esc_html__( 'Page', 'seo-bulk-admin' );
		if ( $this->is_woo() ) {
			$opts['product'] = esc_html__( 'Product', 'seo-bulk-admin' );}
		foreach ( $opts as $k => $v ) {
			$sel = '';
			if ( $bytype === $k ) {
				$sel = ' selected';}
			echo '<option' . esc_attr( $sel ) . ' value="' . esc_attr( $k ) . '">' . esc_attr( $v ) . '</option>';
		}
					echo '</select>';
				echo '</div>';
				echo '<div class="cat-post select-item fcol sbox">';
					echo '<label for="bycategory">' . esc_html__( 'Category', 'seo-bulk-admin' ) . '</label>';
					wp_dropdown_categories(
						array(
							'id'              => 'bycategory',
							'class'           => 'search-field',
							'show_option_all' => __( 'Show All Categories', 'seo-bulk-admin' ),
							'orderby'         => 'name',
							'value_field'     => 'slug',
							'selected'        => $category,
							'hierarchical'    => true,
							'depth'           => 3,
							'show_count'      => true,
							'hide_empty'      => false,
						)
					);
				echo '</div>';
		if ( $this->is_woo() ) {
			$woocategory = $this->sanitize_get_variable( 'woocategory' );
			echo '<div class="cat-product select-item fcol sbox">';
				echo '<label for="woocategory">' . esc_html__( 'Product Category', 'seo-bulk-admin' ) . '</label>';
				wp_dropdown_categories(
					array(
						'id'              => 'woocategory',
						'class'           => 'search-field',
						'show_option_all' => __( 'Show All Categories', 'seo-bulk-admin' ),
						'orderby'         => 'name',
						'value_field'     => 'slug',
						'selected'        => $woocategory,
						'hierarchical'    => true,
						'depth'           => 3,
						'show_count'      => true,
						'hide_empty'      => false,
						'taxonomy'        => 'product_cat',
					)
				);
			echo '</div>';
		}
				echo '<div class="date-box fcol sbox">';
					echo '<div class="date-items frow">';
						echo '<div class="date-item fcol">';
							echo '<label for="after">' . esc_html__( 'After Date', 'seo-bulk-admin' ) . '</label>';
							echo '<input id="after" name="after" type="date" value="' . esc_attr( $after ) . '" class="" autocomplete="off">';
						echo '</div>';
						echo '<div class="date-item fcol">';
							echo '<label for="before">' . esc_html__( 'Before Date', 'seo-bulk-admin' ) . '</label>';
							echo '<input id="before" name="before" type="date" value="' . esc_attr( $before ) . '" class="" autocomplete="off">';
						echo '</div>';
					echo '</div>';
					$alt = esc_html__( 'Check this box to search by the post modified date rather than by the post published date.', 'seo-bulk-admin' );
					echo '<div class="checkbox-item frow" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $alt ) . '">';
						$checked = '';
		if ( 'post_modified' === $column ) {
			$checked = ' checked';}
						echo '<input id="column" name="column" type="checkbox" class=""' . esc_attr( $checked ) . ' autocomplete="off">';
						echo '<label for="column">' . esc_html__( 'Search by date modified', 'seo-bulk-admin' ) . '</label>';
					echo '</div>';
				echo '</div>';
				echo '<div class="select-item fcol sbox">';
					echo '<label for="orderby">' . esc_html__( 'Order By', 'seo-bulk-admin' ) . '</label>';
					echo '<select id="orderby" autocomplete="off">';
						$opts         = array();
						$opts['']     = esc_html__( 'Date : New - Old', 'seo-bulk-admin' );
						$opts['date'] = esc_html__( 'Date : Old - New', 'seo-bulk-admin' );
						$opts['az']   = esc_html__( 'Alpha : A-Z', 'seo-bulk-admin' );
						$opts['za']   = esc_html__( 'Alpha : Z-A', 'seo-bulk-admin' );
		foreach ( $opts as $k => $v ) {
			$sel = '';
			if ( "$orderby" === "$k" ) {
				$sel = ' selected';}
			echo '<option' . esc_attr( $sel ) . ' value="' . esc_attr( $k ) . '">' . esc_html( $v ) . '</option>';
		}
					echo '</select>';
				echo '</div>';
			echo '</div>';
			echo '<div class="buttons frow" style="margin-top:20px;">';
				echo '<a class="button" href="javascript:tacwp_postmgr.submitQuery();">' . esc_html__( 'Submit', 'seo-bulk-admin' ) . '</a>';
				echo '<a class="button" href="javascript:tacwp_postmgr.clearQuery();">' . esc_html__( 'Clear', 'seo-bulk-admin' ) . '</a>';
				echo '<a class="button" href="javascript:tacwp_postmgr.showAll();">' . esc_html__( 'Show All', 'seo-bulk-admin' ) . '</a>';
				echo '<a class="button" href="' . esc_url( $exporturl ) . '" target="_blank">' . esc_html__( 'Export Results as CSV', 'seo-bulk-admin' ) . '</a>';
			echo '</div>';
		echo '</div>';
		$posts      = $this->query_posts();
		$hasresults = false;
		if ( count( $posts ) > 0 ) {
			$hasresults = true;}
		$showresults = true;
		$searching   = false;
		if ( '' !== $this->sanitize_get_variable( 'search' ) ) {
			$searching = true;}
		if ( '' !== $this->sanitize_get_variable( 'bytype' ) ) {
			$searching = true;}
		if ( '' !== $this->sanitize_get_variable( 'category' ) ) {
			$searching = true;}
		if ( '' !== $this->sanitize_get_variable( 'woocategory' ) ) {
			$searching = true;}
		if ( '' !== $this->sanitize_get_variable( 'before' ) ) {
			$searching = true;}
		if ( '' !== $this->sanitize_get_variable( 'after' ) ) {
			$searching = true;}
		$ver = 'results';
		echo '<div id="resultsbox" class="results fcol" data-version="results" data-post-type="' . esc_attr( $bytype ) . '">';
		if ( $searching ) {
			if ( $hasresults ) {
				$results = count( $posts );
				if ( 1 === $results ) {
					$result = esc_html__( '1 result found', 'seo-bulk-admin' );
				} else {
					$result = count( $posts ) . ' ' . esc_html__( 'results found', 'seo-bulk-admin' );
				}
				echo '<div class="results-found">' . esc_attr( $result ) . '</div>';
			} else {
				echo '<div class="results-found">' . esc_html__( 'No results found', 'seo-bulk-admin' ) . '</div>';
			}
		}
			echo '<div class="results-window">';
				echo '<div class="results-side fcol">';
		if ( $hasresults ) {
			echo '<div class="processes frow">';
				echo '<div class="toggle-posts frow">';
					echo '<a href="javascript:tacwp_postmgr.togglePosts();" alt="" class="toggle-button"><span class="isoff fa-regular fa-square-check"></span><span class="ison fa-solid fa-square-check"></span></a>';
					echo '<label class="toggle-label"><span class="isoff lbl">' . esc_html__( 'Select All', 'seo-bulk-admin' ) . '</span><span class="ison lbl">' . esc_html__( 'De-select All', 'seo-bulk-admin' ) . '</span></label>';
				echo '</div>';
			echo '</div>';
			foreach ( $posts as $pid => $data ) {
				$post_id = $this->get_value( $data, 'ID' );
				if ( '' === $post_id ) {
					continue;}
				$post_name  = $this->get_value( $data, 'post_name' );
				$post_title = $this->get_value( $data, 'post_title' );
				$post_type  = $this->get_value( $data, 'post_type' );
				$post_date  = $this->get_value( $data, $column );
				$post_date  = gmdate( 'F j, Y', strtotime( $post_date ) );
				$editurl    = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
				$srcurl     = get_the_permalink( $post_id );
				echo '<div class="result frow">';
					echo '<span class="side fcol" style="gap:0;">';
						echo '<span class="sidebtns frow">';
							echo '<div class="checkbox-item frow">';
								echo '<input id="post_' . esc_attr( $post_id ) . '" name="post_' . esc_attr( $post_id ) . '" type="checkbox" class="post-checkbox" autocomplete="off">';
							echo '</div>';
								echo '<a href="' . esc_url( $editurl ) . '" target="_blank" alt="Open this ' . esc_attr( $post_type ) . ' for editing." style="margin-left:auto;"><span class="fa-solid fa-edit"></span></a>';
								echo '<a href="' . esc_url( $srcurl ) . '" target="_blank" alt="View this ' . esc_attr( $post_type ) . '."><span class="fa-solid fa-eye"></span></a>';
						echo '</span>';
							echo '<span style="font-size:10px;margin-top:auto;">' . esc_attr( $post_date ) . '</span>';
					echo '</span>';
					echo '<div class="thepost fcol" style="gap:0;">';
						echo '<div class="frow">';
								$icon = 'wp-menu-image dashicons-before dashicons-admin-' . $post_type;
				if ( 'product' === $post_type ) {
					$icon = 'dashicons dashicons-cart';}
								echo '<span class="' . esc_attr( $icon ) . '"></span>';
							echo '<span class="">' . esc_html( $post_title ) . '</span>';
						echo '</div>';
						echo '<div class="post-details frow">';
				if ( 'product' === $bytype ) {
					$terms = get_the_terms( $post_id, 'product_cat' );
					if ( ! is_wp_error( $terms ) && is_array( $terms ) && count( $terms ) > 0 ) {
										echo '<span class="cats frow">';
						foreach ( $terms as $term ) {
							$term_id = $term->term_id;
							$name    = $this->get_value( $term, 'name' );
							echo '<span class="cat">' . esc_attr( $name ) . '</span>';
						}
										echo '</span>';
					}
				} else {
							$cats = wp_get_post_categories( $post_id );
					if ( ! is_wp_error( $cats ) && is_array( $cats ) && count( $cats ) > 0 ) {
						echo '<span class="cats frow">';
						foreach ( $cats as $catid ) {
								$term = get_term( $catid );
								$name = $this->get_value( $term, 'name' );
								echo '<span class="cat">' . esc_attr( $name ) . '</span>';
						}
								echo '</span>';
					}
				}
								$tags = get_the_tags( $post_id );
				if ( ! is_wp_error( $tags ) && is_array( $tags ) && count( $tags ) > 0 ) {
								echo '<span class="tags frow">';
					foreach ( $tags as $term ) {
								$name = $this->get_value( $term, 'name' );
								echo '<span class="tag">' . esc_attr( $name ) . '</span>';
					}
								echo '</span>';
				}
													echo '</div>';
													echo '</div>';
													echo '</div>';
			}
		}
				echo '</div>';
		if ( ( $searching && $hasresults ) ) {
			echo '<div class="process-side fcol">';
				echo '<div class="process-field fcol">';
					echo '<label for="method">' . esc_html__( 'Process', 'seo-bulk-admin' ) . '</label>';
					echo '<select id="method" onchange="tacwp_postmgr.selectMethod(this);" class="proc-input" data-proc-type="select" autocomplete="off">';
						echo '<option value=""></option>';
			if ( 'post' === $bytype ) {
				echo '<option value="category">' . esc_html__( 'Assign Category', 'seo-bulk-admin' ) . '</option>';
				echo '<option value="tags">' . esc_html__( 'Assign Tags', 'seo-bulk-admin' ) . '</option>';
				echo '<option value="delete">' . esc_html__( 'Delete Post', 'seo-bulk-admin' ) . '</option>';
			}
			if ( 'page' === $bytype ) {
				echo '<option value="delete">' . esc_html__( 'Delete Page', 'seo-bulk-admin' ) . '</option>';
			}
			if ( 'product' === $bytype ) {
				echo '<option value="category">' . esc_html__( 'Assign Category', 'seo-bulk-admin' ) . '</option>';
				echo '<option value="delete">' . esc_html__( 'Delete Product', 'seo-bulk-admin' ) . '</option>';
			}
			if ( function_exists( 'tacwp_postmgr_process_options' ) ) {
				tacwp_postmgr_process_options();
			}
							echo '</select>';
							echo '</div>';
			if ( function_exists( 'tacwp_postmgr_process_fields' ) ) {
				tacwp_postmgr_process_fields();
			}
							echo '<div class="proc as-categories use-categories fcol">';
							echo '<label for="tocategories">' . esc_html__( 'Category', 'seo-bulk-admin' ) . '</label>';
							echo '<ul id="tocategories" class="cats-checklist">';
						wp_terms_checklist( 0, array( 'class' => 'proc-input' ) );
							echo '</ul>';
							echo '</div>';
			if ( $this->is_woo() ) {
				echo '<div class="proc as-product-cat use-product-categories fcol">';
				echo '<label for="toproductcat">' . esc_html__( 'Product Category', 'seo-bulk-admin' ) . '</label>';
				echo '<ul id="toproductcat" class="cats-checklist">';
					wp_terms_checklist(
						0,
						array(
							'taxonomy' => 'product_cat',
							'class'    => 'proc-input',
						)
					);
				echo '</ul>';
				echo '</div>';
			}
							$alt = esc_html__( 'Enter a comma delimited list of tags to apply to this post. You may also enter nothing if you wish to clear the current tags instead.', 'seo-bulk-admin' );
							echo '<div class="text-item fcol proc use-tags" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $alt ) . '">';
							echo '<label for="tags">' . esc_html__( 'Tags', 'seo-bulk-admin' ) . '</label>';
							$tags  = array();
							$cloud = wp_tag_cloud(
								array(
									'taxonomy'   => array( 'post_tag' ),
									'echo'       => false,
									'hide_empty' => false,
								)
							);
							$dom   = $this->dom_load( $cloud );
			if ( $dom ) {
				$elements = $dom->getElementsByTagName( 'a' );
				for ( $i = $elements->length; --$i >= 0; ) {
					$node = $elements->item( $i );
					$tag  = $node->nodeValue;// phpcs:ignore WordPress.NamingConventions.ValidVariableName
					if ( ! in_array( $tag, $tags, true ) ) {
						$tags[] = $tag;}
				}
			}
			if ( count( $tags ) > 0 ) {
				echo '<div class="tag-cloud post-details">';
					sort( $tags );
				foreach ( $tags as $tag ) {
					echo '<span class="tag" data-tagname="' . esc_attr( $tag ) . '" onclick="tacwp_postmgr.toggleTagCloud(this);">' . esc_html( $tag ) . '</span>';
				}
				echo '</div>';
			}
							echo '<textarea id="tags" type="text" class="proc-input"  data-proc-type="text" autocomplete="off"></textarea>';
							echo '</div>';
							$alt = esc_html__( 'Check this box to delete the post after assigning the redirect.', 'seo-bulk-admin' );
							echo '<div class="checkbox-item frow proc use-redirect" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $alt ) . '">';
							echo '<input id="redelete" type="checkbox" class="proc-input redirect-checkbox" data-proc-type="checkbox" onchange="tacwp_postmgr.deletePost(this);" autocomplete="off">';
							echo '<label for="redelete">' . esc_html__( 'Delete post', 'seo-bulk-admin' ) . '</label>';
							echo '</div>';
							$alt = esc_html__( 'Check this box to force delete the post, which bypasses the Trash bin.', 'seo-bulk-admin' );
							echo '<div class="checkbox-item frow proc use-delete" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $alt ) . '">';
							echo '<input id="force" type="checkbox" class="proc-input force-checkbox" data-proc-type="checkbox" autocomplete="off">';
							echo '<label for="force">' . esc_html__( 'Force delete', 'seo-bulk-admin' ) . '</label>';
							echo '</div>';
							$alt = esc_html__( 'Check this box to append rather than replace the existing categories or tags.', 'seo-bulk-admin' );
							echo '<div class="checkbox-item frow proc use-append" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $alt ) . '">';
							echo '<input id="append" type="checkbox" class="proc-input append-checkbox" data-proc-type="checkbox" autocomplete="off">';
							echo '<label for="append">' . esc_html__( 'Append', 'seo-bulk-admin' ) . '</label>';
							echo '</div>';
							echo '<div class="process-animation"></div>';
							echo '<div class="process-button frow">';
							echo '<a class="button" href="javascript:tacwp_postmgr.process();">' . esc_html__( 'Run the Process', 'seo-bulk-admin' ) . '</a>';
							echo '</div>';
							echo '</div>';
		}
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Add a string to a string with a delimiter if it is not blank.
	 *
	 * @since 1.0
	 *
	 * @param string $str .
	 * @param string $delim .
	 * @param string $val .
	 * @usage : wp_init
	 * @source core
	 */
	public function delimitit( $str, $delim = '', $val = '' ) {
		if ( '' === $delim ) {
			$delim = ',';}
		if ( '' !== $str ) {
			$str .= $delim;}
		$str .= $val;
		return $str;
	}

	/**
	 * Setup and return a DOMDocument object for HTML parsing.
	 *
	 * @since 1.0
	 *
	 * @param string $input .
	 * @param string $how .
	 * @usage : batch_processing
	 * @source core
	 */
	public function dom_load( $input, $how = 'wrap' ) {
		if ( '' === $input ) {
			return $input;}
		$dom = new DOMDocument();
		libxml_use_internal_errors( true );
		if ( 'wrap' === $how ) {
			$input = '<domdoc>' . $input . '</domdoc>';}
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $input, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		return $dom;
	}

	/**
	 * Get a value from an array or object across nested levels of depth.
	 *
	 * @since 1.0
	 *
	 * @param string $incoming .
	 * @param string $input .
	 * @param string $def .
	 * @requires : object_to_array
	 * @usage : batch_processing, wp_init
	 * @source core
	 */
	public function get_value( $incoming, $input, $def = '' ) {
		if ( is_object( $incoming ) && is_array( $input ) ) {
			$incoming = $this->object_to_array( $incoming );}
		if ( is_object( $incoming ) ) {
			if ( isset( $incoming->$input ) ) {
				return $incoming->$input;
			}
		} elseif ( is_array( $input ) ) {
			if ( count( $input ) > 0 ) {
				$tar = $incoming;
				foreach ( $input as $far ) {
					if ( isset( $tar[ $far ] ) ) {
						$tar = $tar[ $far ];
					} else {
						return $def;
					}
				}
				return $tar;
			}
		} elseif ( isset( $incoming[ $input ] ) ) {
				return $incoming[ $input ];
		}
		return $def;
	}

	/**
	 * Initialize actions, filters, hooks and scripts needed within the project.
	 *
	 * @since 1.0
	 *
	 * @requires : admin_init, admin_menu, wp_init, ajax_setup, plugin_action_links
	 * @source ext
	 */
	public function init() {
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'init', array( &$this, 'wp_init' ), 10 );
		add_action( 'after_setup_theme', array( &$this, 'ajax_setup' ), 1 );
		add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );
	}

	/**
	 * Check if data from a checkbox is true or false covering a number of possible values.
	 *
	 * @since 1.0
	 *
	 * @param string $val .
	 * @usage : admin_ajax_handler
	 * @source core
	 */
	public function is_checked( $val ) {
		if ( 'off' === $val ) {
			return false;}
		if ( '1' === $val || 1 === $val || 'true' === $val || true === $val || 'on' === $val ) {
			return true;}
		return false;
	}

	/**
	 * Check if WooCommerce is installed and active.
	 *
	 * @since 1.0
	 *
	 * @usage : ajax_data_object, batch_processing
	 * @source ext
	 */
	public function is_woo() {
		if ( class_exists( 'WooCommerce' ) ) {
			return true;}
		return false;
	}

	/**
	 * Create and return nonce value across methods in this class object.
	 *
	 * @since 1.0
	 *
	 * @usage : ajax_data_object
	 * @source ext
	 */
	public function nonce() {
		if ( ! isset( $this->self['ajax_nonce'] ) ) {
			$this->self['ajax_nonce'] = wp_create_nonce( 'tcgMsoErHlsQbxoSe' );
		}
		return $this->self['ajax_nonce'];
	}

	/**
	 * Convert a PHP object to an array.
	 *
	 * @since 1.0
	 *
	 * @param string $d .
	 * @usage : get_value
	 * @source core
	 */
	public function object_to_array( $d ) {
		if ( is_object( $d ) ) {
			$d = get_object_vars( $d );
		}if ( is_array( $d ) ) {
			return array_map( array( $this, 'object_to_array' ), $d );
		} else {
			return $d;}
	}

	/**
	 * Add links and status indicator to activation links on the plugin page.
	 *
	 * @since 1.0
	 *
	 * @param string $actions .
	 * @param string $file .
	 * @usage : init
	 * @source ext
	 */
	public function plugin_action_links( $actions, $file ) {
		$arr  = array();
		$base = $this->self['FOLDER'] . '/functions.php';
		if ( $file === $base ) {
			if ( ! function_exists( 'tacwp_postmgr_process_options' ) ) {
				$arr[] = '<a href="https://ampwptools.com/seo-bulk-admin" target="_blank" style="color:darkblue;font-weight:bold;">' . esc_html__( 'Upgrade to Pro', 'seo-bulk-admin' ) . '</a>';
			}
		}
		return array_merge( $actions, $arr );
	}

	/**
	 * Output the contents of the Pro tab.
	 *
	 * @since 1.0
	 *
	 * @usage : admin_page
	 * @source ext
	 */
	public function pro() {
		echo '<div class="promessage fcol" style="align-items:flex-start;gap:20px;">';
			echo '<div class="promode pro-upgrade">';
				echo '<span class="pro-headline">' . esc_html__( 'Get Rid of Website Maintenance Headaches With SEO Bulk Admin Pro', 'seo-bulk-admin' ) . '</span>';
				echo '<span class="pro-features">';
					echo '<span class="pro-feature"><span class="pro-icon fa-regular fa-circle-check"></span>' . esc_html( 'Batch Processing: Single/bulk redirects for Posts, Pages, and Products' ) . '</span>';
					echo '<span class="pro-feature"><span class="pro-icon fa-regular fa-circle-check"></span>' . esc_html( 'Redirect Manager: Track all redirects - Easily update or delete redirects' ) . '</span>';
					echo '<span class="pro-feature"><span class="pro-icon fa-regular fa-circle-check"></span>' . esc_html( '404 Monitoring: 404 URL reporting with hit counts' ) . '</span>';
					echo '<span class="pro-feature"><span class="pro-icon fa-regular fa-circle-check"></span>' . esc_html( '404 Handling: Single/bulk error management and redirection' ) . '</span>';
					echo '<span class="pro-feature"><span class="pro-icon fa-regular fa-circle-check"></span>' . esc_html( 'CSV report exports and more!' ) . '</span>';
				echo '</span>';
				echo '<a href="https://ampwptools.com/seo-bulk-admin" target="_blank" class="pro-button" style="">' . esc_html__( 'Visit the site for details', 'seo-bulk-admin' ) . '</a>';
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Query the post table for results from the Batch Processing search form.
	 *
	 * @since 1.0
	 *
	 * @requires : sanitize_get_variable
	 * @usage : batch_processing, wp_init
	 * @source ext
	 */
	public function query_posts() {
		$before      = $this->sanitize_get_variable( 'before' );
		$after       = $this->sanitize_get_variable( 'after' );
		$column      = $this->sanitize_get_variable( 'column', 'post_date' );
		$category    = $this->sanitize_get_variable( 'category' );
		$woocategory = $this->sanitize_get_variable( 'woocategory' );
		$search      = $this->sanitize_get_variable( 'search' );
		$searchby    = $this->sanitize_get_variable( 'searchby' );
		$orderby     = $this->sanitize_get_variable( 'orderby' );
		$bytype      = $this->sanitize_get_variable( 'bytype' );
		$range       = array();
		if ( '' !== $before ) {
			$range['before'] = $before;}
		if ( '' !== $after ) {
			$range['after'] = $after;}
		$bycolumn = $column;
		if ( '' !== $orderby ) {
			$order = 'DESC';
			if ( 'date' === $orderby ) {
				$order = 'ASC';}
			if ( 'az' === $orderby ) {
				$bycolumn = 'post_title';
				$order    = 'ASC';}
			if ( 'za' === $orderby ) {
				$bycolumn = 'post_title';
				$order    = 'DESC';}
		} else {
			$bycolumn = 'id';
			$order    = 'DESC';
		}
		$runit = false;
		if ( count( $range ) > 0 ) {
			$runit = true;}
		if ( '' !== $category ) {
			$runit = true;}
		if ( '' !== $search ) {
			$runit = true;}
		if ( '' !== $bytype ) {
			$runit = true;}
		if ( $runit ) {
			$args = array(
				'posts_per_page' => -1,
				'orderby'        => $bycolumn,
				'order'          => $order,
			);
			if ( '' === $bytype ) {
				$bytpe = 'post';}
			$args['post_type'] = $bytype;
			if ( count( $range ) > 0 ) {
				$range['column']    = $column;
				$args['date_query'] = $range;
			}
			$taxqry = array();
			if ( 'product' === $bytype ) {
				if ( '' !== $woocategory ) {
					$taxqry[] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $woocategory,
					);
				}
			} elseif ( '' !== $category ) {
					$args['category_name'] = $category;
			}
			if ( 'all' !== strtolower( $search ) ) {
				if ( '' !== $search ) {
					if ( 'tag' === $searchby ) {
						$taxqry[] = array(
							'taxonomy' => 'post_tag',
							'field'    => 'slug',
							'terms'    => $search,
						);
					} else {
						$args['s'] = $search;
						if ( 'title' === $searchby ) {
							$args['search_columns'] = array( 'post_title' );
						} elseif ( 'content' === $searchby ) {
							$args['search_columns'] = array( 'post_content' );
						}
					}
				}
			}
			if ( count( $taxqry ) > 0 ) {
				$args['tax_query'] = $taxqry;// phpcs:ignore
			}
			$query = new WP_Query( $args );
			wp_reset_postdata();
			if ( $query->have_posts() ) {
				return $query->posts;
			}
		}
		return array();
	}

	/**
	 * Generate the admin.php URl from parameters used by batch processing.
	 *
	 * @since 1.0
	 *
	 * @param array $args .
	 * @requires : sanitize_get_variable
	 * @usage : batch_processing
	 * @source ext
	 */
	public function query_url( $args = array() ) {
		$before      = $this->sanitize_get_variable( 'before' );
		$after       = $this->sanitize_get_variable( 'after' );
		$column      = $this->sanitize_get_variable( 'column', 'post_date' );
		$category    = $this->sanitize_get_variable( 'category' );
		$woocategory = $this->sanitize_get_variable( 'woocategory' );
		$search      = $this->sanitize_get_variable( 'search' );
		$searchby    = $this->sanitize_get_variable( 'searchby' );
		$orderby     = $this->sanitize_get_variable( 'orderby' );
		$bytype      = $this->sanitize_get_variable( 'bytype' );
		$bytarget    = $this->sanitize_get_variable( 'bytarget' );
		$bycode      = $this->sanitize_get_variable( 'bycode' );
		$urlargs     = array( 'page' => 'seobulkadmin' );
		if ( '' !== $after ) {
			$urlargs['after'] = $after;}
		if ( '' !== $before ) {
			$urlargs['before'] = $before;}
		if ( '' !== $category ) {
			$urlargs['category'] = $category;}
		if ( 'post_date' !== $column ) {
			$urlargs['column'] = $column;}
		if ( '' !== $search ) {
			$urlargs['search'] = $search;}
		if ( '' !== $searchby ) {
			$urlargs['searchby'] = $searchby;}
		if ( '' !== $orderby ) {
			$urlargs['orderby'] = $orderby;}
		if ( '' !== $bytype ) {
			$urlargs['bytype'] = $bytype;}
		if ( '' !== $bytarget ) {
			$urlargs['bytarget'] = $bytarget;}
		if ( '' !== $bycode ) {
			$urlargs['bycode'] = $bycode;}
		if ( count( $args ) > 0 ) {
			foreach ( $args as $k => $v ) {
				$urlargs[ $k ] = $v;
			}
		}
		return add_query_arg( $urlargs, admin_url( 'admin.php' ) );
	}

	/**
	 * Process GET and POST objects to return sanitized for use by ajax requests and systems.
	 *
	 * @since 1.0
	 *
	 * @param string $input .
	 * @param string $def .
	 * @usage : admin_ajax_handler
	 * @source core
	 */
	public function sanitize_ajax_value( $input, $def = '' ) {
		if ( 'action' === $input || 'how' === $input || 'security' === $input ) {
			if(isset($_GET[$input])){// phpcs:ignore
				return sanitize_text_field( wp_unslash( $_GET[$input] ) );// phpcs:ignore
			}
			if(isset($_POST[$input])){// phpcs:ignore
				return sanitize_text_field( wp_unslash( $_POST[$input] ) );// phpcs:ignore
			}
		} else {
			if(isset($_GET['pass']) && isset($_GET['pass'][$input])){// phpcs:ignore
				if(is_array($_GET['pass'][$input])){// phpcs:ignore
					return array_map( 'sanitize_text_field', wp_unslash( $_GET['pass'][$input] ) );// phpcs:ignore
				} else {
					return sanitize_text_field( wp_unslash( $_GET['pass'][$input] ) );// phpcs:ignore
				}
			}
			if(isset($_POST['pass']) && isset($_POST['pass'][$input])){// phpcs:ignore
				if(is_array($_POST['pass'][$input])){// phpcs:ignore
					return array_map( 'sanitize_text_field', wp_unslash( $_POST['pass'][$input] ) );// phpcs:ignore
				} else {
					return sanitize_text_field( wp_unslash( $_POST['pass'][$input] ) );// phpcs:ignore
				}
			}
		}
		return $def;
	}

	/**
	 * Return sanitized GET value.
	 *
	 * @since 1.0
	 *
	 * @param string $input .
	 * @param string $def .
	 * @usage : admin_page, batch_processing, query_posts, query_url, wp_init
	 * @source core
	 */
	public function sanitize_get_variable( $input, $def = '' ) {
		if(isset($_GET[$input])){// phpcs:ignore
				$def = sanitize_text_field( wp_unslash( $_GET[$input] ) );// phpcs:ignore
		}
		return $def;
	}

	/**
	 * Action hook method for "init". Export results from batch processing and redirects in CSV format when activated from the admin page.
	 *
	 * @since 1.0
	 *
	 * @requires : sanitize_get_variable, query_posts, get_value, delimitit
	 * @usage : init
	 * @source ext
	 */
	public function wp_init() {
		global $pagenow;
		if('admin.php'===$pagenow && 'seobulkadmin'===$this->sanitize_get_variable('page') && ''!==$this->sanitize_get_variable('exportcsv')){// phpcs:ignore
			$arr      = array();
			$filename = 'SEO Bulk Admin Results.csv';
			$column   = $this->sanitize_get_variable( 'column', 'post_date' );
			$posts    = $this->query_posts();
			if ( count( $posts ) > 0 ) {
				foreach ( $posts as $pid => $data ) {
					$post_id = $this->get_value( $data, 'ID' );
					if ( '' === $post_id ) {
						continue;}
					$post_name  = $this->get_value( $data, 'post_name' );
					$post_title = $this->get_value( $data, 'post_title' );
					$post_type  = $this->get_value( $data, 'post_type' );
					$post_date  = $this->get_value( $data, $column );
					$editurl    = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
					$srcurl     = get_the_permalink( $post_id );
					$pro        = array();
					$pro[]      = $post_id;
					$pro[]      = $post_title;
					$pro[]      = $post_name;
					$pro[]      = $post_date;
					$pro[]      = $srcurl;
					$pro[]      = $editurl;
					$arr[]      = $pro;
				}
			}
			$datettl = 'Date Published';
			if ( 'post_modified' === $column ) {
				$datettl = 'Date Modified';}
			header( 'Cache-Control: public' );
			header( 'Content-Type: text/csv' );
			header( 'Content-Transfer-Encoding: Binary' );
			header( "Content-Disposition: attachment; filename=$filename" );
			echo 'ID,Title,Slug,' . esc_attr( $datettl ) . ',View,Edit';
			foreach ( $arr as $row ) {
				echo '
';
				$txt = '';
				foreach ( $row as $val ) {
					$newval = $val;
					if ( '' !== $val ) {
						$newval = '"' . $val . '"';}
					$txt = $this->delimitit( $txt, ',', $newval );
				}
				echo esc_html( $txt );
			}
			die();
		}
	}
}
