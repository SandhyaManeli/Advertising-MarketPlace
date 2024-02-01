<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class StripePayment extends Moloquent{
  
  /* 
  * StripePayment fields
  * _id
  * ch_id
  * object
  * amount
  * amount_refunded 
  * application_fee
  * application_fee_amount
  * address
  * city
  * country
  * line1
  * line2
  * postal_code
  * state
  * email
  * name
  * phone
  * calculated_statement_descriptor
  * created
  * currency
  * customer
  * description
  * payment_intent
  * payment_method
  * card
  * brand
  * address_line1_check
  * address_postal_code_check
  * cvc_check
  * country
  * exp_month
  * exp_year
  * fingerprint
  * funding
  * installments
  * last4
  * network
  * three_d_secure
  * wallet
  * type
  * receipt_email
  * receipt_number
  * receipt_url
  * refunded
  * has_more
  * total_count
  * url
  */

  protected $connection = 'content';
  protected $collection = 'stripe_payments';
  
}