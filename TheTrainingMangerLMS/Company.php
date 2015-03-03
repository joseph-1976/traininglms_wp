<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS class Company
 *
 * This class encapsulates fields and functionality
 * of logic for real-world companies. It manages the
 * internal structure of the company using groups
 * and sets appropriate roles within the groups.
 *
 * IMPORTANT Users are associated with a specific group inside a company. This means:
 * - The Company is a group with each department being a child group
 * - Each group (including the child group & the company) can have both users and child groups
 * - The recursive functions allow you to return users within the entire company or a division of the company.
 * - get_users_below_user(): returns all users in the groups below that user and in all child groups below the user's groups
 * - get_groups_in_company(): returns a list of all groups (divisions) in the company
 * - get_groups_in_group_recursive() returns a list of all child groups of a group
 * - get_users_in_company(): returns a list of all users in the company (from the company group AND from all children).
 * - get_users_in_group_recursive(): returns a list of all users in the supplied group & all child groups
 * - get_managers_in_company(): This function returns a list of all managers in the company.
 * - get_nonmanagers_in_company(): This function returns a list of all non-managers in the company.
 * - NOTE: The $one_dimensional argument allows the results to be returned in a single dimension of unique values. This is useful when looping through all employees or performing actions where company structure is irrelevant.
 * - The non-recursive functions allow you to return information about a specific group or about the outer company group:
 * - get_managers_in_group(): This function returns all managers in a specified group. If no group is supplied, then it recursively gets all managers in the company.
 * - get_nonmanagers_in_group(): This function returns all non-managers in a specified group. If no group is supplied, then it recursively gets all non-managers in the company.
 * - get_users_with_capability(): This function returns users with a specific capability. A group can be specified as an argument otherwise no group argument returns all users in the company with that capability.
 * - get_group_user_ids(): This function returns a list of users for a specific group (not recursively). If no group is specified then the users found in the company group (without recursively looking in child groups) is returned.
 *
 * Please see the function signatures for more information & argument information.
 * To avoid getting mixed results, please only add a user to groups they belong in and ONLY add them to the outer company group if their position requires it.
 * A user does NOT need to be added to the outer company group to be added to a child group.
 * The recursive functions above are meant to provide an overview of the company as a whole including the users, groups, and structure.
 * The non-recursive (or default to recursive functions) above are meant to provide specific information about a group or the outer company group.
 **/

/*
	The class
*/
class Company {

	const SUPER_ADMIN = "company_super_admin";
	const MANAGER = "company_manager";

	// Maximum number of users in group
	const MAX_CHILD_GROUP_SIZE = 500;
	// Maximum number of child groups per group
	const MAX_NUM_CHILD_GROUPS = 10;

	public $group_id;
	public $group_prefix;
	public $company_name;
	public $new_data;
	protected $id;

	/**
	 * Constructor should set the core
	 * group components for easy access in methods
	 * and store new data for later use/access. If you are sure
	 * that a company does not need to be created, set $create_blank_instance
	 * equal to false so avoid the extra database calls.
	 * $create_blank_instance can also be set to false if you need an instance
	 * of the Company class without setting any data in the database.
	 * NOTE: The Groups plugin must be installed & activated for the plugin to
	 * work properly & install successfully.
	 * @param $group_name
	 * @param 
	 */
	function __construct( $group_name, $num_employees=false, $super_admin_id = false, $create_blank_instance = true ) {
		$this->company_name = $group_name;
		$this->group_id = $this->get_group_id( $group_name );

		// We need a group prefix for child groups to prevent conflicting names with other companies
		$this->group_prefix = get_the_ID().'_';
		$this->id = get_the_ID();
		
		


		if( $create_blank_instance && ! $this->group_id ) {

			$create_group_array = array( 'name' => $group_name );
			$this->group_id = $this->create_group( $create_group_array );
		}

		if( $create_blank_instance && ( ! $this->get_super_admin() || $super_admin_id ) ) {
			if( ! $super_admin_id ) {
				$super_admin_id = get_current_user_id();
			}

			$this->set_super_admin( $super_admin_id );
			$this->add_user( $super_admin_id );
		}

		if( $num_employees ) {
			$this->set_num_employees($num_employees);
		}

		$this->update_post_title();		
	}
	
	protected static function getPostType() {
		return ttp_lms_post_prefix(Constants::CompanyPostType);
	}
	
	/** 
	 * Temporary function to allow for creation of Company post type and object from outside of the testing interface
	 * At the minimum, need to reduce shared code between this and __construct
	 * Ideally, integrate with WP_DB_Object framework and this function would be taken care of.
	 *
	 */ 
	public static function create(  $group_name, $num_employees=false, $super_admin_id = false, $create_blank_instance = true ) {
		$post = array( 'post_type' => static::getPostType());
		
		$post_id = wp_insert_post($post, true);
		
		$company = new \TheTrainingMangerLMS\Company;
		
		$company->id = $post_id;
		if (is_wp_error($post_id))
			throw new \RuntimeException(serialize($post_id));
		
		$company->company_name = $group_name;
		$company->group_id = $company->get_group_id( $group_name );

		// We need a group prefix for child groups to prevent conflicting names with other companies
		$company->group_prefix = $company->id.'_';
		
		if( $create_blank_instance && ! $company->group_id ) {

			$create_group_array = array( 'name' => $group_name );
			$company->group_id = $company->create_group( $create_group_array );
		}

		if( $create_blank_instance && ( ! $company->get_super_admin() || $super_admin_id ) ) {
			if( ! $super_admin_id ) {
				$super_admin_id = get_current_user_id();
			}

			$company->set_super_admin( $super_admin_id );
			$company->add_user( $super_admin_id );
		}

		if( $num_employees ) {
			$company->set_num_employees($num_employees);
		}

		$company->update_post_title();	
		return $company;
		
	}
	
	public function ID() {
		return $this->id;
	}

	/**
	 * Creates a group based on an array that includes the following keys
	 * - 'name' (required)
	 * - 'description' (optional)
	 * - 'parent_id' (optional)
	 * @param $group_detail_array
	 * @return \group_id
	 */
	public function create_group( $group_detail_array ) {
		return \Groups_Group::create( $group_detail_array );
	}

	/**
	 * Get the Id of a group given it's name.
	 * @param $group_name
	 * @return \group_id
	 */
	public function get_group_id( $group_name ) {
		if( $group_name != $this->company_name ) {
			if( 0 !== strpos( $group_name, $this->group_prefix ) ) {
				$group_name = $this->group_prefix.$group_name;
			}
		}
		return \Groups_Group::read_by_name( $group_name )->group_id;
	}

	/**
	 * Provide a simple way to fetch the group name when an id is given
	 * @param $id
	 * @return mixed
	 */
	public function get_group_name_from_id( $id ) {
		$result = \Groups_Group::read( $id )->name;
		if( $result ) {
			$result = str_replace($this->group_prefix, '', $result);
		}

		return $result;
	}

	/**
	 * Add a user to a specific group. If no group name is provided
	 * then add it to the primary (main company) group
	 * @param $user_id
	 * @param bool $group_name
	 * @return bool|\true
	 */
	public function add_user( $user_id, $group_name= false ) {
		if( ! $group_name || $group_name == $this->company_name ) {
			$group_id = $this->group_id;
		} else {
			$group_id = $this->get_group_id( $group_name );
			if( ! $group_id ) {
				return false;
			}
		}

		$user_count = count( $this->get_group_user_ids( $group_name ) );
		if( $user_count < Company::MAX_CHILD_GROUP_SIZE ) {
			return \Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
		} else {
			return false;
		}
	}

	/**
	 * Remove a user from a specifc group. If no group is specified,
	 * then remove them from the primary (company) group.
	 * @param $user_id
	 * @param bool $group_name
	 * @return bool|\true
	 */
	public function delete_user( $user_id, $group_name= false ) {
		if( ! $group_name || $group_name == $this->company_name ) {
			$group_id = $this->group_id;
		} else {
			$group_id = $this->get_group_id( $group_name );
			if( ! $group_id ) {
				return false;
			}
		}

		$capability = $this->group_prefix.$group_name.'_'.Company::MANAGER;
		$capability_id = \Groups_Capability::read_by_capability( $capability )->capability_id;
		\Groups_User_Capability::delete( $user_id, $capability_id );
		return \Groups_User_Group::delete( $user_id,  $group_id );
	}

	/**
	 * Assign a capability to a user after ensuring that it exists
	 * @param $user_id
	 * @param $capability
	 * @return bool|\true
	 */
	private function set_user_capability( $user_id, $capability ) {
		$capability_id = \Groups_Capability::read_by_capability( $capability )->capability_id;
		if( ! $capability_id ) {
			return false;
		}

		return \Groups_User_Capability::create( array( 'user_id' => $user_id, 'capability_id' => $capability_id ) );
	}

	/**
	 * Create a manager for a group within a company. If the user does
	 * not belong to that group, they will be added automatically. If no
	 * group is specified then the manager is assigned to the company
	 * @param $user_id
	 * @param bool $group_name
	 * @return bool|\true
	 */
	public function create_manager_in_group( $user_id, $group_name = false ) {
		if( ! $group_name || $group_name == $this->company_name ) {
			return false;
		} else {
			$capability = $this->group_prefix.$group_name.'_'.Company::MANAGER;
		}

		$capability_result = \Groups_Capability::read_by_capability( $capability );

		if( ! $capability_result ) {
			$capability_info = array(
				'capability' => $capability,
				'description' => $this->company_name.' manager in the '.$group_name.' group'
			);

			\Groups_Capability::create( $capability_info );
		}

		$this->add_user( $user_id, $group_name );
		return $this->set_user_capability( $user_id, $capability );

	}


	/**
	 * Return all users in a group that have a specific
	 * capability associated with them. If no group is
	 * specified then all users in the company with the capability are returned.
	 * @param $capability
	 * @return bool|array
	 */
	public function get_users_with_capability( $capability, $group_name = false ) {
		global $wpdb;

		if( ! $group_name ) {
			$group_array = $this->array_flatten( $this->get_groups_in_company(), true );
		} else {;
			$group_id = $this->get_group_id( $group_name );
			if( ! $group_id ) {
				return false;
			}

			$group_array = array( $group_id );
		}

		$capability_id = \Groups_Capability::read_by_capability( $capability )->capability_id;
		if( ! $capability_id ) {
			return false;
		}

		foreach( $group_array as $key => $value ) {
			if( $value != $this->company_name ) {
				$group_array[$key] = $this->group_prefix.$value;
			}
		}

		if( count( $group_array ) > 0 ) {
			$group_string = implode("','", esc_sql( $group_array ) );
		} else {
			$group_string = esc_sql( $this->company_name );
		}

		$group_string = "'".$group_string."'";

		$result_array = $wpdb->get_col( $wpdb->prepare( "SELECT wp_groups_user_capability.user_id
		FROM wp_groups_user_capability
		JOIN wp_groups_user_group ON ( wp_groups_user_capability.user_id = wp_groups_user_group.user_id
			AND wp_groups_user_group.group_id )
		JOIN wp_groups_group ON ( wp_groups_user_group.group_id = wp_groups_group.group_id AND wp_groups_group.name IN ({$group_string}) )
		WHERE capability_id = %d GROUP BY wp_groups_user_capability.user_id", $capability_id ) );

		return $result_array;
	}

	/**
	 * Removes the management capability (not the user) from
	 * a group. If no group is specified then the company is used
	 * as the group.
	 * @param $user_id
	 * @param $group_name
	 */
	public function delete_manager_from_group( $user_id, $group_name = false ) {
		if( ! group_name || $group_name == $this->company_name ) {
			$capability = $this->group_prefix.Company::MANAGER;
		} else {
			$capability = $this->group_prefix.$group_name.'_'.Company::MANAGER;
		}

		$this->delete_user_capability( $user_id, $capability );
	}

	/**
	 * Remove a user's capability
	 * @param $user_id
	 * @param $capability
	 * @return bool|\true
	 */
	public function delete_user_capability( $user_id, $capability ) {
		$capability_id = \Groups_Capability::read_by_capability( $capability )->capability_id;
		if( ! $capability_id ) {
			return false;
		}

		return \Groups_User_Capability::delete( $user_id, $capability_id );
	}

	/**
	 * Fetch all user Id's associated with a group (company)
	 * @param $group_name
	 * @return bool|array
	 */
	public function get_group_user_ids( $group_name = false ) {
		global $wpdb;

		if( ! $group_name || $group_name == $this->company_name ) {
			$group_id = $this->group_id;
		} else {
			$group_id = $this->get_group_id( $group_name );
			if( ! $group_id ) {
				return false;
			}
		}
		$query = $wpdb->prepare( "SELECT user_id FROM wp_groups_user_group
			WHERE group_id = %d", $group_id );
		$result_array = $wpdb->get_col( $query );
		return $result_array;
	}

	/**
	 * Return all child groups (organization units) of a group (company)
	 * @param $group_name
	 * @return bool|array
	 */
	public function get_child_group_ids( $group_name = false ) {
		global $wpdb;

		if( ! $group_name || $group_name == $this->company_name ) {
			$group_id = $this->group_id;
		} else {
			$group_id = $this->get_group_id( $group_name );
			if( ! $group_id ) {
				return false;
			}
		}

		$result_array = $wpdb->get_col( $wpdb->prepare( "SELECT group_id FROM wp_groups_group
			WHERE parent_id = %d", $group_id ) );

		return $result_array;

	}

	/**
	 * Returns the super admin of the current company
	 * @return mixed
	 */
	public function get_super_admin() {
		$result_array = $this->get_users_with_capability( $this->group_prefix.Company::SUPER_ADMIN );
		$result = false;

		if( count( $result_array ) == 1 ) {
			$result = get_user_by( 'id', $result_array[0]);
		}
		return $result;
	}

	/**
	 * Set the role of super-admin for a user given their Id
	 * @param $user_id
	 * @return bool|\true
	 */
	public function set_super_admin( $user_id ){
		$capability = $this->group_prefix.Company::SUPER_ADMIN;

		$capability_result = \Groups_Capability::read_by_capability( $capability );

		if( ! $capability_result ) {
			$capability_info = array(
				'capability' => $capability,
				'description' => $this->company_name.' super admin'
			);

		\Groups_Capability::create( $capability_info );
		}

		$this->delete_user_capability( $this->get_super_admin()->id, $capability );
		$return_value = $this->set_user_capability( $user_id, $capability );
		$this->add_user( $user_id );
		return $return_value;
	}

	/**
	 * Create a child group under a specific parent. If no parent is specified,
	 * then use the current company group
	 * @param $group_name
	 * @param bool $parent_name
	 * @return bool|\group_id
	 */
	public function create_child_group( $group_name, $description = false,  $parent_name = false ) {
		if( ! $parent_name || $parent_name == $this->company_name ) {
			$parent_id = $this->group_id;
		} else {
			$parent_id = $this->get_group_id( $parent_name );

			if( ! $parent_id ) {
				return false;
			}
		}

		$group_name = $this->group_prefix.$group_name;

		$parent_children = $this->get_child_group_ids( $parent_name );
		if( count( $parent_children ) >= Company::MAX_NUM_CHILD_GROUPS ) {
			return false;
		}

		$group_info = array(
			'name' => $group_name,
			'parent_id' => $parent_id
		);

		if( $description ) {
			$group_info['description'] = $description;
		}

		return \Groups_Group::create( $group_info );
	}

	/**
	 * Remove the specified child group. If $preserve_users
	 * is set to true then the users of the group and lower groups
	 * will be moved to the parent group
	 * @param $group_name
	 * @param bool $preserve_users
	 * @return bool|\group_id
	 */
	public function delete_child_group( $group_name, $preserve_users = true ) {
		$group_id = $this->get_group_id( $group_name );

		if( ! $group_id ) {
			return false;
		}


		if( $preserve_users ) {
			$parent_company_id = \Groups_Group::read( $group_id )->parent_id;
			$parent_name = \Groups_Group::read( $parent_company_id )->name;
			$user_array = $this->get_users_in_group_recursive( $group_name, true );
			$delete_result= \Groups_Group::delete( $group_id );
			$company_user_array = $this->get_users_in_company( true );
			foreach( $user_array as $user_id ) {
				$user_array_index = array_search( $user_id, $company_user_array );
				if( false ===  $user_array_index ) {
					$this->add_user( $user_id, $parent_name );
				}
			}
		}

		return $delete_result;
	}

	/**
	 * Offers a way to easily interface with Group classes to change
	 * group attributes such as parent, name, etc. The Groups plugin
	 * bases all updates on the group_id thus the array passed into
	 * this function needs to have a key called "group_id" that identifies
	 * the group that needs updating. The Company function get_group_id()
	 * can return the group_id value from a group name to comply with normal
	 * class usage.\
	 * @param array $values
	 * @return bool|\group_id
	 */
	public function change_group_attribute( $values = array() ) {

		if( array_key_exists( 'group_name', $values ) && $values['group_name'] != $this->company_name ) {
			$values['group_name'] = $this->group_prefix.$values['group_name'];
		} else {
			return false;
		}

		return \Groups_Group::update( $values );
	}

	/**
	 * Gets the group that a user is in
	 * @param $user_id
	 * @return bool|mixed
	 */
	public function get_all_groups_from_user_id( $user_id ) {
		global $wpdb;

		if( ! $user_id ) {
			return false;
		}

		$group_array = $this->array_flatten( $this->get_groups_in_company(), true );
		$group_array = array_unique( $group_array );

		foreach( $group_array as $key => $value ) {
			if( $value != $this->company_name ) {
				$group_array[$key] = $this->group_prefix.$value;
			}
		}


		$group_string = "'".implode("','", esc_sql( $group_array ) )."'";
		$result_array = $wpdb->get_col( $wpdb->prepare( "SELECT wp_groups_user_group.group_id FROM wp_groups_user_group
		JOIN wp_groups_group ON ( wp_groups_user_group.group_id = wp_groups_group.group_id AND name IN ({$group_string})  )
		WHERE user_id = %d", $user_id ) );

		$return_value = false;
		if( count( $result_array ) > 0 ) {
			$return_value = array();
			foreach( $result_array as $result ) {
				$result = \Groups_Group::read( $result )->name;
				$return_value[] = str_replace($this->group_prefix, '', $result);
			}

		}

		return $return_value;

	}

	/**
	 * Gets a recursive list (array) of all users below a user
	 * @param $user_id
	 * @param $one_dimensional
	 * @return array|bool
	 */
	public function get_users_below_user( $user_id, $one_dimensional = false ) {
		$group_name_array = $this->get_all_groups_from_user_id( $user_id );


		if( ! $group_name_array ) {
			return false;
		}

		$results_array = array();

		foreach( $group_name_array as $group_name ) {
			$user_array = $this->get_users_in_group_recursive( $group_name );

			/*
		 * Let's find/remove managers/users from the same level group unless
		 * the person is a super admin. Additionally, remove the user from
		 * our results.
		 */

			$super_admin = $this->get_super_admin();
			$search_index = array_search( $super_admin, $results_array );
			if( false !== $search_index ) {
				unset( $user_array[$search_index] );
			}

			$manager_array = array();
			if( $user_id != $super_admin ) {
				$manager_array = $this->get_managers_in_group( $group_name );
			}

			$user_array = array_diff( $user_array, $manager_array );
			$key = array_search( $user_id, $user_array );
			if( $key !== false ) {
				unset( $user_array[$key] );
			}

			if( $one_dimensional ) {
				$user_array = $this->array_flatten( $user_array );
				$user_array = array_unique( $user_array );
			}

			$results_array = array_merge( $results_array, $user_array );
		}

		$key = array_search( $this->get_super_admin()->id, $user_array );
		if( $key !== false ) {
			unset( $user_array[$key] );
		}

		return $results_array;
	}

	/**
	 * Returns a list of all groups in the company
	 * @param bool $one_dimensional
	 * @return array|bool
	 */
	public function get_groups_in_company( $one_dimensional = false ) {
		return $this->get_groups_in_group_recursive( false, $one_dimensional );
	}

	/**
	 * Returns a list of all groups in a group and below
	 * @param bool $group_name
	 * @param bool $one_dimensional
	 * @return array|bool
	 */
	public function get_groups_in_group_recursive( $group_name = false, $one_dimensional = false ) {
		$return_array = $this->base_recursive( $group_name, 'Group' );
		if( $one_dimensional ) {
			$return_array = $this->array_flatten( $return_array, true );
			$return_array = array_unique( $return_array );
		}

		return $return_array;
	}

	/**
	 * Returns a list of all users in a company
	 * @param bool $one_dimensional
	 * @return array|bool
	 */
	public function get_users_in_company( $one_dimensional = false ) {
		return $this->get_users_in_group_recursive( false, $one_dimensional );
	}

	/**
	 * Returns an array of all users within a group and below
	 * @param bool $group_name
	 * @param $one_dimensional
	 * @return array|bool
	 */
	public function get_users_in_group_recursive( $group_name = false, $one_dimensional = false ) {
		$return_array = $this->base_recursive( $group_name, 'User' );
		if( $one_dimensional ) {
			$return_array = $this->array_flatten( $return_array );
			$return_array = array_unique( $return_array );
		}

		return $return_array;
	}

	/**
	 * Returns all non-managers in a group. If no group is given
	 * then all nonmanagers in the company are returned
	 * @param bool $group_name
	 * @return array|bool
	 */
	public function get_nonmanagers_in_group( $group_name = false ) {
		if( ! $group_name ) {
			$group_users = $this->get_users_in_company();
			$group_users = $this->array_flatten( $group_users );
			$group_users = array_unique( $group_users );
		} else {
			$group_users = $this->get_group_user_ids( $group_name );
		}

		$return_value = false;
		$manager_group = $this->get_managers_in_group( $group_name );
		$return_array = array_diff( $group_users, $manager_group );

		if( count( $return_array ) > 0 ) {
			$return_value = $return_array;
		}

		return $return_value;
	}

	/**
	 * Returns all managers in a group. If no group is supplied, then
	 * all managers in the company are returned
	 * @param bool $group_name
	 * @return array
	 */
	public function get_managers_in_group( $group_name = false ) {
		global $wpdb;

		if( ! $group_name ) {
			$group_array = $this->get_groups_in_company( true );

			foreach( $group_array as $key => $value ) {
				if( $value == $this->company_name ) {
					$group_array[$key] = $this->group_prefix.Company::MANAGER;
				} else {
					$group_array[$key] = $this->group_prefix.$value."_".Company::MANAGER;
				}

			}
			$group_string = "'".implode("','", esc_sql( $group_array) )."'";
		} else {
			if( $group_name != $this->company_name ) {
				$group_name = $this->group_prefix.$group_name.'_';
			} else {
				$group_name = $this->group_prefix;
			}
			$group_string = "'".esc_sql( $group_name.Company::MANAGER )."'";
		}

		$results_array = $wpdb->get_col( "SELECT wp_groups_user_capability.user_id FROM wp_groups_user_capability
			JOIN wp_groups_capability ON (wp_groups_user_capability.capability_id = wp_groups_capability.capability_id
			AND wp_groups_capability.capability IN ({$group_string}))" );

		$results_array = array_unique( $results_array );
		return $results_array;
	}

	/**
	 * Returns all managers in the company as an array
	 * @return array|bool
	 */
	public function get_managers_in_company() {
		return $this->get_managers_in_group();
	}

	/**
	 * Returns all nonamangers in the company as an array
	 * @return array|bool
	 */
	public function get_nonmanagers_in_company() {
		return $this->get_nonmanagers_in_group();
	}


	/**
	 * Base function for recursive searches
	 * @param bool $group_name
	 * @param $type
	 * @return array|bool
	 */
	private function base_recursive($group_name = false, $type ) {
		$return_array = array();
		if( ! $group_name || $group_name == $this->company_name ) {
			$group_name = $this->company_name;
		}

		$child_group_array = $this->get_child_group_ids( $group_name );
		if( 'User' == $type ) {
			$return_array = $this->get_group_user_ids( $group_name );
		}

		if( count( $child_group_array ) > 0 ) {
			foreach( $child_group_array as $child_group_id ) {
				$child_group = \Groups_Group::read( $child_group_id );
				$child_group_name = $child_group->name;
				$child_group_name = str_replace( $this->group_prefix, '', $child_group_name );
				$child_results_array = $this->base_recursive( $child_group_name, $type );
				$return_array[$group_name][$child_group_name] =  $child_results_array;
			}
		}

		if( $group_name == $this->company_name && empty( $return_array ) && $type == 'Group' ) {
			$return_array = array( $this->company_name => array() );
		}

		return $return_array;
	}

	/**
	 * This function returns an hierarchical array starting with the group passed as the first
	 * argument. If no argument is given, then the entire company hierarchy is returned. Each group
	 * contains an array of user id's in that group, an array of child groups (nested) and an attribute
	 * that contains the group_id value (if the $include_group_id is set to true).
	 * @param bool $group_name
	 * @return array
	 */
	public function get_children_recursively( $group_name = false, $show_numbers_of_users_in_group = false, $include_group_id = true ) {

		$return_array = array();
		if( ! $group_name ) {
			$group_name = $this->company_name;
		}

		$return_array['Users'] = $this->get_group_user_ids( $group_name );

		if( $include_group_id ) {
			$return_array['group_id'] = $this->get_group_id( $group_name );
		}



		$child_group_array = $this->get_child_group_ids( $group_name );
		if( ! is_array( $return_array['Users'] ) ) {
			$return_array['Users'] = array();
		}

		if( is_array( $child_group_array ) && count( $child_group_array ) > 0 ) {
			foreach( $child_group_array as $child_group ) {
				$group_obj = \Groups_Group::read( $child_group );
				$group_name = str_replace( $this->group_prefix, '', $group_obj->name );
				$fetching_name = $group_name;
				if( $show_numbers_of_users_in_group ) {
					$user_set = $this->get_group_user_ids( $group_name );
					$user_count = 0;
					if( $user_set ) {
						$user_count = count( $user_set );
					}

					$group_name = $group_name.' ('.$user_count.')';
				}
				$return_array['Groups'][$group_name] = $this->get_children_recursively( $fetching_name, $show_numbers_of_users_in_group );
			}
		} else {
			$return_array['Groups'] = array();
		}

		return $return_array;
	}

	/**
	 * Function that is hooked into post delete for Company post types.
	 * It removes components associated with the group & child group not covered by
	 * the Groups plugin then calls the Groups delete method
	 */
	public function delete_company() {
		global $wpdb;

		$child_group_array = $this->get_groups_in_company( true );
		if( ( $key = array_search($this->company_name, $child_group_array ) ) !== false) {
			unset($child_group_array[$key]);
		}


		foreach( $child_group_array as $child_group ) {
			$group_id = $this->get_group_id( $child_group);
			\Groups_Group::delete( $group_id );
		}

		$capability_id_array = $wpdb->get_col( "SELECT capability_id FROM wp_groups_capability WHERE
capability LIKE '".$this->group_prefix."%';" );

		foreach( $capability_id_array as $capability_id ) {
			\Groups_Capability::delete( $capability_id );
		}

		\Groups_Group::delete( $this->group_id );
	}

	/**
	 * Flattens a multi-dimensional array into one dimension.
	 * The $use_keys parameter can be passed to compose an array of keys
	 * instead (as one would need when fetching groups)
	 * @param $array
	 * @param bool $use_keys
	 * @return array
	 */
	private function array_flatten( $array, $use_keys = false ) {
		$result_array = array();
		foreach( $array as $key => $value ) {
			if( is_array( $value ) ) {
				if( $use_keys ) {
					$result_array[] = $key;
				}

				if( count( $value ) > 0 ) {
					$result_array = array_merge( $result_array, $this->array_flatten( $value, $use_keys ) );
				}

			} else {
				if( $use_keys ) {
					$result_array[] = $key;
				} else {
					$result_array[] = $value;
				}

			}
		}

		return $result_array;
	}

	/**
	 * Updates the post's title with the company name
	 */
	private function update_post_title() {
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_title' =>  $this->company_name ), array( 'ID' => get_the_ID() ) );
	}
	
	public function set_num_employees($num_employees) {
		//echo "setting num employees to $num_employees for ".$this->ID();exit();
	
		update_post_meta($this->ID(), ttp_lms_prefix('num_employees'), $num_employees  );
	}
	
	public function get_num_employees() {
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT pm.meta_value FROM $wpdb->posts p 
			JOIN ($wpdb->postmeta pm) ON (p.ID = pm.post_id)
			WHERE p.post_type = %s AND p.ID = %d AND pm.meta_key = %s" ,
			ttp_lms_post_prefix(Constants::CompanyPostType), $this->id, ttp_lms_prefix('num_employees'));
		$num_employees = $wpdb->get_var($query);
		return $num_employees;
	}
}
?>