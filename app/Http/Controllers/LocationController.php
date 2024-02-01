<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Area;
use App\Models\User;
use App\Models\Product;
use Auth;
use Log;
use App\Jobs\UpdateCountryEverywhere;
use App\Jobs\UpdateStateEverywhere;
use App\Jobs\UpdateCityEverywhere;
use App\Jobs\UpdateAreaEverywhere;

class LocationController extends Controller
{
  private $input;
  private $request;
	/**
  * Create a new controller instance.
  *
  * @return void
  */
	public function __construct(Request $request)
	{
    $this->request = $request;
    if ($request->isJson()) {
			$this->input = $request->json()->all();
		} else {
			$this->input = $request->all();
		}
		// Resolve dependencies out of container
		// $this->middleware('auth', ['only' => [
			// 'saveMarkers'
		// ]]);
	}

	public function getCountries(){
		$countries = Country::orderBy('name', 'asc')->get();
		return response()->json($countries);
	}

  public function getStates($country_id = null){
    if(empty($country_id)){
      $states = State::orderBy('name', 'asc')->get();
    }
    else{
      $states = State::where('country_id', '=', $country_id)->orderBy('name', 'asc')->get();
    }
    return response()->json($states);
  }  
  public function getCities($state_ids){
    $state_ids = explode(',' , $state_ids);
    $cities = City::whereIn('state_id', $state_ids)->orderBy('name', 'asc')->get();
    return response()->json($cities);
  }
  public function getAllCities(){
    //$cities = City::orderBy('name', 'asc')->get();
    $cities = City::orderBy('state_name', 'asc')->get();
    return response()->json($cities);
  }
  public function getAreas($city_ids){
    $city_ids = explode(',' , $city_ids);
    $areas = Area::whereIn('city_id', $city_ids)->orderBy('name', 'asc')->get();
    return response()->json($areas);
  }
  public function getAllAreas(){
    $page_no = $this->request->input('page_no');
		$page_size = $this->request->input('page_size');
		if(isset($page_no, $page_size) && !empty($page_no) && !empty($page_size)){
			$offset = ($page_no - 1) * $page_size;
      $areas = Area::orderBy('name', 'asc')->skip($offset)->take((int)$page_size)->get();
			$area_list_data = [
				"areas" => $areas,
				"page_count" => ceil(Area::count() / $page_size)
			];
		}
		else{
			$areas = Area::orderBy('name', 'asc')->get();
			$area_list_data = [
				"areas" => $areas,
			];
		}
		return response()->json($area_list_data);
  }

  public function addCountry(){
    if(isset($this->input['id'])){
      $country_obj = Country::where('id', '=', $this->input['id'])->first();
      $country_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
      if($country_obj->save()){
        return response()->json(["status" => "1", "message" => "Country updated successfully."]);
      }
      else{
        return response()->json(["status" => "0", "message" => "Couldn't update the country."]);
      } 
    }
    else{
      $this->validate($this->request, 
        [
          'name' => 'required'
        ],
        [
          'name.required' => 'Country name is required'
        ]
      );
      $country_obj = new Country;
      $country_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
      $country_obj->id = uniqid();
      if($country_obj->save()){
        return response()->json(["status" => "1", "message" => "Country added successfully."]);
      }
      else{
        return response()->json(["status" => "0", "message" => "Couldn't add the country."]);
      } 
    }
  }

  public function deleteCountry($country_id){
    $country_to_del = Country::where('id', '=', $country_id)->first();
    // check if it has cities
    $states_in_country_in_question = State::where('country_id', '=', $country_id)->get()->toArray();
    if(count($states_in_country_in_question) > 0){
      return response()->json(["status" => "0", "message" => "Failed to delete country. Country has state(s) associated with it."]);
    }
    else{
      $success = $country_to_del->delete();
      if($success){
        return response()->json(["status" => "1", "message" => "Country deleted successfully."]);
      }
      else{
        return response()->json(["status" => "1", "message" => "An error occured while deleting the country."]);
      }
    }
  }

  public function addState(){
    $this->validate($this->request, 
      [
        'name' => 'required',
        'country_id' => 'required'
      ],
      [
        'name.required' => 'State name is required',
        'country_id.required' => 'Country id is required'
      ]
    );
    $country = Country::where('id', '=', $this->input['country_id'])->first();
    if(empty($country)){
      return response()->json(["status" => "0", "message" => "No country found by given id."]);
    }
    else{
      if(isset($this->input['id'])){
        $state_obj = State::where('id', '=', $this->input['id'])->first();
        $state_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
        $state_obj->country_id = isset($this->input['country_id']) ? $this->input['country_id'] : "";
        $state_obj->country_name = $country->name;    
        if($state_obj->save()){
          try{
            dispatch(new UpdateStateEverywhere($state_obj));
					 // Log::info("job completed: UpdateStateEverywhere with data" . serialize($state_obj));            
          }
          catch (Exception $ex){
            Log::error($ex);
          }
          return response()->json(["status" => "1", "message" => "State added successfully."]);
        }
        else{
          return response()->json(["status" => "0", "message" => "Couldn't add the state."]);
        }
      }
      else{
        $state_obj = new State;
        $state_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
        $state_obj->country_id = isset($this->input['country_id']) ? $this->input['country_id'] : "";
        $state_obj->country_name = $country->name;    
        $state_obj->id = uniqid();
        if($state_obj->save()){
          return response()->json(["status" => "1", "message" => "State added successfully."]);
        }
        else{
          return response()->json(["status" => "0", "message" => "Couldn't add the state."]);
        }
      }
    }
  }

  public function deleteState($state_id){
    $state_to_del = State::where('id', '=', $state_id)->first();
    // check if it has cities
    $cities_in_state_in_question = City::where('state_id', '=', $state_id)->get()->toArray();
    if(count($cities_in_state_in_question) > 0){
      return response()->json(["status" => "0", "message" => "Failed to delete state. State has city(s) associated with it."]);
    }
    else{
      $success = $state_to_del->delete();
      if($success){
        return response()->json(["status" => "1", "message" => "State deleted successfully."]);
      }
      else{
        return response()->json(["status" => "1", "message" => "An error occured while deleting the state."]);
      }
    }
  }

  public function addCity(){
    $this->validate($this->request, 
      [
        'name' => 'required',
        'state_id' => 'required'
      ],
      [
        'name.required' => 'City name is required',
        'state_id.required' => 'State id is required'
      ]
    );
    $state = State::where('id', '=', $this->input['state_id'])->first();
    if(empty($state)){
      return response()->json(["status" => "0", "message" => "No state found by given id."]);
    }
    else{
      $country = Country::where('id', '=', $state->country_id)->first();
      if(isset($this->input['id'])){
        $city_obj = City::where('id', '=', $this->input['id'])->first();
        $city_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
        $city_obj->state_id = isset($this->input['state_id']) ? $this->input['state_id'] : "";
        $city_obj->state_name = $state->name;
        $city_obj->country_id = $country->id;
        $city_obj->country_name = $country->name;
        if($city_obj->save()){
          try{
            dispatch(new UpdateCityEverywhere($city_obj));
					 // Log::info("job completed: UpdateCityEverywhere with data" . serialize($city_obj));
          }
          catch (Exception $ex){
            Log::error($ex);
          }
          return response()->json(["status" => "1", "message" => "City added successfully."]);
        }
        else{
          return response()->json(["status" => "0", "message" => "Couldn't add the city."]);
        }        
      }
      else{
        $city_obj = new City;
        $city_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
        $city_obj->state_id = isset($this->input['state_id']) ? $this->input['state_id'] : "";
        $city_obj->state_name = $state->name;
        $city_obj->country_id = $country->id;
        $city_obj->country_name = $country->name;
        $city_obj->id = uniqid();
        if($city_obj->save()){
          return response()->json(["status" => "1", "message" => "City added successfully."]);
        }
        else{
          return response()->json(["status" => "0", "message" => "Couldn't add the city."]);
        }
      }
    }
  }

  public function deleteCity($city_id){
    $city_to_del = City::where('id', '=', $city_id)->first();
    // check if it has areas
    $areas_in_city_in_question = Area::where('city_id', '=', $city_id)->get()->toArray();
    if(count($areas_in_city_in_question) > 0){
      return response()->json(["status" => "0", "message" => "Failed to delete city. City has area(s) associated with it."]);
    }
    else{
      $success = $city_to_del->delete();
      if($success){
        return response()->json(["status" => "1", "message" => "City deleted successfully."]);
      }
      else{
        return response()->json(["status" => "1", "message" => "An error occured while deleting the city."]);
      }
    }
  }

  public function addArea(){
    if(isset($this->input['id'])){
      $area_obj = Area::where('id', '=', $this->input['id'])->first();
      $city_id = isset($this->input['city_id']) ? $this->input['city_id'] : $area_obj->city_id;
      $city = City::where('id', '=', $city_id)->first();
      $state = State::where('id', '=', $city->state_id)->first();
      $country = Country::where('id', '=', $state->country_id)->first();
      $area_obj->name = isset($this->input['name']) ? $this->input['name'] : $area_obj->name;
      $area_obj->city_id = isset($this->input['city_id']) ? $this->input['city_id'] : $area_obj->city_id;
      $area_obj->city_name = $city->name;
      $area_obj->state_id = $state->id;
      $area_obj->state_name = $state->name;
      $area_obj->country_id = $country->id;
      $area_obj->country_name = $country->name;
      $area_obj->lat = isset($this->input['lat']) ? $this->input['lat'] : $area_obj->lat;
      $area_obj->lng = isset($this->input['lng']) ? $this->input['lng'] : $area_obj->lng;
      $area_obj->pincode = isset($this->input['pincode']) ? $this->input['pincode'] : $area_obj->pincode;
      if($area_obj->save()){
        try{
          dispatch(new UpdateAreaEverywhere($area_obj));
         // Log::info("job completed: UpdateAreaEverywhere with data" . serialize($area_obj));
        }
        catch (Exception $ex){
          //Log::error($ex);
        }
        // Update data to elasticsearch :: Pankaj 19 Oct 2021
        $get_data = Area::where('id', '=', $area_obj->id)->first();
        $this->es_etl($get_data, "update");
        return response()->json(["status" => "1", "message" => "Area updated successfully."]);
      }
      else{
        return response()->json(["status" => "0", "message" => "Couldn't update the area."]);
      }
    }
    else{
      $this->validate($this->request, 
        [
          'name' => 'required',
          'city_id' => 'required',
          'lat' => 'required',
          'lng' => 'required'
        ],
        [
          'name.required' => 'Area name is required',
          'city_id.required' => 'City id is required',
          'lat.required' => 'Latitude is required',
          'lng.required' => 'Longitude is required'
        ]
      );
      $city = City::where('id', '=', $this->input['city_id'])->first();
      if(empty($city)){
        return response()->json(["status" => "0", "message" => "No city found by given id."]);
      }
      else{
        $state = State::where('id', '=', $city->state_id)->first();
        $country = Country::where('id', '=', $state->country_id)->first();
        $area_obj = new Area;
        $area_obj->name = isset($this->input['name']) ? $this->input['name'] : "";
        $area_obj->city_id = isset($this->input['city_id']) ? $this->input['city_id'] : "";
        $area_obj->city_name = $city->name;
        $area_obj->state_id = $state->id;
        $area_obj->state_name = $state->name;
        $area_obj->country_id = $country->id;
        $area_obj->country_name = $country->name;
        $area_obj->lat = isset($this->input['lat']) ? $this->input['lat'] : "";
        $area_obj->lng = isset($this->input['lng']) ? $this->input['lng'] : "";
        $area_obj->pincode = isset($this->input['pincode']) ? $this->input['pincode'] : "";
        $area_obj->id = uniqid();
        if($area_obj->save()){
          // Insert data to elasticsearch :: Pankaj 19 Oct 2021
          $get_data = Area::where('id', '=', $area_obj->id)->first();
          $this->es_etl($get_data, "insert");
          return response()->json(["status" => "1", "message" => "Area added successfully."]);
        }
        else{
          return response()->json(["status" => "0", "message" => "Couldn't add the area."]);
        }
      }
    }
  }


  public function es_etl($get_data, $opr){
    $url_insert = env('ES_SERVER_URL_INSERT');
    $url_delete = env('ES_SERVER_URL_DELETE');

    $index = env('ES_AREAS');   
    $id = $get_data->id;

    if ( $opr == "delete" ) {
      $data_string = array(
          "index" => $index,
          "data" => array (
              array (
                  "id" => $id
              )
          )
      );
      $data = json_encode($data_string);
      $ch = curl_init( $url_delete );
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      $result = curl_exec($ch);
      curl_close($ch);
    } else {     

      if ( $opr == "update" ) {
        $data_string = array(
            "index" => $index,
            "data" => array (
                array (
                    "id" => $id
                )
            )
        );
        $data = json_encode($data_string);
        $ch = curl_init( $url_delete );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
      }

      $updated_at = $get_data->updated_at;
      $d_updated_at = date("Y-m-d", strtotime($updated_at));
      $t_updated_at = date("H:i:s", strtotime($updated_at));
      $new_updated_at = $d_updated_at."T".$t_updated_at.".000Z";

      $created_at = $get_data->created_at;
      $d_created_at = date("Y-m-d", strtotime($created_at));
      $t_created_at = date("H:i:s", strtotime($created_at));
      $new_created_at = $d_created_at."T".$t_created_at.".000Z";
      
      $data_string = array(
          "index" => $index,
          "data" => array (
              array (
                  "id" => $get_data->id,
                  "name" => $get_data->name,
                  "city_name" => $get_data->city_name,
                  "state_name" => $get_data->state_name,
                  "country_name" => $get_data->country_name,
                  "lat" => $get_data->lat,
                  "lng" => $get_data->lng,
                  "pincode" => $get_data->pincode,
                  "updated_at" => $new_updated_at,
                  "created_at" => $new_created_at
                )
          )
      );
      $data = json_encode($data_string);
      $ch = curl_init( $url_insert );
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      $result = curl_exec($ch);
      curl_close($ch); 
    }
  }

  public function deleteArea($area_id){
    $area_to_del = Area::where('id', '=', $area_id)->first();
    // check if it has products
    $products_in_area_in_question = Product::where('area_id', '=', $area_id)->get()->toArray();
    if(count($products_in_area_in_question) > 0){
      return response()->json(["status" => "0", "message" => "Failed to delete area. Area has products in it."]);
    }
    else{
      // Delete data to elasticsearch :: Pankaj 19 Oct 2021
      $this->es_etl($area_to_del, "delete");
      $success = $area_to_del->delete();
      if($success){
        return response()->json(["status" => "1", "message" => "Area deleted successfully."]);
      }
      else{
        return response()->json(["status" => "1", "message" => "An error occured while deleting the area."]);
      }
    }
  }

  public function autoCompleteArea($search_term){
    $area_list = Area::where('city_name', 'like', "$search_term%")->get();  
    return response()->json($area_list);
  }

  public function searchAreas($search_term){
    $word = strtolower($search_term);
		$areas = Area::where('name', 'like', "%$word%")
			->orWhere('country_name', 'like', "%$word%")
			->orWhere('state_name', 'like', "%$word%")
			->orWhere('city_name', 'like', "%$word%")
			->orWhere('pincode', 'like', "%$word%")
			->get();
		return response()->json($areas);
  }

  public function searchLoc($search_term){
    $word = strtolower($search_term);
		$areas = Area::where('name', 'like', "%$word%")
			->orWhere('country_name', 'like', "%$word%")
			->orWhere('state_name', 'like', "%$word%")
			->orWhere('city_name', 'like', "%$word%")
			->orWhere('pincode', 'like', "%$word%")
			->get();
		return response()->json($areas);
  }

  
  public function searchCities($search_term){
    $word = strtolower($search_term);
		$cities = City::where('name', 'like', "%$word%")
      ->orWhere('state_name', 'like', "%$word%")
      ->orWhere('id', 'like', "%$word%")
			->get();
		return response()->json($cities);
  }
}
