<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('brpr_acf_field_relation_field') ) :

class brpr_acf_field_relation_field extends acf_field {
	
	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options
		
		
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct( $settings )
	{
		// vars
		$this->name = 'brpr_relation_field';
		$this->label = __('BrPr Relation Field');
		$this->category = __("Relational",'brpr_relation_field'); // Basic, Content, Choice, etc
		$this->defaults = array(
			// add default here to merge into your field. 
			// This makes life easy when creating the field options as you don't need to use any if( isset('') ) logic. eg:
			//'preview_size' => 'thumbnail'
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// settings
		$this->settings = $settings;

		add_action('save_post', array($this,'post_updated'));
	}
	
	function post_updated($post_id) {
		if (
			isset($_POST['brpr_relation_field_order']) &&
			isset($_POST['brpr_relation_field_order_changed']) &&
			isset($_POST['brpr_relation_field_order_by']) &&
			$_POST['brpr_relation_field_order_by'] &&
			!empty($_POST['brpr_relation_field_order'])
		) {
			if ($_POST['brpr_relation_field_order_changed'] == '1') {
				$order = $_POST['brpr_relation_field_order'];
				foreach ($order as $key => $id) {
					update_post_meta(
						$id, 
						$_POST['brpr_relation_field_order_by'], 
						$key+1
					);
				}
			}
			unset($_POST['brpr_relation_field_order']);
			unset($_POST['brpr_relation_field_order_changed']);
		}
		
	}
	
	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like below) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function create_options( $field )
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/
		
		// key is needed in the field names to correctly save the data
		$key = $field['name'];
		
		// Create Field Options HTML
		?>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label>
			<?php _e("Relation to type",'brpr_relation_field'); ?>
			<span class="required">*</span>			
		</label>
		<p class="description">
			<?php _e("many to one",'brpr_relation_field'); ?>
		</p>
	</td>
	<td>
		<?php

		$post_types = get_post_types();
		do_action('acf/create_field', array(
			'type'		=>	'select',
			'name'		=>	'fields['.$key.'][relation_to_type]',
			'value'		=>	$field['relation_to_type'],
			'layout'	=>	'horizontal',
			'choices'	=>	$post_types
		));
		
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label>
			<?php 
				_e("Related entity FK meta field name", 'brpr_relation_field'); 
			?>
			<span class="required">*</span>
		</label>
		<p class="description">
			<?php //_e("many to one",'brpr_relation_field'); ?>
		</p>
	</td>
	<td>
		<?php
		
		$post_types = get_post_types();
		do_action('acf/create_field', array(
			'type'			=>	'text',
			'name'			=>	'fields['.$key.'][related_entity_fk]',
			'value'			=>	$field['related_entity_fk'],
			'layout'		=>	'horizontal',
			'placeholder'	=>	__("Enter related FK here", 'brpr_relation_field')
		));
		
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label>
			<?php 
				_e("Sort related records by meta field", 'brpr_relation_field'); 
			?>
			<span class="required">*</span>
		</label>
		<p class="description">
			<?php //_e("",'brpr_relation_field'); ?>
		</p>
	</td>
	<td>
		<?php
		
		$post_types = get_post_types();
		do_action('acf/create_field', array(
			'type'			=>	'text',
			'name'			=>	'fields['.$key.'][sort_meta_field_name]',
			'value'			=>	$field['sort_meta_field_name'],
			'layout'		=>	'horizontal',
			'placeholder'	=>	__("Field name", 'brpr_relation_field')
		));
		
		?>
	</td>
</tr>
		<?php
		
	}
	
	
	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function create_field( $field )
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/
		
		// perhaps use $field['preview_size'] to alter the markup?
		
		$id = $_GET['post'];
		$args = [
			'post_type' => $field['relation_to_type'],
			'meta_key' => $field['sort_meta_field_name'],
			'orderby' => 'meta_value',
			'order' => 'asc',
			'meta_query' => [
				'key'     => $field['related_entity_fk'],
                'value'   => $id
			],
			'posts_per_page' => -1,
		];
		$posts = get_posts($args);
		$addParams = [
			'acf-field-'.$field['related_entity_fk'] => $id,
			'post_type' => $field['relation_to_type'],
		];
		$addlink = admin_url('post-new.php').'?'.http_build_query($addParams);
		// create Field HTML
		?>
<div class="brpr_relation_field_holder">
	<input 
		type="hidden" 
		name="brpr_relation_field_order_changed"
		value="0"
	/>
	<input 
		type="hidden" 
		name="brpr_relation_field_order_by"
		value="<?php echo $field['sort_meta_field_name']; ?>"
	/>
	<ul class="brpr_relation_field">
		<?php foreach($posts as $post): ?>
			<?php 
				$fk = $field['sort_meta_field_name'];
				$edit_text = __('Edit post', 'brpr_relation_field');
				$link = get_edit_post_link($post->ID, ''); 
			?>
			<li draggable="true">
				<div class="item-wrapper">
					<input 
						type="hidden" 
						value="<?php echo $post->ID ?>"
						name="brpr_relation_field_order[]" 
					/>
					<div class="name">
						<?php 
							echo apply_filters(
								'the_content',
								$post->post_title
							); 
						?>
					</div>
					<div class="link">
						<a 
							href="<?php echo $link; ?>" 
							target="_blank"
						>
							<?php echo $edit_text; ?>
						</a>
					</div>
				</div>					
			</li>
		<?php endforeach; ?>
	</ul>
	<a 	
		href="<?php echo $addlink; ?>"
		target="_blank"
		class="field-bottom-link"
	>
		<?php _e('+ Add new child'); ?>
	</a>
</div> 
		<?php
	}
	
	
	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your create_field() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function input_admin_enqueue_scripts()
	{
		// Note: This function can be removed if not used
		
		
		// vars
		$url = $this->settings['url'];
		$version = $this->settings['version'];
		
		
		// register & include JS
		wp_register_script(
			'jquery_ui', 
			"{$url}assets/js/jquery_ui.js", 
			array('acf-input')
		);
		wp_enqueue_script('jquery_ui');
		wp_register_script(
			'brpr_relation_field', 
			"{$url}assets/js/input.js", 
			array('acf-input','jquery_ui'), 
			$version
		);
		wp_enqueue_script('brpr_relation_field');

		// register & include CSS
		wp_register_style('brpr_relation_field', "{$url}assets/css/input.css", array('acf-input'), $version);
		wp_enqueue_style('brpr_relation_field');
		
	}
	
	
	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your create_field() action.
	*
	*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function input_admin_head()
	{
		// Note: This function can be removed if not used
		
	}
	
	
	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your create_field_options() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function field_group_admin_enqueue_scripts()
	{
		// Note: This function can be removed if not used
	}

	
	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your create_field_options() action.
	*
	*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function field_group_admin_head()
	{
		// Note: This function can be removed if not used
	}


	/*
	*  load_value()
	*
		*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in the database
	*/
	
	function load_value( $value, $post_id, $field )
	{
		// Note: This function can be removed if not used
		return $value;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field )
	{
		// Note: This function can be removed if not used
		
		return $value;
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is applied to the $value after it is loaded from the db and before it is passed to the create_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value( $value, $post_id, $field )
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/
		
		// perhaps use $field['preview_size'] to alter the $value?
		
		
		// Note: This function can be removed if not used
		return $value;
	}
	
	
	/*
	*  format_value_for_api()
	*
	*  This filter is applied to the $value after it is loaded from the db and before it is passed back to the API functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value_for_api( $value, $post_id, $field )
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/
		
		// perhaps use $field['preview_size'] to alter the $value?
		
		
		// Note: This function can be removed if not used
		return $value;
	}
	
	
	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	
	function load_field( $field )
	{
		// Note: This function can be removed if not used
		return $field;
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	function update_field( $field, $post_id )
	{
		// Note: This function can be removed if not used
		return $field;
	}
}


// initialize
new brpr_acf_field_relation_field( $this->settings );


// class_exists check
endif;

?>