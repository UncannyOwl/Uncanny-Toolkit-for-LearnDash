<?php

namespace uncanny_learndash_toolkit;

/**
 * Class learndashBreadcrumbs
 * @package uncanny_custom_toolkit
 */
class Breadcrumbs extends Config implements RequiredFunctions {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 */
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {

			/* ADD FILTERS ACTIONS FUNCTION */
			add_shortcode( 'uo_breadcrumbs', array( __CLASS__, 'uo_breadcrumbs' ) );
			//Disable WP SEO breadcrumbs
			add_filter( 'wpseo_breadcrumb_output', array( __CLASS__, 'wpseo_uo_breadcrumbs' ) );
			
			// Enhance LD 3.x breadcrumb
			add_filter( 'learndash_breadcrumbs', array( __CLASS__, 'uo_learndash_breadcrumbs' ) );
			add_filter( 'learndash_breadcrumbs_keys', array( __CLASS__, 'uo_breadcrumbs_keys' ) );
		}

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist() {
		/* Checks for LearnDash */
		global $learndash_post_types;

		if ( ! isset( $learndash_post_types ) ) {
			return 'Plugin: LearnDash';
		}

		return true;
	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id   = 'breadcrumbs';

		$class_title = esc_html__( 'Breadcrumbs', 'uncanny-learndash-toolkit' );

		$kb_link = 'https://www.uncannyowl.com/knowledge-base/learndash-breadcrumb-links/';

		/* Sample Simple Description with shortcode */
		$class_description = esc_html__( 'Easily add breadcrumb links that work with courses, lessons, topics and quizzes using a shortcode or template change. WooCommerce is also supported.', 'uncanny-learndash-toolkit' );

		/* Icon as font awesome icon */
		$class_icon = '<i class="uo_icon_fa fa fa-link"></i>';
		$category          = 'wordpress';
		$type       = 'free';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
			'kb_link'          => $kb_link, // OR set as null not to display
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings'         => self::get_class_settings( $class_title ),
			//'settings'         => false,
			'icon'             => $class_icon,
		);
	}


	/**
	 * HTML for modal to create settings
	 *
	 * @param String
	 *
	 * @return string Return either false or settings html modal
	 *
	 */
	public static function get_class_settings( $class_title ) {
		$pages[]   = array( 'value' => 0, 'text' => '-- Select Page --' );
		$get_pages = get_pages(
			array(
				'sort_order'  => 'asc',
				'sort_column' => 'post_title',
			) );
		foreach ( $get_pages as $page ) {
			$pages[] = array( 'value' => $page->ID, 'text' => get_the_title( $page->ID ) );
		}

		// Create options
		$options = array(

			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Dashboard Page Title', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uncanny-breadcrumbs-dashboard-text',
			),
			array(
				'type'        => 'select',
				'label'       => esc_html__( 'Dashboard Page', 'uncanny-learndash-toolkit' ),
				'select_name' => 'uncanny-breadcrumbs-dashboard-link',
				'options'     => $pages,
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Breadcrumbs Separator', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uncanny-breadcrumbs-dashboard-separator',
			),
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Include Current Page', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uncanny-breadcrumbs-include-current-page',
			),
		);

		// Build html
		$html = self::settings_output(
			array(
				'class'   => __CLASS__,
				'title'   => $class_title,
				'options' => $options,
			) );

		return $html;
	}

	/**
	 * @return mixed
	 */
	public static function wpseo_uo_breadcrumbs() {
		return self::uo_breadcrumbs();
	}

	/**
	 * @param bool $echo
	 *
	 * @return string
	 */
	public static function uo_breadcrumbs() {
		global $wp_query;
		//$wp_query = new WP_Query();
		$learn_dash_labels = new \LearnDash_Custom_Label();
		$course_label      = $learn_dash_labels::get_label( 'courses' );
		// Define main variables
		$trail   = array();
		$trail[] = self::uo_build_anchor_links( get_bloginfo( 'url' ), esc_html__( 'Home', 'uncanny-learndash-toolkit' ), 'get-blog-info-url' );
		//$dashboard_link      = get_post_type_archive_link( 'sfwd-courses' );
		$dashboard_link      = '';
		$dashboard_text      = 'Dashboard';
		$dashboard_separator = '&raquo;';

		$get_dashboard_text       = self::get_settings_value( 'uncanny-breadcrumbs-dashboard-text', __CLASS__ );
		$get_dashboard_link       = self::get_settings_value( 'uncanny-breadcrumbs-dashboard-link', __CLASS__ );
		$get_dashboard_separator  = self::get_settings_value( 'uncanny-breadcrumbs-dashboard-separator', __CLASS__ );
		$get_include_current_page = self::get_settings_value( 'uncanny-breadcrumbs-include-current-page', __CLASS__ );
		$course_archive_link      = self::uo_build_anchor_links( get_post_type_archive_link( 'sfwd-courses' ), esc_html__( $course_label, 'uncanny-learndash-toolkit' ), 'course-archive-link' );
		//$course_archive_link     = self::uo_build_anchor_links( get_post_type_archive_link( 'sfwd-courses' ), esc_html__( 'Courses', 'uncanny-learndash-toolkit' ) );

		if ( strlen( trim( $get_dashboard_text ) ) ) {
			$dashboard_text = $get_dashboard_text;
		}

		if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
			$dashboard_link = get_permalink( $get_dashboard_link );
			$dashboard_link = self::uo_build_anchor_links( $dashboard_link, $dashboard_text, 'dashboard-link-no-get-dashboard-link' );
		}

		if ( strlen( trim( $get_dashboard_separator ) ) ) {
			$dashboard_separator = $get_dashboard_separator;
		}
		$lesson_id = false;

		// If it's on home page
		if ( is_front_page() ) {
			$trail = array(); //Removing Single Home link from Homepage.
		} elseif ( is_singular() ) {
			// Get singular vars (page, post, attachments)
			$post      = $wp_query->get_queried_object();
			$post_id   = absint( $wp_query->get_queried_object_id() );
			$post_type = $post->post_type;

			if ( 'post' === $post_type ) {
				$maybe_tax = self::uo_post_taxonomy( $post_id );

				if ( false !== $maybe_tax ) {
					$trail[] = $maybe_tax;
				}
				$trail[] = get_the_title( $post_id );

			} elseif ( 'page' === $post_type ) {
				// If Woocommerce is installed and being viewed, add shop page to cart, checkout pages
				if ( class_exists( 'Woocommerce' ) ) {

					if ( is_cart() || is_checkout() ) {
						// Get shop page
						if ( function_exists( 'wc_get_page_id' ) ) {
							$shop_id    = wc_get_page_id( 'shop' );
							$shop_title = get_the_title( $shop_id );
							if ( function_exists( 'wpml_object_id' ) ) {
								$shop_title = get_the_title( wpml_object_id( $shop_id, 'page' ) );
							}
							// Shop page
							if ( $shop_id && $shop_title ) {
								$trail[] = self::uo_build_anchor_links( get_permalink( $shop_id ), $shop_title, 'woo-shop-page' );
							}
						}
					}
					if ( $get_include_current_page === 'on' ) {
						$trail[] = '<span class="uo-current_page">' . get_the_title( $post_id ) . '</span>';
					}
				} else {
					// Regular pages. See if the page has any ancestors. Add in the trail if ancestors are found
					$ancestors = get_ancestors( $post_id, 'page' );
					if ( ! empty ( $ancestors ) ) {
						$ancestors = array_reverse( $ancestors );
						foreach ( $ancestors as $page ) {
							$trail[] = self::uo_build_anchor_links( get_permalink( $page ), get_the_title( $page ), 'regular-page-ancestors' );
						}
					}
					if ( $get_include_current_page === 'on' ) {
						$trail[] = '<span class="uo-current_page">' . get_the_title( $post_id ) . '</span>';
					}
				}
			} elseif ( 'sfwd-courses' === $post_type ) {
				// See if Single Course is being displayed.
				if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
					$trail[] = $dashboard_link;
				} else {
					$trail[] = $course_archive_link;
				}
				if ( $get_include_current_page === 'on' ) {
					$trail[] = '<span class="uo-current_page">' . get_the_title( $post_id ) . '</span>';
				}
			} elseif ( 'sfwd-lessons' === $post_type ) {
				// See if Single Lesson is being displayed.
				$course_id = learndash_get_course_id( $post_id );  // Getting Parent Course ID
				if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
					$trail[] = $dashboard_link;
				} else {
					$trail[] = $course_archive_link;
				}
				$trail[] = self::uo_build_anchor_links( get_permalink( $course_id ), get_the_title( $course_id ), 'lessons-course-link' ); // Getting Lesson's Course Link

				if ( $get_include_current_page === 'on' ) {
					$trail[] = '<span class="uo-current_page">' . get_the_title( $post_id ) . '</span>';
				}
			} elseif ( 'sfwd-topic' === $post_type ) {
				// See if single Topic is being displayed
				$course_id = learndash_get_course_id( $post_id ); // Getting Parent Course ID
				$lesson_id = learndash_get_lesson_id( $post_id, $course_id ); // Getting Parent Lesson ID
				if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
					$trail[] = $dashboard_link;
				} else {
					$trail[] = $course_archive_link;
				}
				$trail[] = self::uo_build_anchor_links( get_permalink( $course_id ), get_the_title( $course_id ), 'topics-course-link' ); // Getting Lesson's Course Link
				$trail[] = self::uo_build_anchor_links( get_permalink( $lesson_id ), get_the_title( $lesson_id ), 'topics-lesson-link' ); // Getting Topics's Lesson Link
				if ( $get_include_current_page === 'on' ) {
					$trail[] = '<span class="uo-current_page">' . get_the_title( $post_id ) . '</span>';
				}
			} elseif ( 'sfwd-quiz' === $post_type ) {
				// See if quiz is being displayed
				$course_id = learndash_get_course_id( $post_id ); // Getting Parent Course ID
				if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
					$trail[] = $dashboard_link;
				} else {
					$trail[] = $course_archive_link;
				}

				$topic_id = learndash_get_lesson_id( $post_id, $course_id ); // Getting Parent Topic/Lesson ID

				if ( 'sfwd-topic' === get_post_type( $topic_id ) ) {
					$lesson_id = learndash_get_lesson_id( $topic_id, $course_id ); // Getting Parent Lesson ID
				} else {
					// detect topic id & grab lesson id
					$parent_ids = learndash_course_get_all_parent_step_ids( $course_id, $post_id );
					if ( ! empty( $parent_ids ) ) {
						foreach ( $parent_ids as $parent_id ) {
							if ( get_post_type( $parent_id ) === learndash_get_post_type_slug( 'topic' ) ) {
								$topic_id = $parent_id;
								$lesson_id = learndash_get_lesson_id( $topic_id, $course_id ); // Getting Parent Lesson ID
								break;
							}
						}
					}
				}

				$trail[] = self::uo_build_anchor_links( get_permalink( $course_id ), get_the_title( $course_id ), 'quizs-course-link' ); // Getting Lesson's Course Link
				//If $lesson_id is false, the quiz is associated with a lesson and course but not a topic.
				if ( $lesson_id ) {
					$trail[] = self::uo_build_anchor_links( get_permalink( $lesson_id ), get_the_title( $lesson_id ), 'quizs-lesson-link' ); // Getting Topics's Lesson Link
				}
				//If $topic_id is false, the quiz is associated with a course but not associated with any lessons or topics.
				if ( $topic_id ) {
					$trail[] = self::uo_build_anchor_links( get_permalink( $topic_id ), get_the_title( $topic_id ), 'quizs-topic-link' );
				}
				if ( $get_include_current_page === 'on' ) {
					$trail[] = '<span class="uo-current_page">' . get_the_title( $post_id ) . '</span>';
				}

			} else {
				// Add shop page to single product
				if ( 'product' === $post_type ) {
					// Get shop page
					if ( class_exists( 'Woocommerce' ) && function_exists( 'wc_get_page_id' ) ) {
						$shop_id    = wc_get_page_id( 'shop' );
						$shop_title = get_the_title( $shop_id );
						if ( function_exists( 'wpml_object_id' ) ) {
							$shop_title = get_the_title( wpml_object_id( $shop_id, 'page' ) );
						}

						// Shop page
						if ( $shop_id && $shop_title ) {
							$trail[] = self::uo_build_anchor_links( get_permalink( $shop_id ), $shop_title, 'products-woo-shop-page' );
						}
					}
				}

				// Getting terms of the post.
				if ( self::lms_get_taxonomy( $post_id, $post_type ) ) {
					$trail[] = self::lms_get_taxonomy( $post_id, $post_type );
				}
				if ( $get_include_current_page === 'on' ) {
					$trail[] = '<span class="uo-current_page">' . get_the_title( $post_id ) . '</span>';
				}
			}
		}
		// If it's an Archive
		if ( is_archive() ) {
			//Ignore if Courses & Products
			if ( ! is_post_type_archive( 'sfwd-courses' ) && ! is_post_type_archive( 'product' ) ) {
				if ( is_category() || is_tax() ) {
					$trail[] = single_cat_title( '', false ); // If its Blog Category
				}
				if ( is_day() ) {
					$trail[] = get_the_date(); // If its Single Day Archive
				}
				if ( is_month() ) {
					$trail[] = get_the_date( __( 'F Y', 'uncanny-learndash-toolkit' ) ) . esc_html__( ' Archives', 'uncanny-learndash-toolkit' ); // If Mothly Archives
				}
				if ( is_year() ) {
					$trail[] = get_the_date( __( 'Y', 'uncanny-learndash-toolkit' ) ) . esc_html__( ' Archives', 'uncanny-learndash-toolkit' ); // If its Yearly Archives
				}
				if ( is_author() ) {
					$trail[] = get_the_author(); // If its Author's Archives
				}
			} elseif ( is_post_type_archive( 'sfwd-courses' ) ) {
				$trail[] = esc_html__( $course_label, 'uncanny-learndash-toolkit' );
			} elseif ( is_post_type_archive( 'product' ) ) {
				$trail[] = esc_html__( 'Shop', 'uncanny-learndash-toolkit' );
			}
		}

		if ( is_search() ) {
			$trail[] = esc_html__( 'Search', 'uncanny-learndash-toolkit' );
			$trail[] = get_search_query();
		}

		// Build breadcrumbs
		$classes = 'sfwd-breadcrumbs clr';

		if ( array_key_exists( 'the_content', $GLOBALS['wp_filter'] ) ) {
			$classes .= ' lms-breadcrumbs ';
		}

		// Open breadcrumbs
		$breadcrumb = '<nav aria-label="' . esc_html__( 'Breadcrumb', 'uncanny-learndash-toolkit' ) . '" class="' . esc_attr( $classes ) . '"><div class="breadcrumb-trail">';

		// Separator HTML
		$separator = '<span class="sep"> ' . stripslashes( $dashboard_separator ) . ' </span>';

		// Join all trail items into a string
		$breadcrumb .= implode( $separator, $trail );

		// Close breadcrumbs
		$breadcrumb .= '</div></nav>';

		return $breadcrumb;
	}

	/**
	 * @param $permalink
	 * @param $title
	 * @param $type
	 *
	 * @return mixed
	 */
	public static function uo_build_anchor_links( $permalink, $title, $type = '' ) {

		$link = sprintf(
			'<span itemscope="" itemtype="http://schema.org/Breadcrumb"><a href="%1$s" title="%2$s" rel="%3$s" class="trail-begin"><span itemprop="%2$s">%4$s</span></a></span>',
			esc_url( $permalink ),
			esc_attr( $title ),
			sanitize_title( $title ),
			esc_html( $title )
		);

		return apply_filters( 'uo_build_anchor_links', $link, $permalink, $title, $type );

	}

	/**
	 * @param        $post_id
	 * @param string $taxonomy
	 *
	 * @return bool
	 */
	public static function uo_post_taxonomy( $post_id, $taxonomy = 'category' ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		$t     = array();
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$t[] = self::uo_build_anchor_links( get_term_link( $term->slug, $taxonomy ), $term->name );
			}

			return implode( ' / ', $t );
		} else {
			return false;
		}
	}

	/**
	 * @param $post_id
	 * @param $post_type
	 *
	 * @return bool
	 */
	public static function lms_get_taxonomy( $post_id, $post_type ) {
		$taxonomies = get_taxonomies( array( 'object_type' => array( $post_type ) ), 'objects' );
		$tax        = array();
		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				// Pass the $taxonomy name to uo_post_taxonomy to return with proper terms and links
				$tax[] = self::uo_post_taxonomy( $post_id, $taxonomy->query_var );
			}

			return implode( ' / ', $tax );
		} else {
			return false;
		}
	}


	/**
	 * @return null
	 */
	public static function uo_get_the_id() {
		// If singular get_the_ID
		if ( is_singular() ) {
			return get_the_ID();
		} // Get ID of WooCommerce product archive
		elseif ( is_post_type_archive( 'product' ) && class_exists( 'Woocommerce' ) && function_exists( 'wc_get_page_id' ) ) {
			$shop_id = wc_get_page_id( 'shop' );
			if ( isset( $shop_id ) ) {
				return wc_get_page_id( 'shop' );
			}
		} // Posts page
		elseif ( is_home() && $page_for_posts = get_option( 'page_for_posts' ) ) {
			return $page_for_posts;
		} // Return nothing
		else {
			return null;
		}

		return null;
	}
	
	/**
	 * @param $keys
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	public static function uo_breadcrumbs_keys( $keys ){
		
		$dashboard_link = '';
		$dashboard_text      = 'Dashboard';
		$get_dashboard_text       = self::get_settings_value( 'uncanny-breadcrumbs-dashboard-text', __CLASS__ );
		$get_dashboard_link       = self::get_settings_value( 'uncanny-breadcrumbs-dashboard-link', __CLASS__ );
		$get_dashboard_separator  = self::get_settings_value( 'uncanny-breadcrumbs-dashboard-separator', __CLASS__ );
		
		if ( strlen( trim( $get_dashboard_text ) ) ) {
			$dashboard_text = $get_dashboard_text;
		}
		
		if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
			$dashboard_link = get_permalink( $get_dashboard_link );
		}
		
		if ( ! empty( $dashboard_link ) && ! empty( $dashboard_text ) ) {
			$keys = array_merge(['dashboard'],$keys);
		}
		
		return $keys;
	}
	
	/**
	 * @param $breadcrumbs
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	public static function uo_learndash_breadcrumbs( $breadcrumbs ){
		$dashboard_link          = '';
		$dashboard_text          = 'Dashboard';
		$get_dashboard_text      = self::get_settings_value( 'uncanny-breadcrumbs-dashboard-text', __CLASS__ );
		$get_dashboard_link      = self::get_settings_value( 'uncanny-breadcrumbs-dashboard-link', __CLASS__ );
		$get_dashboard_separator = self::get_settings_value( 'uncanny-breadcrumbs-dashboard-separator', __CLASS__ );
		
		if ( strlen( trim( $get_dashboard_text ) ) ) {
			$dashboard_text = $get_dashboard_text;
		}
		
		if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
			$dashboard_link = get_permalink( $get_dashboard_link );
		}
		
		if ( ! empty( $dashboard_link ) && ! empty( $dashboard_text ) ) {
			$breadcrumbs['dashboard'] = [
				'permalink' => $dashboard_link,
				'title'     => $dashboard_text,
			];
		}
		
		return $breadcrumbs;
	}
}