<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class CampaignSuggestionRequest extends Moloquent{
  
  protected $connection = 'content';

  /**
  * ============
  * | Fields
  * ============
  * 
  *  "_id" : ,
  *  "campaign_id" : "",
  *  "org_name" : "",
  *  "product" : "",
  *  "product_desc" : "",
  *  "updated_at" : ,
  *  "created_at" : ,
  *  "market_reach" : [
  *          {
  *                  "solution_to_problem" : 
  *          }
  *  ],
  *  "adv_objective" : [
  *          {
  *                  "new_product" : 
  *          }
  *  ],
  *  "medium" : [
  *          {
  *                  "unipoles" : ,
  *                  "retail" : ,
  *                  "center_mediums" : 
  *          }
  *  ],
  *  "target_audience" : [
  *          {
  *                  "it_people" : ,
  *                  "students" : ,
  *                  "manufacturers" : ,
  *                  "real_state" : 
  *          }
  *  ],
  *  "gender_group" : [
  *          {
  *                  "male" : ,
  *                  "other" : 
  *          }
  *  ],
  *  "age_group" : [
  *          {
  *                  "four_ten" : ,
  *                  "fourty_above" : 
  *          }
  *  ]
  * 
  */

}