<?php

namespace JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc;

use WP_List_Table;
use PXLBSAdminify\Inc\Utils;
// no direct access allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPAdminify
 *
 * @package Activity Logs
 *
 * @author Jewel Theme <support@jeweltheme.com>
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


class Adminify_Activity_Log_List_Table extends WP_List_Table {

	public $url;

	// User Roles
	protected $_roles = [];

	// Capabilities
	protected $_caps = [];

	protected $_allow_caps = [];

	public function __construct( $args = [] ) {
		global $wp_roles;

		if( defined('PXLBSADMINIFY_URL')){
			$this->url = PXLBSADMINIFY_URL . 'Inc/Modules/ActivityLogs';
		}else{
			$this->url = ACTIVITYLOGS_URL . 'Inc/Modules/ActivityLogs';
		}

		parent::__construct(
			[
				'singular' => 'activity',
				'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
			]
		);

		$this->_roles = apply_filters(
			'adminify_activity_logs_roles',
			[
				// admin
				'manage_options' => [
					'Core',
					'Export',
					'Post',
					'Taxonomy',
					'User',
					'Options',
					'Attachment',
					'Plugin',
					'Widget',
					'Theme',
					'Menu',
					'Comments',
				],

				// editor
				'edit_pages'     => [
					'Post',
					'Taxonomy',
					'Attachment',
					'Comments',
				],
			]
		);

		$default_rules = [ 'administrator', 'editor', 'author', 'guest' ];

		$all_roles = [];
		foreach ( $wp_roles->roles as $key => $wp_role ) {
			$all_roles[] = $key;
		}

		$this->_caps = apply_filters(
			'adminify_activity_logs_caps',
			[
				'administrator' => array_unique( array_merge( $default_rules, $all_roles ) ),
				'editor'        => [ 'editor', 'author', 'guest' ],
				'author'        => [ 'author', 'guest' ],
			]
		);

		add_screen_option(
			'per_page',
			[
				'default' => 50,
				'label'   => esc_html__( 'Activities', 'adminify-activity-logs' ),
				'option'  => 'edit_adminify_activity_logs_per_page',
			]
		);

		add_filter( 'set-screen-option', [ $this, 'adminify_activity_logs_set_screen_option' ], 10, 3 );
		set_screen_options();
	}


	public function get_columns() {
		$columns = [
			'date'        => esc_html__( 'Date', 'adminify-activity-logs' ),
			'author'      => esc_html__( 'Author', 'adminify-activity-logs' ),
			'ip'          => esc_html__( 'IP Address', 'adminify-activity-logs' ),
			'type'        => esc_html__( 'Type', 'adminify-activity-logs' ),
			'label'       => esc_html__( 'Label', 'adminify-activity-logs' ),
			'action'      => esc_html__( 'Action', 'adminify-activity-logs' ),
			'description' => esc_html__( 'Description', 'adminify-activity-logs' ),
			'delete'      => esc_html__( 'Delete', 'adminify-activity-logs' ),
		];

		return $columns;
	}

	public function get_sortable_columns() {
		return [
			'ip'   => 'log_ip',
			'date' => [ 'log_time', true ],
		];
	}

	public function get_action_label( $action ) {
		return ucwords( str_replace( '_', ' ', $action ) );
	}

	protected function _get_where_by_role() {
		$allow_modules = [];

		foreach ( $this->_roles as $key => $role ) {
			if ( current_user_can( $key ) || current_user_can( 'view_all_adminify_activity_logs' ) ) {
				$allow_modules = array_merge( $allow_modules, $role );
			}
		}

		if ( empty( $allow_modules ) ) {
			wp_die( 'Not allowed here.' );
		}

		$allow_modules = array_unique( $allow_modules );

		$where = [];
		foreach ( $allow_modules as $type ) {
			$where[] .= '`object_type` = \'' . $type . '\'';
		}

		$where_caps = [];
		foreach ( $this->_get_allow_caps() as $cap ) {
			$where_caps[] .= '`user_caps` = \'' . $cap . '\'';
		}

		return 'AND (' . implode( ' OR ', $where ) . ') AND (' . implode( ' OR ', $where_caps ) . ')';
	}

	protected function _get_allow_caps() {
		if ( empty( $this->_allow_caps ) ) {
			$user = get_user_by( 'id', get_current_user_id() );
			if ( ! $user ) {
				wp_die( 'Not allowed here.' );
			}

			$user_cap   = strtolower( key( $user->caps ) );
			$allow_caps = [];

			foreach ( $this->_caps as $key => $cap_allow ) {
				if ( $key === $user_cap ) {
					$allow_caps = array_merge( $allow_caps, $cap_allow );

					break;
				}
			}

			// TODO: Find better way to Multisite compatibility.
			if ( is_super_admin() || current_user_can( 'view_all_adminify_activity_logs' ) ) {
				$allow_caps = $this->_caps['administrator'];
			}

			if ( empty( $allow_caps ) ) {
				wp_die( 'Not allowed here.' );
			}

			$this->_allow_caps = array_unique( $allow_caps );
		}
		return $this->_allow_caps;
	}

	public function column_default( $item, $column_name ) {
		$return = '';

		switch ( $column_name ) {
			case 'action':
				$return = $this->get_action_label( $item->action );
				break;
			case 'date':
				/* translators: draft saved date format, see http://php.net/date */
				$return  = sprintf( '<strong>' . __( '%s ago', 'adminify-activity-logs' ) . '</strong>', human_time_diff( $item->log_time, current_time( 'timestamp' ) ) );
				$return .= '<br />' . date( 'd/m/Y', $item->log_time );
				$return .= '<br />' . date( 'H:i:s', $item->log_time );
				break;
			case 'ip':
				// Handle IPv6 localhost display
				if ( $item->log_ip === '::1' ) {
					$return = '127.0.0.1';
				} elseif ( strpos( $item->log_ip, '::ffff:' ) === 0 ) {
					// Handle IPv4-mapped IPv6 addresses
					$return = substr( $item->log_ip, 7 );
				} else {
					$return = $item->log_ip;
				}
				break;
			default:
				if ( isset( $item->$column_name ) ) {
					$return = $item->$column_name;
				}
		}

		$return = apply_filters( 'adminify_activity_logs_table_list_column_default', $return, $item, $column_name );

		return $return;
	}

	public function column_author( $item ) {
		global $wp_roles;

		if ( ! empty( $item->user_id ) && 0 !== (int) $item->user_id ) {
			$user = get_user_by( 'id', $item->user_id );
			if ( $user instanceof \WP_User && 0 !== $user->ID ) {
				return sprintf(
					'<a href="%s">%s <span class="wp-adminify-author-name">%s</span></a><br /><small>%s</small>',
					get_edit_user_link( $user->ID ),
					get_avatar( $user->ID, 40 ),
					$user->display_name,
					isset( $user->roles[0] ) && isset( $wp_roles->role_names[ $user->roles[0] ] ) ? $wp_roles->role_names[ $user->roles[0] ] : esc_html__( 'Unknown', 'adminify-activity-logs' )
				);
			}
		}

		return sprintf(
			'<span class="wp-adminify-author-name">%s</span>',
			esc_html__( 'N/A', 'adminify-activity-logs' )
		);
	}

	public function column_type( $item ) {
		$return = apply_filters( 'adminify_activity_logs_table_list_column_type', $item->object_type, $item );
		return $return;
	}

	public function column_label( $item ) {
		$return = '';
		if ( ! empty( $item->object_subtype ) ) {
			$pt     = get_post_type_object( $item->object_subtype );
			$return = ! empty( $pt->label ) ? $pt->label : $item->object_subtype;
		}

		$return = apply_filters( 'adminify_activity_logs_table_list_column_label', $return, $item );
		return $return;
	}


	public function column_description( $item ) {
		$return = esc_html( $item->object_name );

		switch ( $item->object_type ) {
			case 'Post':
				$return = sprintf( wp_kses_post( '<a href="%s">%s</a>' ), get_edit_post_link( $item->object_id ), esc_html( $item->object_name ) );
				break;

			case 'Taxonomy':
				if ( ! empty( $item->object_id ) ) {
					$return = sprintf( wp_kses_post( '<a href="%s">%s</a>' ), get_edit_term_link( $item->object_id, $item->object_subtype ), esc_html( $item->object_name ) );
				}
				break;

			case 'Comments':
				if ( ! empty( $item->object_id ) && $comment = get_comment( $item->object_id ) ) {
					$return = sprintf( wp_kses_post( '<a href="%s">%s #%d</a>' ), get_edit_comment_link( $item->object_id ), $item->object_name, $item->object_id );
				}
				break;

			case 'Export':
				if ( 'all' === $item->object_name ) {
					$return = esc_html__( 'All', 'adminify-activity-logs' );
				} else {
					$pt     = get_post_type_object( $item->object_name );
					$return = ! empty( $pt->label ) ? $pt->label : $item->object_name;
				}
				break;

			case 'Options':
			case 'Core':
				$return = !empty( $item->object_name ) ? $item->object_name : '';
				break;
		}

		$return = apply_filters( 'adminify_activity_logs_table_list_column_description', $return, $item );

		return $return;
	}

	public function column_delete( $item ) {
		$url = admin_url( wp_nonce_url( "admin.php?page=adminify-activity-logs&action=delete&log_id=$item->log_id", 'delete-log_' . $item->log_id ) );
		return sprintf( wp_kses_post( '<a href="%s" class="button button-small">%s</button>' ), $url, esc_html__( 'Delete', 'adminify-activity-logs' ) );
	}

	public function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			$this->search_box( esc_html__( 'Search', 'adminify-activity-logs' ), 'wp-adminify-search' );
			// Add nonce field to the form
			wp_nonce_field( 'adminify_activity_logs_action', 'adminify_activity_logs_nonce' );
			echo sprintf(
				\JLTAdminify\Modules\ActivityLogs\Libs\Helper::wp_kses_custom( '<h3>' . esc_html__( 'Activity Logs Data', 'adminify-activity-logs' ) . ' <strong><em><small>' . esc_html__( 'default: 30 days', 'adminify-activity-logs' ) . '</small></em></strong></h3> <p>' . esc_html__( 'Data Store for:', 'adminify-activity-logs' ) . ' <input type="number" id="adminify-activity-logs-days" value="%1$s" style="width:60px"/> <button class="button button-primary" id="adminify-activity-logs-days-save">' . esc_html__( 'Save Settings', 'adminify-activity-logs' ) . '</button></p>' ),
				(int) get_option( '_adminify_activity_logs_days', 30 )
			);
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
			<br class="clear" />
		</div>
		<?php
	}

	public function extra_tablenav($which) {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'adminify-activity-logs'));
    }

    global $wpdb;

    if ('top' !== $which) {
        return;
    }

    // Sanitize and validate request parameters early
    $date_show = isset($_REQUEST['dateshow']) ? sanitize_text_field(wp_unslash($_REQUEST['dateshow'])) : '';
    $cap_show = isset($_REQUEST['capshow']) ? sanitize_text_field(wp_unslash($_REQUEST['capshow'])) : '';
    $user_show = isset($_REQUEST['usershow']) ? sanitize_text_field(wp_unslash($_REQUEST['usershow'])) : '';
    $type_show = isset($_REQUEST['typeshow']) ? sanitize_text_field(wp_unslash($_REQUEST['typeshow'])) : '';
    $show_action = isset($_REQUEST['showaction']) ? sanitize_text_field(wp_unslash($_REQUEST['showaction'])) : '';

    $role_where = $this->_get_where_by_role();

    // Cache the dropdown facets — they only change when new logs are inserted.
    $facets_key = 'adminify_activity_logs_facets_' . md5( $role_where );
    $facets     = wp_cache_get( $facets_key, 'adminify_activity_logs' );

    if ( false === $facets ) {
        $rows = $wpdb->get_results(
            'SELECT `user_id`, `object_type`, `action` FROM `' . $wpdb->adminify_activity_logs . '`
                WHERE 1 = 1 ' . $role_where . '
                GROUP BY `user_id`, `object_type`, `action`'
        );

        $u = $t = $a = [];
        foreach ( (array) $rows as $r ) {
            $u[ (int) $r->user_id ]    = (int) $r->user_id;
            $t[ $r->object_type ]      = $r->object_type;
            $a[ $r->action ]           = $r->action;
        }
        ksort( $u );
        ksort( $t );
        ksort( $a );

        $facets = [
            'users'   => array_map( function ( $id ) { $o = new \stdClass(); $o->user_id = $id; return $o; }, array_values( $u ) ),
            'types'   => array_map( function ( $v )  { $o = new \stdClass(); $o->object_type = $v; return $o; }, array_values( $t ) ),
            'actions' => array_map( function ( $v )  { $o = new \stdClass(); $o->action = $v; return $o; }, array_values( $a ) ),
        ];

        wp_cache_set( $facets_key, $facets, 'adminify_activity_logs', 5 * MINUTE_IN_SECONDS );
    }

    $users = $facets['users'];
    $types = $facets['types'];

    // Make sure we get items for filter.
    if ( $users || $types ) {
        if ( ! isset( $_REQUEST['dateshow'] ) ) {
            $_REQUEST['dateshow'] = '';
        }

        $date_options = [
            ''          => esc_html__( 'All Time', 'adminify-activity-logs' ),
            'today'     => esc_html__( 'Today', 'adminify-activity-logs' ),
            'yesterday' => esc_html__( 'Yesterday', 'adminify-activity-logs' ),
            'week'      => esc_html__( 'Week', 'adminify-activity-logs' ),
            'month'     => esc_html__( 'Month', 'adminify-activity-logs' ),
        ];

        echo '<select name="dateshow" id="hs-filter-date">';
        foreach ( $date_options as $key => $value ) {
            echo '<option value="' . esc_attr( $key ) . '" ' . selected( sanitize_text_field( wp_unslash( $_REQUEST['dateshow'] ) ), $key, false ) . ' >' . esc_html( $value ) . '</option>';
        }
        echo '</select>';

        
    }

    if ( $users ) {
        if ( ! isset( $_REQUEST['capshow'] ) ) {
            $_REQUEST['capshow'] = '';
        }

        $output = [];
        foreach ( $this->_get_allow_caps() as $cap ) {
            $output[ $cap ] = ucwords( $cap );
        }

        if ( ! empty( $output ) ) {
            echo '<select name="capshow" id="hs-filter-capshow">';
            printf( '<option value="">%s</option>', esc_html__( 'All Roles', 'adminify-activity-logs' ) );
            foreach ( $output as $key => $value ) {
                echo '<option value="' . esc_attr( $key ) . '" ' . selected( sanitize_text_field( wp_unslash( $_REQUEST['capshow'] ) ), $key, false ) . ' >' . esc_html( $value ) . '</option>';
            }
            echo '</select>';
        }

        if ( ! isset( $_REQUEST['usershow'] ) ) {
            $_REQUEST['usershow'] = '';
        }

        $output = [];
        foreach ( $users as $_user ) {
            if ( 0 === (int) $_user->user_id ) {
                $output[0] = esc_html__( 'N/A', 'adminify-activity-logs' );
                continue;
            }

            $user = get_user_by( 'id', $_user->user_id );
            if ( $user ) {
                $output[ $user->ID ] = $user->user_nicename;
            }
        }

        if ( ! empty( $output ) ) {
            echo '<select name="usershow" id="hs-filter-usershow">';
            printf( '<option value="">%s</option>', esc_html__( 'All Users', 'adminify-activity-logs' ) );
            foreach ( $output as $key => $value ) {
                echo '<option value="' . esc_attr( $key ) . '" ' . selected( sanitize_text_field( wp_unslash( $_REQUEST['usershow'] ) ), $key, false ) . ' >' . esc_html( $value ) . '</option>';
            }
            echo '</select>';
        }
    }

    if ( $types ) {
        if ( ! isset( $_REQUEST['typeshow'] ) ) {
            $_REQUEST['typeshow'] = '';
        }

        $output = [];
        foreach ( $types as $type ) {
            $output[] = '<option value="' . esc_attr( $type->object_type ) . '" ' . selected( sanitize_text_field( wp_unslash( $_REQUEST['typeshow'] ) ), $type->object_type, false ) . ' >' . esc_html( $type->object_type ) . '</option>';
        }

        echo '<select name="typeshow" id="hs-filter-typeshow">';
        printf( '<option value="">%s</option>', esc_html__( 'All Types', 'adminify-activity-logs' ) );
        echo \JLTAdminify\Modules\ActivityLogs\Libs\Helper::wp_kses_custom( implode( '', $output ) );
        echo '</select>';
    }

    $actions = $facets['actions'];

    if ( $actions ) {
        if ( ! isset( $_REQUEST['showaction'] ) ) {
            $_REQUEST['showaction'] = '';
        }

        $output = [];
        foreach ( $actions as $type ) {
            $output[] = '<option value="' . esc_attr( $type->action ) . '" ' . selected( sanitize_text_field( wp_unslash( $_REQUEST['showaction'] ) ), $type->action, false ) . ' >' . esc_html( $type->action ) . '</option>';
        }

        echo '<select name="showaction" id="hs-filter-showaction">';
        printf( '<option value="">%s</option>', esc_html__( 'All Actions', 'adminify-activity-logs' ) );
        echo \JLTAdminify\Modules\ActivityLogs\Libs\Helper::wp_kses_custom( implode( '', $output ) );
        echo '</select>';
    }
		submit_button( esc_html__( 'Filter', 'adminify-activity-logs' ), 'button', 'adminify-activity-logs-filter', false, [ 'id' => 'activity-query-submit' ] );
}


	public function prepare_items() {
		global $wpdb;

		// Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'adminify-activity-logs'));
		}

		$items_per_page        = $this->get_items_per_page( 'edit_adminify_activity_logs_per_page', 20 );
		$this->_column_headers = [ $this->get_columns(), get_hidden_columns( $this->screen ), $this->get_sortable_columns() ];
		$where                 = ' WHERE 1 = 1';

		if ( ! isset( $_REQUEST['order'] ) || ! in_array( $_REQUEST['order'], [ 'desc', 'asc' ] ) ) {
			$_REQUEST['order'] = 'DESC';
		}
		if ( ! isset( $_REQUEST['orderby'] ) || ! in_array( $_REQUEST['orderby'], [ 'log_time', 'log_ip' ] ) ) {
			$_REQUEST['orderby'] = 'log_time';
		}

		if ( ! empty( $_REQUEST['typeshow'] ) ) {
			$where .= $wpdb->prepare( ' AND `object_type` = %s', sanitize_text_field( wp_unslash( $_REQUEST['typeshow'] ) ) );
		}

		if ( isset( $_REQUEST['showaction'] ) && '' !== $_REQUEST['showaction'] ) {
			$where .= $wpdb->prepare( ' AND `action` = %s', sanitize_text_field( wp_unslash( $_REQUEST['showaction'] ) ) );
		}

		if ( isset( $_REQUEST['usershow'] ) && '' !== $_REQUEST['usershow'] ) {
			$where .= $wpdb->prepare( ' AND `user_id` = %d', sanitize_text_field( wp_unslash( $_REQUEST['usershow'] ) ) );
		}

		if ( isset( $_REQUEST['capshow'] ) && '' !== $_REQUEST['capshow'] ) {
			$where .= $wpdb->prepare( ' AND `user_caps` = %s', strtolower( sanitize_text_field( wp_unslash( $_REQUEST['capshow'] ) ) ) );
		}

		if ( isset( $_REQUEST['dateshow'] ) && in_array( $_REQUEST['dateshow'], [ 'today', 'yesterday', 'week', 'month' ] ) ) {
			$current_time = current_time( 'timestamp' );

			// Today
			$start_time = mktime( 0, 0, 0, date( 'm', $current_time ), date( 'd', $current_time ), date( 'Y', $current_time ) );

			$end_time = mktime( 23, 59, 59, date( 'm', $current_time ), date( 'd', $current_time ), date( 'Y', $current_time ) );

			if ( 'yesterday' === $_REQUEST['dateshow'] ) {
				$start_time = strtotime( 'yesterday', $start_time );
				$end_time   = mktime( 23, 59, 59, date( 'm', $start_time ), date( 'd', $start_time ), date( 'Y', $start_time ) );
			} elseif ( 'week' === $_REQUEST['dateshow'] ) {
				$start_time = strtotime( '-1 week', $start_time );
			} elseif ( 'month' === $_REQUEST['dateshow'] ) {
				$start_time = strtotime( '-1 month', $start_time );
			}

			$where .= $wpdb->prepare( ' AND `log_time` > %d AND `log_time` < %d', $start_time, $end_time );
		}

		if ( isset( $_REQUEST['s'] ) ) {
			// Search only searches 'description' fields.
			$where .= $wpdb->prepare( ' AND `object_name` LIKE %s', '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%' );
		}

		$offset = ( $this->get_pagenum() - 1 ) * $items_per_page;

		$total_items = $wpdb->get_var(
			'SELECT COUNT(`log_id`) FROM  `' . $wpdb->adminify_activity_logs . '`
				' . $where . '
					' . $this->_get_where_by_role()
		);

		$items_orderby = sanitize_key( $_REQUEST['orderby'] );
		if ( empty( $items_orderby ) ) {
			$items_orderby = 'log_time'; // Sort by time by default.
		}

		$items_order = strtoupper( sanitize_key( $_REQUEST['order'] ) );
		if ( empty( $items_order ) || ! in_array( $items_order, [ 'DESC', 'ASC' ] ) ) {
			$items_order = 'DESC'; // Descending order by default.
		}

		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM `' . $wpdb->adminify_activity_logs . '`
				' . $where . '
					' . $this->_get_where_by_role() . '
					ORDER BY ' . $items_orderby . ' ' . $items_order . '
					LIMIT %d, %d;',
				$offset,
				$items_per_page
			)
		);

		// Prime user cache for visible rows in one query — avoids N+1 in column_author().
		if ( ! empty( $this->items ) && function_exists( 'cache_users' ) ) {
			$user_ids = [];
			foreach ( $this->items as $item ) {
				$uid = (int) $item->user_id;
				if ( $uid > 0 ) {
					$user_ids[ $uid ] = true;
				}
			}
			if ( ! empty( $user_ids ) ) {
				cache_users( array_keys( $user_ids ) );
			}
		}

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $items_per_page,
				'total_pages' => ceil( $total_items / $items_per_page ),
			]
		);
	}

	public function adminify_activity_logs_set_screen_option( $status, $option, $value ) {
		if ( 'edit_adminify_activity_logs_per_page' === $option ) {
			return $value;
		}
		return $status;
	}

	public function search_box( $text, $input_id ) {
		// Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'adminify-activity-logs'));
		}

		// Sanitize search input - remove double sanitization
		$search_data = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';

		$input_id = $input_id . '-search-input';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php echo esc_attr( $search_data ); ?>" />
			<?php submit_button( $text, 'button', false, false, [ 'id' => 'search-submit' ] ); ?>
		</p>
		<?php
	}
}
