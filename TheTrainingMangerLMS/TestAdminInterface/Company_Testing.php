<?php
namespace TheTrainingMangerLMS\TestAdminInterface;

import('TheTrainingMangerLMS.Company');

	/**
	 * Training LMS class Company Test
	 *
	 * This class encapsulates methods & attributes
	 * needed for testing the Company class via the
	 * custom post type interface found in the WP admin.
	 *
	 **/

/*
	The class
*/
class Company_Testing {
	public $company;
	public $test_field_array;

	/**
	 * Make a list of field & field attributes to show in the WP admin
	 */
	private function __construct() {
		// Fields needed for simple test interface
		$this->test_field_array = array(
			'thetrainingpartners_company_overview' => array( 'title' => 'Company Overview' ),
			'thetrainingpartners_company_name' => array(
				'title' => 'Provide A Company Name',
				'html-id' => 'thetrainingpartners-company-name',
				'description' => 'Provide a company name',
				'requires_parent' => false
			),
			'thetrainingpartners_company_employees' => array(
				'title' => 'How Many Employees Are Present',
				'html-id' => 'thetrainingpartners-company-employees',
				'description' => 'How many employees are present',
				'requires_parent' => false
			),
			'thetrainingpartners_company_add_child_group' => array(
				'title' => 'Add Child Group',
				'html-id' => 'thetrainingpartners-company-add-child-group',
				'description' => 'Add child group to parent group',
				'requires_parent' => true),
			'thetrainingpartners_company_delete_child_group' => array(
				'title' => 'Delete Child Group',
				'html-id' => 'thetrainingpartners-company-delete-child-group',
				'description' => 'Delete child from parent group',
				'requires_parent' => false
			),
			'thetrainingpartners_company_add_user' => array(
				'title' => 'Add User To Group',
				'html-id' => 'thetrainingpartners-company-add-user',
				'description' => 'Add user to group',
				'requires_parent' => true
			),
			'thetrainingpartners_company_delete_user' => array(
				'title' => 'Delete User From Group',
				'html-id' => 'thetrainingpartners-company-delete-user',
				'description' => 'Delete user from group',
				'requires_parent' => true
			),
			'thetrainingpartners_company_add_manager' => array(
				'title' => 'Make user manager of a group',
				'html-id' => 'thetrainingpartners-company-add-manager',
				'description' => 'Add user capability',
				'requires_parent' => true
			),
			'thetrainingpartners_company_delete_manager' => array(
				'title' => 'Remove manager status from user',
				'html-id' => 'thetrainingpartners-company-delete-manager',
				'description' => 'Delete user capability',
				'requires_parent' => true
			),
			'thetrainingpartners_company_superadmin' => array(
				'title' => 'Give User Super Admin Capability',
				'html-id' => 'thetrainingpartners_company_superadmin',
				'description' => 'Set company super admin',
				'requires_parent' => false
			)
		);

		$this->setup_actions();
	}

	public static function Instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new Company_Testing();
		}
	}

	private function setup_actions() {
		add_action( 'save_post',  array( $this, 'save_post' ), 10, 1 );
		add_action( 'add_meta_boxes', array( $this, 'add_company_fields' ), 10, 1 );
	}

	/**
	 * Call proper function to print field box content
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	public function field_set_callback( $post, $metabox ) {
		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */

		$field_name = $metabox['args']['field_name'];

		switch( $field_name ) {
			case 'thetrainingpartners_company_name':
			case 'thetrainingpartners_company_employees':
			case 'thetrainingpartners_company_add_child_group':
			case 'thetrainingpartners_company_add_user':
			case 'thetrainingpartners_company_add_manager':
			case 'thetrainingpartners_company_superadmin':
			case 'thetrainingpartners_company_delete_child_group':
			case 'thetrainingpartners_company_delete_user':
			case 'thetrainingpartners_company_delete_manager':
				$this->test_meta_box_for_text( $field_name );
				break;
			case 'thetrainingpartners_company_overview':
				$this->test_meta_box_for_info();
				break;

		}
	}

	/**
	 * Print the box elements needed for user interaction
	 * @param $field_name
	 */
	private function test_meta_box_for_text( $field_name ) {
		$field_values_array = $this->test_field_array[$field_name];

		echo '<label for="'.$field_values_array['html-id'].'">';
		echo $field_values_array['description'];
		echo '</label> ';
		if( 'thetrainingpartners_company_add_child_group' == $field_name || 'thetrainingpartners_company_delete_child_group' == $field_name ||
			'thetrainingpartners_company_name' == $field_name || 'thetrainingpartners_company_employees' == $field_name ) {
			echo '<input type="text" id="'.$field_values_array['html-id'].'" name="'.$field_name.'" value="" size="25" />';
		} else {
			wp_dropdown_users( array( 'id' =>$field_values_array['html-id'], 'name' => $field_name, 'show_option_none' => 'None' ) );
		}

		if( $field_values_array['requires_parent'] ) {
			echo '<br />';

			if( 'thetrainingpartners_company_add_manager' == $field_name || 'thetrainingpartners_company_delete_manager' == $field_name ) {
				echo '<label for="'.$field_values_array['html-id'].'-group">';
				echo 'Group name (leave blank for company)';
				echo '</label> ';
				echo '<input type="text" id="'.$field_values_array['html-id'].'-group" name="'.$field_name.'_group" value="" size="25" />';


			} else {
				echo '<label for="'.$field_values_array['html-id'].'-parent">';
				echo 'Parent group name (leave blank for company group)';
				echo '</label> ';
				echo '<input type="text" id="'.$field_values_array['html-id'].'-parent" name="'.$field_name.'_parent" value="" size="25" />';

				if( 'thetrainingpartners_company_add_child_group' == $field_name ) {
					echo '<br />';
					echo '<label for="'.$field_values_array['html-id'].'-count">';
					echo 'Description';
					echo '</label> ';
					echo '<input type="text" id="'.$field_values_array['html-id'].'-count" name="'.$field_name.'_count" value="" size="25" />';

				}
			}
		}

	}

	/**
	 * Function to display current values
	 */
	private function test_meta_box_for_info() {
		$company_name = get_post_meta( get_the_ID(), 'post_group', true );
		if( empty( $company_name ) ) {
			return;
		}

		$company = new \TheTrainingMangerLMS\Company( $company_name, false, false, false );
		echo "Company name is ".$company_name;
		echo "<br />Super Admin is ".$company->get_super_admin()->user_nicename;
		echo "<br />The company structure is<br />";
		echo "<pre>";
		print_r( $company->get_children_recursively( false, true ) );
		echo "</pre>";
		
		echo "<br />Employees in Company<br />";
		echo "<pre>";
		print_r( $company->get_num_employees());
		echo "</pre>";
		
		echo "<br />All users in the company (structured example)<br />";
		echo "<pre>";
		print_r( $company->get_users_in_company());
		echo "</pre>";

		echo "<br />All groups in the company (one-dimensional argument passed in example)<br />";
		echo "<pre>";
		print_r( $company->get_groups_in_company( true ) );
		echo "</pre>";

		echo "<br />All managers in the company<br />";
		echo "<pre>";
		print_r( $company->get_managers_in_company() );
		echo "</pre>";

		echo "<br />All non-managers in the company<br />";
		echo "<pre>";
		print_r( $company->get_nonmanagers_in_group() );
		echo "</pre>";

		echo "<br />All users belows super admin<br />";
		echo "<pre>";
		$admin_id = $company->get_super_admin()->id;
		$users_under_admin = $company->get_users_below_user( $admin_id, true );
		print_r($users_under_admin);
		echo "</pre>";

	}

	/**
	 * Called when the post is saved & uses the actual Company() methods.
	 * Each if statement and actions represent interaction with the Company() class
	 */
	public function test_post_save() {
		/*
		 * As these are only from the admin testing panel,
		 * we only need to do simple checks before performing an action
		 */
		$stripped_post = stripslashes_deep( $_POST );

		if( $this->test_has_value( $stripped_post['thetrainingpartners_company_add_child_group'] ) ) {

			$employee_count = false;
			if( $this->test_has_value( $stripped_post['thetrainingpartners_company_add_child_group_count'] ) ) {
				$employee_count = $stripped_post['thetrainingpartners_company_add_child_group_count'];
			}


			if( $this->test_has_value( $stripped_post['thetrainingpartners_company_add_child_group_parent'] ) ) {
				$parent_name = $stripped_post['thetrainingpartners_company_add_child_group_parent'];
				$this->company->create_child_group( $stripped_post['thetrainingpartners_company_add_child_group'], $employee_count, $parent_name );
			} else {
				$this->company->create_child_group( $stripped_post['thetrainingpartners_company_add_child_group'], $employee_count );
			}
		}


		if( $this->test_has_value( $stripped_post['thetrainingpartners_company_delete_child_group'] ) ) {
			$this->company->delete_child_group( $stripped_post['thetrainingpartners_company_delete_child_group'] );
		}

		if( -1 != $stripped_post['thetrainingpartners_company_add_user'] ) {
			if( $this->test_has_value( $stripped_post['thetrainingpartners_company_add_user_parent'] ) ) {
				$parent_group = $stripped_post['thetrainingpartners_company_add_user_parent'];
				$this->company->add_user( $stripped_post['thetrainingpartners_company_add_user'], $parent_group );
			} else {
				$this->company->add_user( $stripped_post['thetrainingpartners_company_add_user'] );
			}
		}

		if( -1 != $stripped_post['thetrainingpartners_company_delete_user'] ) {
			if( $this->test_has_value( $stripped_post['thetrainingpartners_company_delete_user_parent'] ) ) {
				$parent_group = $stripped_post['thetrainingpartners_company_delete_user_parent'];
				$this->company->delete_user( $stripped_post['thetrainingpartners_company_delete_user'], $parent_group );
			} else {
				$this->company->delete_user( $stripped_post['thetrainingpartners_company_delete_user'] );
			}
		}

		if( -1 != $stripped_post['thetrainingpartners_company_add_manager'] ) {
			if( $this->test_has_value( $stripped_post['thetrainingpartners_company_add_manager_group'] ) ) {
				$group = $stripped_post['thetrainingpartners_company_add_manager_group'];
				$this->company->create_manager_in_group( $stripped_post['thetrainingpartners_company_add_manager'], $group );
			} else {
				$this->company->create_manager_in_group( $stripped_post['thetrainingpartners_company_add_manager']  );
			}
		}

		if( -1 != $stripped_post['thetrainingpartners_company_delete_manager'] ) {
			if( $this->test_has_value( $stripped_post['thetrainingpartners_company_delete_manager_group'] ) ) {
				$group = $stripped_post['thetrainingpartners_company_delete_manager_group'];
				$this->company->delete_manager_from_group( $stripped_post['thetrainingpartners_company_delete_manager'], $group );
			} else {
				$this->company->delete_manager_from_group( $stripped_post['thetrainingpartners_company_delete_manager']  );
			}
		}



		if( -1 != $stripped_post['thetrainingpartners_company_superadmin'] ) {
			$this->company->set_super_admin( $stripped_post['thetrainingpartners_company_superadmin'] );
		}
	}

	/**
	 * Simple helper method to see if a value exists
	 * @param $value
	 * @return bool
	 */
	public function test_has_value($value) {
		$return = false;
		if( isset( $value ) && ! empty( $value ) ) {
			$return = true;
		}
		return $return;
	}

	public function save_post( $post_id ) {
		if( get_post_type($post_id) !== ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::CompanyPostType )) {
			return;
		}
		$post = get_post($post_id);
		if($post->post_status == 'trash' or $post->post_status == 'auto-draft'){
			return $post_id;
		}
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		$stripped_post = stripslashes_deep( $_POST );

		if( get_post_type() == ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::CompanyPostType ) ) {
			if( $this->test_has_value( $stripped_post['thetrainingpartners_company_name'] ) ) {
				$company_name = $stripped_post['thetrainingpartners_company_name'];
				add_post_meta( $post_id, 'post_group', $company_name, true ) || update_post_meta( $post_id, 'post_group', $company_name );
			} else {
				$company_name = get_post_meta( $post_id, 'post_group', true );
			}

			$company_size = false;
			if( $this->test_has_value( $stripped_post['thetrainingpartners_company_employees'] ) ) {
				$company_size = $stripped_post['thetrainingpartners_company_employees'];
			}

			$this->company = new \TheTrainingMangerLMS\Company( $company_name, $company_size );
			$this->test_post_save();
		}
	}

	public function add_company_fields() {

		$group_name = get_post_meta( get_the_ID(), 'post_group', true );

	  if( ! empty( $group_name ) ) {
			unset( $this->test_field_array['thetrainingpartners_company_name'] );
			unset( $this->test_field_array['thetrainingpartners_company_employees'] );
		}

		$post_type = ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::CompanyPostType );
		foreach( array_keys( $this->test_field_array ) as $field_name ) {
			add_meta_box( $field_name,
				$this->test_field_array[$field_name]['title'],
				array($this, 'field_set_callback'),
				$post_type,
				'normal',
				'high',
				array( 'field_name' => $field_name ) );
		}
	}
}

Company_Testing::Instance();
