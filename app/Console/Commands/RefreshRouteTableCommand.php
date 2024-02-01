<?php 

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Client;
use App\Models\User;
use Log;

class RefreshRouteTableCommand extends Command {
    
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'route_table:refresh';
  
  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "Refreshes the route list and updates the permissions tables in db";

  private $dev_permissions;
  // private $basic_user_permissions;

  public function __construct(){

    $this->dev_permissions = [
      "GET-test-mail",
      "GET-loginRequired",
      "GET-isOwner",
      "GET-isAdmin",
      "GET-isAdminOrOwner",
      "GET-test-pdf",
      "GET-test-view"
    ];

    // $this->basic_user_permissions = [
    //   "GET-countries",
    //   "GET-states",
    //   "GET-cities",
    //   "GET-allCities",
    //   "GET-areas",
    //   "GET-allAreas",
    //   "GET-autocomplete-area",
    //   "GET-products",
    //   "GET-formats",
    //   "GET-shortlistedProducts",
    //   "POST-shortlistProduct",
    //   "DELETE-shortlistedProduct",
    //   "GET-searchBySiteNo",
    //   "POST-share-shortlisted",
    //   "GET-logout",
    //   "GET-verify-email",
    //   "GET-user-profile",
    //   "POST-request-reset-password",
    //   "POST-reset-password",
    //   "GET-campaigns",
    //   "GET-get-all-campaigns",
    //   "GET-planned-campaigns",
    //   "GET-campaign",
    //   "POST-campaign",
    //   "POST-product-to-campaign",
    //   "POST-suggestion-request",
    //   "POST-share-campaign",
    //   "GET-export-all-campaigns",
    //   "DELETE-campaign",
    //   "GET-request-proposal",
    //   "GET-request-campaign-launch",
    //   "POST-request-quote-change",
    //   "GET-all-notifications",
    //   "GET-update-notification-read"
    // ];

    parent::__construct();
  }
  
  /**
   * Execute the console command.
   *
   * @return void
   */
  public function handle()
  {
    try{
      // create permission list
      $routes = app()->getRoutes();
      $route_list = [];
      foreach($routes as $route){
          if(preg_match('/\/api\//', $route['uri'])){
              array_push($route_list, $route['method'] . '-' . preg_replace('/\/api\/([a-zA-Z0-9\-]*)(\[\/\{.*)?|(\/.*)?/', "$1", $route['uri']));
          }
      }

      // remove dev routes
      $route_list = array_diff(array_unique($route_list), $this->dev_permissions);

      // find the newly added routes(routes added after last update)
      $old_routes = Permission::pluck('name')->toArray();

      $newly_added_routes = array_diff($route_list, $old_routes);
      $removed_routes = array_diff($old_routes, $route_list);

      // delete removed routes from table
      foreach($removed_routes as $removed_route){
        Permission::where('name', '=', $removed_route)->delete();
      }

      // getting super_admin role to add these permissions to later
      $admin_role = Role::where('name', '=', 'super_admin')->first();

      // assign newly added permissions to super_admin of BBI
      $bbi_super_admin_id = Client::where('company_name', '=', 'BBI')->first()->super_admin;
      $user = User::where('id', '=', $bbi_super_admin_id)->first();

      // inserting new permissions into the database and 
      // assigning them to appropriate roles
      foreach($newly_added_routes as $route){
          $permission = new Permission();
          $permission->name = $route;
          $permission->save();

          // adding the permission to super admin role
          $admin_role->permissions()->attach($permission);
      }

      // $basic_user_role = Role::where('name', '=', 'basic_user')->first();
      // $basic_user_routes = Permission::whereIn('name', $this->basic_user_permissions)->get();
      // foreach($basic_user_routes as $basic_perm){
      //   $basic_user_role->permissions()->attach($basic_perm);
      // }
    }
    catch(Exception $ex){
        Log::error($ex);
    }
  }
}