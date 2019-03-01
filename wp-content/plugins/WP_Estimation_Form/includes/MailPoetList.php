<?php
// Easier Developer API for WordPress plugin Mailpoet: https://wordpress.org/plugins/wysija-newsletters/

class MailPoetListEP {

  // Id for Mailpoet_List
  public $ID;

  /**
   * Constructor for Mailpoet list
   *
   * @param  int $id
   * @return static
   */
  function __construct($id)
  {
      $this->ID = $id;
  }

  /**
   * Get Mailpoet list by id
   *
   * @param  int  $id
   * @return static
   */
  public static function get($id)
  {
      return new static($id);
  }

  /**
   * Returns all maillists
   *
   * @return array( array() ) - Array of maililing lists
   *
   * Each element contains following values:
   * 'name' => string 'WordPress Users' (length=15)
   * 'list_id' => string '2' (length=1)
   * 'created_at' => string '1452241941' (length=10)
   * 'is_enabled' => string '0' (length=1)
   * 'is_public' => string '0' (length=1)
   * 'namekey' => string 'users' (length=5)
   * 'subscribers' => string '12' (length=2)
   * 'campaigns_sent' => string '0' (length=1)
   * 'belonging' => string '12' (length=2)
   * 'unsubscribers' => int 0
   * 'unconfirmed' => int 0
   * 'users' => string '12' (length=2)
   */
  public static function all() {
    return WYSIJA::get('list', 'model')->getLists();
  }

  /**
   * Creates new list into Mailpoet
   * @param $args - Arguments for the new list
   * For example:
   * array (
   *   'name' => 'Test'
   *   'description' => 'This list is for testing the mailpoet api'
   * )
   *
   * @return int - Returns the ID of the list if successful
   */
  public static function create(array $args) {

    $args['list_id'] = '';
    $args['created_at'] = time();
    $args['is_enabled'] = 1;

    return WYSIJA::get('list', 'model')->insert($args);
  }

  /**
   * Delete list from Mailpoet
   *
   * @return Bool - Result of list delete
   */
  public function delete() {

    $args = array( 'list_id' => $this->ID );
    return WYSIJA::get('list', 'model')->delete($args);
  }

  /**
   * Adds new user into
   * @param $user_id - WP_User id
   *
   * @return Bool
   */
  public function add_user($user_id) {
    // Get the user
    $user = get_user_by('id', $user_id );

    // In this array firstname and lastname are optional
    $user_data = array(
      'email' => $user->user_email,
      'firstname' => $user->user_firstname,
      'lastname' => $user->user_lastname
    );

    // Create model where user gets added into lists
    $data_subscriber = array(
      'user' => $user_data,
      'user_list' => array('list_ids' => array($this->ID))
    );
 
    return WYSIJA::get('user','helper')->addSubscriber($data_subscriber);
  }
  
  /**
   * Adds new contact into
   * @param $user_email
   *
   * @return Bool
   */
  public function add_contact($user_email,$list_id,$firstName,$lastName) {
    
    // In this array firstname and lastname are optional
    $user_data = array(
      'email' => $user_email,
      'firstname'=>$firstName,
      'lastname'=>$lastName
    );

    // Create model where user gets added into lists
    $data_subscriber = array(
      'user' => $user_data,
      'user_list' => array('list_ids' => array($list_id))
    );
 
    return WYSIJA::get('user','helper')->addSubscriber($data_subscriber);
  }

  /**
   * Adds new user into
   * @param $user_id - WP_User id
   *
   * @param $list_ids - List ids to which add user
   *
   * @return Bool
   */
  public static function add_user_to_multiple_lists($user_id, $list_ids) {
    // Get the user
    $user = get_user_by('id', $user_id );

    // In this array firstname and lastname are optional
    $user_data = array(
      'email' => $user->user_email,
      'firstname' => $user->user_firstname,
      'lastname' => $user->user_lastname
    );

    // Create model where user gets added into lists
    $data_subscriber = array(
      'user' => $user_data,
      'user_list' => array('list_ids' => $list_ids)
    );

    return WYSIJA::get('user','helper')->addSubscriber($data_subscriber);
  }
}