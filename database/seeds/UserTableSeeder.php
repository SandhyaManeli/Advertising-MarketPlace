<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClientType;
use App\Models\Client;
use App\Models\ClientMongo;
use App\Models\Role;
use App\Models\User;
use App\Models\UserMongo;
use App\Models\Permission;


class UserTableSeeder extends Seeder
{

    private $dev_permissions;
    private $basic_user_permissions;
    private $owner_permissions;
    private $company_types;

    // Constructor to set up the necessary values
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
        $this->basic_user_permissions = [
            "GET-countries",
            "GET-states",
            "GET-cities",
            "GET-allCities",
            "GET-areas",
            "GET-allAreas",
            "GET-autocomplete-area",
            "GET-products",
            "GET-map-products",
            "GET-formats",
            "GET-shortlistedProducts",
            "POST-shortlistProduct",
            "DELETE-shortlistedProduct",
            "GET-searchBySiteNo",
            "POST-share-shortlisted",
            "GET-logout",
            "GET-verify-email",
            "GET-user-profile",
            "POST-request-reset-password",
            "POST-reset-password",
            "GET-user-campaigns",
            "GET-get-all-campaigns",
            "GET-active-user-campaigns",
            "GET-user-campaign",
            "POST-user-campaign",
            "POST-product-to-campaign",
            "POST-suggestion-request",
            "POST-share-campaign",
            "GET-export-all-campaigns",
            "DELETE-user-campaign",
            "GET-request-proposal",
            "GET-request-campaign-launch",
            "POST-request-quote-change",
            "GET-all-notifications",
            "GET-update-notification-read",
            "GET-metro-corridors",
            "GET-metro-packages",
            "GET-product-unavailable-dates",
            "GET-request-campaign-booking",
            "GET-confirm-campaign-booking"
        ];

        $this->owner_permissions = [

        ];

        $this->company_types = [
            "bbi",
            "owner",
            "agency"
        ];
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // create type of companies we'll be dealing with
        foreach($this->company_types as $ct){
            $client_type = new ClientType();
            $client_type->type = $ct;
            $client_type->save();
        }
        
        $bb_type = ClientType::where("type", "=", 'bbi')->first();
        $owner_type = ClientType::where("type", "=", "owner")->first();

        // create first client (Billboards India)
        $client = new Client();
        $client->company_name = "BBI";
        $client->activated = true;
        $client->company_slug = "bbi";
        $client->type = $bb_type->id;
        $client->save();

        $client_mongo = new ClientMongo;
        $client_mongo->id = uniqid();
        $client_mongo->client_id = $client->id;
        $client_mongo->contact_name = "Mridul Kashyap";
        $client_mongo->email = "mridulkashyap57@gmail.com";
        $client_mongo->name = "Billboards India";
        $client_mongo->phone = "9550094213";
        $client_mongo->client_type = $bb_type->id;
        $client_mongo->address = "";
        $client_mongo->save();

        // create first user (Billboards India admin)
        $user = new User();
        $user->client_id = Client::where('company_name', '=','BBI')->first()->id;
        $user->username = 'bbadmin';
        $user->email = 'mridulkashyap57@gmail.com';
        $user->salt = 'bb94213';
        $user->password = md5('mk@bbindia123' . $user->salt);
        $user->activated = true;       
        $user->save(); 
        // assigning the user to client
        $client->super_admin = $user->id;
        $client->save();
        // continuing with the profile details to be saved in mongo
        $user_mongo = new UserMongo;
        $user_mongo->id = uniqid();
        $user_mongo->user_id = $user->id;
        $user_mongo->first_name = "Mridul";
        $user_mongo->last_name = "Kashyap";
        $user_mongo->email = "mridulkashyap57@gmail.com";
        $user_mongo->phone = "9550094213";
        $user_mongo->company_name = "Billboard India";
        $user_mongo->company_type = "bbi";
        $user_mongo->client_mongo_id = $client_mongo->id;
        $user_mongo->save();


        // create super admin role
        $admin_role = new Role();
        $admin_role->client_id = $client->id;
        $admin_role->name = "super_admin";
        $admin_role->description = "The ulitmate user of application. Has every permission that can exist in the application";
        $admin_role->save();

        // create basic user role, the default role that'll be given to a user when he 
        // registers himself.
        $basic_user_role = new Role();
        $basic_user_role->client_id = $client->id;
        $basic_user_role->name = "basic_user";
        $basic_user_role->display_name = "Basic User";
        $basic_user_role->description = "The most basic user of application. Has the permissions only related to viewing products, locations, profile etc.";
        $basic_user_role->save();

        //create owner role
        $owner_role = new Role();
        $owner_role->client_id = $client->id;
        $owner_role->name = "owner";
        $owner_role->display_name = "Ad Space Owner";
        $owner_role->description = "Owner of ad spaces. May have inventory shared with Billboards India.";
        $owner_role->save();

        // setting the permissions for defined roles        
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
            // inserting remaining permissions into the database and 
            // assigning them to appropriate roles
            foreach($route_list as $route){
                $permission = new Permission();
                $permission->name = $route;
                $permission->save();
                $admin_role->permissions()->attach($permission);
                // assigning of owner/agency permissions should be done by the super
                // admin himself

                // setting up basic user permissions
                if(in_array($route, $this->basic_user_permissions)){
                    $basic_user_role->permissions()->attach($permission);
                }
            }

            // assign super_admin role to the user
            $user->roles()->attach($admin_role);
        }
        catch(Exception $ex){
            Log::error($ex);
        }
    }
}
