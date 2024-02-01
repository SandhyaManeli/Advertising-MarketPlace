<?php
//date_default_timezone_set('America/Los_Angeles');
/*===========================
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
===========================*/ 

$app->get('/', function () use ($app){
    return view('index');
});

$app->get('/info', function () use ($app){
    return view('info');
});

$app->group(['prefix' => 'api'], function () use ($app){

    /*=========================== 
    |   Location 
    ===========================*/
    $app->get('countries', ['uses' => 'LocationController@getCountries']);
    $app->get('states[/{country_id}]', ['uses' => 'LocationController@getStates']);
    $app->get('cities/{state_ids}', ['uses' => 'LocationController@getCities']);
    $app->get('allCities', ['uses' => 'LocationController@getAllCities']);
    $app->get('areas/{city_ids}', ['uses' => 'LocationController@getAreas']);
    $app->get('allAreas', ['uses' => 'LocationController@getAllAreas']);
    $app->post('country', ['uses' => 'LocationController@addCountry']);
    $app->delete('country/{country_id}', ['uses' => 'LocationController@deleteCountry']);    
    $app->post('state', ['uses' => 'LocationController@addState']);
    $app->delete('state/{state_id}', ['uses' => 'LocationController@deleteState']);
    $app->post('city', ['uses' => 'LocationController@addCity']);
    $app->delete('city/{city_id}', ['uses' => 'LocationController@deleteCity']);
    $app->post('area', ['uses' => 'LocationController@addArea']);
    $app->delete('area/{area_id}', ['uses' => 'LocationController@deleteArea']);
    $app->get('autocomplete-area/{search_term}', ['uses' => 'LocationController@autoCompleteArea']);
    $app->get('search-areas/{search_term}', ['uses' => 'LocationController@searchAreas']);
    $app->get('search-cities/{search_term}', ['uses' => 'LocationController@searchCities']);

    /*=========================== 
    |   Products 
    ===========================*/ 
    $app->get('products', ['uses' => 'ProductController@getProducts']);
    $app->get('map-products', ['uses' => 'ProductController@getProductsForMap']);
    $app->get('map-products-filter-shortlist', ['uses' => 'ProductController@getProductsForMapfilterShortlist']);
    $app->get('product/{product_id}', ['uses' => 'ProductController@getProductDetails']);
    $app->post('product', ['uses' => 'ProductController@saveProduct']);
    $app->post('products', ['uses' => 'ProductController@saveProductsBulk']);
    $app->delete('product/{product_id}', ['uses' => 'ProductController@deleteProduct']);
    $app->post('approved-owner-products', ['uses' => 'ProductController@getApprovedOwnerProducts']);
    $app->get('bulk-upload-products', ['uses' => 'ProductController@getBulkUploadProducts']);
    $app->post('request-owner-product-addition', ['uses' => 'ProductController@requestHoardingIntoInventory']);
    $app->get('requested-hoardings', ['uses' => 'ProductController@getRequestedHoardings']);
    $app->get('requested-hoardings-for-owner', ['uses' => 'ProductController@getRequestedHoardingsForOwner']);
    $app->get('owner-products-report', ['uses' => 'ProductController@getOwnerProductsReport']);
    $app->get('owner-product-details/{product_id}', ['uses' => 'ProductController@getOwnerProductDetails']);
    $app->post('metro-corridor', ['uses' => 'ProductController@saveMetroCorridor']);
    $app->get('metro-corridors', ['uses' => 'ProductController@getMetroCorridors']);
    $app->post('metro-package', ['uses' => 'ProductController@saveMetroPackage']);
    $app->post('change-product-price', ['uses' => 'ProductController@changeProductPrice']);
    $app->post('change-campaign-product-price', ['uses' => 'ProductController@changeCampaignProductPrice']);
	$app->put('product-visibility/{product_id}', ['uses' => 'ProductController@productVisibility']);
    $app->get('metro-packages', ['uses' => 'ProductController@getMetroPackages']);
    $app->get('close-metro-campaigns/{campaign_id}', ['uses' => 'CampaignController@closeMetroCampaign']);
    $app->delete('metro-campaign-product/{campaign_id}/product/{product_id}', ['uses' => 'CampaignController@deleteMetroProductFromCampaign']);
    $app->delete('metro-campaign/{campaign_id}', ['uses' => 'CampaignController@deleteMetroCampaign']);
    $app->delete('metro-corridor/{corridor_id}', ['uses' => 'ProductController@deletMetroCorridors']);
    $app->delete('metro-package/{package_id}', ['uses' => 'ProductController@deletMetroPackage']);
    $app->get('product-unavailable-dates/{product_id}', ['uses' => 'ProductController@getProductUnavailableDates']);
    $app->get('product-unavailable-dates-no-login/{product_id}', ['uses' => 'ProductController@getProductUnavailableDatesWithoutLogin']);
    $app->get('campaigns-from-products/{product_id}', ['uses' => 'ProductController@getCampaignsFromProduct']);
    $app->post('products-price-in-campaigns', ['uses' => 'CampaignController@getProductsPriceInCampaign']);
    $app->post('clone-product-details', ['uses' => 'ProductController@cloneProductDetails']);
    $app->get('product-availabilty-quantity/{product_id}/{from_date}/{to_date}', ['uses' => 'ProductController@getProductAvailabilityQuantity']);
	
	/*=========================== 
    |   Management Report 
    ===========================*/
	
	$app->get('products-count', ['uses' => 'ProductController@getProductsCount']);
	$app->get('get-counts', ['uses' => 'CampaignController@getCounts']);
	$app->get('get-user-details', ['uses' => 'CampaignController@getUsersDetails']);
	$app->get('get-product-details', ['uses' => 'CampaignController@getProductsDetails']);
	$app->get('get-campaign-details', ['uses' => 'CampaignController@getCampaignsDetails']);
	$app->get('get-user-details-test-auth', ['uses' => 'CampaignController@getUsersDetailsTestingAuth']);
	$app->post('filter-users', ['uses' => 'CampaignController@filterUsers']);
    $app->post('filter-users-download', ['uses' => 'CampaignController@filterUsersDownload']);
    $app->get('filter-users-download1', ['uses' => 'CampaignController1@filterUsersExcelDownload1']);
	$app->post('filter-products-report', ['uses' => 'CampaignController@filterProductsReport']);
    $app->post('filter-products-report-download', ['uses' => 'CampaignController@filterProductsReportDownload']);
	$app->post('filter-campaigns-report', ['uses' => 'CampaignController@filterCampaignsReport']);
    $app->post('filter-campaigns-report-download', ['uses' => 'CampaignController@filterCampaignsReportDownload']);
	$app->post('search-user-details', ['uses' => 'ProductController@searchByUserDetails']);
	$app->post('search-campaign-details', ['uses' => 'ProductController@searchByCampaignDetails']);
	$app->post('search-product-details', ['uses' => 'ProductController@searchByProductsDetails']); 
	$app->post('users-details-filters-search', ['uses' => 'CampaignController@userDetailsFiltersSearch']); 
	$app->post('products-details-filters-search', ['uses' => 'CampaignController@productsDetailsFiltersSearch']); 
	$app->post('campaigns-details-filters-search', ['uses' => 'CampaignController@campaignDetailsFiltersSearch']); 
	$app->get('get-owner-counts', ['uses' => 'CampaignController@getOwnerCounts']);
    $app->get('get-buyer-counts', ['uses' => 'CampaignController@getBuyerCounts']);
 
    $app->post('filter-users-excel-download', ['uses' => 'CampaignController@filterUsersExcelDownload']);
    $app->post('filter-products-excel-download', ['uses' => 'CampaignController@filterProductsExcelDownload']);
    $app->post('filter-campaigns-excel-download', ['uses' => 'CampaignController@filterCampaignsExcelDownload']);

    $app->get('campaigns-buyer-export-excel-download/{campaign_id}', ['uses' => 'CampaignController@downloadCampaignBuyerExportExcel']);

    $app->get('seller-expiry-products', ['uses' => 'CampaignController1@sellerExpiryProducts']);
     
    /*===========================  
    |   Format 
    ===========================*/
    $app->get('formats', ['uses' => 'ProductController@getFormats']);
    $app->post('format', ['uses' => 'ProductController@saveFormat']);
    $app->delete('format/{format_id}', ['uses' => 'ProductController@deleteFormat']);

    /*=========================== 
    |   product filtering 
    ===========================*/
    $app->get('search-products', ['uses' => 'ProductController@getProductsSearchNPaginate']);
    $app->get('search-owner-products/{word}', ['uses' => 'ProductController@searchOwnerProductsByQuery']);
    $app->get('search-products/{word}', ['uses' => 'ProductController@searchProductsByQuery']);
    $app->post('filterProducts', ['uses' => 'ProductController@filterProducts']);
    
    $app->post('filterProductsByDate', ['uses' => 'ProductController@filterProductsByDate']);
    /*=========================== 
    |  Shortlisting, ordering products 
    ===========================*/
    $app->get('shortlistedProducts', ['uses' => 'ProductController@getShortlistedProducts']);
    $app->post('shortlistProduct', ['uses' => 'ProductController@shortListProduct']);
    $app->post('bulk-shortlist-product', ['uses' => 'ProductController@bulkShortListProductForOwner']);
    $app->post('notifyUsershortlistedProduct', ['uses' => 'ProductController@notifyUserShortlistedProduct']);
    $app->delete('shortlistedProduct/{shortlist_id}', ['uses' => 'ProductController@deleteShortlistedProduct']); 
    $app->post('deleteshortlistedProducts', ['uses' => 'ProductController@deleteShortlistedProducts']); 
    $app->get('searchBySiteNo/{site_no}', ['uses' => 'ProductController@searchProductBySiteNo']);
    $app->get('searchByCpm/{cpm}', ['uses' => 'ProductController@searchProductByCpm']);
    $app->get('searchBySecondImpression/{secondImpression}', ['uses' => 'ProductController@searchProductBySecondImpression']);
    $app->post('share-shortlisted', ['uses' => 'ProductController@shareShortlistedProducts']);
    $app->post('shortlist-metro-package', ['uses' => 'ProductController@shortlistMetroPackage']);
    $app->get('shortlisted-metro-packages', ['uses' => 'ProductController@getShortlistedMetroPackages']);
    $app->delete('shortlisted-metro-package/{package_id}', ['uses' => 'ProductController@deleteShortlistedMetroPackage']);
	$app->get('user-cart-count', ['uses' => 'ProductController@getCartCount']);

    
    /*=========================== 
    |  Authentication 
    ===========================*/ 
    $app->post('login', ['uses' => 'UserController@login']);
    $app->get('logout', ['uses' => 'UserController@logout']);
    $app->get('search-loc/{search_term}', ['uses' => 'LocationController@searchLoc']);

    /*=========================== 
    |   Users 
    ===========================*/
    $app->post('userByAdmin', ['uses' => 'UserController@addUser']);
    $app->post('user', ['uses' => 'UserController@register']);
    $app->get('verify-email/{verification_code}', ['uses' => 'UserController@verifyEmail']);
    $app->get('user-profile', ['uses' => 'UserController@getUserProfile']);
    $app->get('users-count', ['uses' => 'UserController@getUserCount']);
    $app->post('request-reset-password', ['uses' => 'UserController@sendResetPasswordLink']);
    // resets forgotten password
    $app->post('reset-password', ['uses' => 'UserController@resetPassword']);
    $app->get('activate-user/{user_mongo_id}', ['uses' => 'UserController@activateUser']);
    $app->get('user-permissions/{user_mongo_id}', ['uses' => 'UserController@getUserPermissions']);
    // changes the current password(current password is required)
    $app->post('change-password', ['uses' => 'UserController@changePassword']);
    $app->get('switch-activation-user/{user_mongo_id}', ['uses' => 'UserController@toggleActivationForUser']);
    $app->get('delete-user/{user_mongo_id}', ['uses' => 'UserController@deleteUser']);
    $app->post('update-profile-pic', ['uses' => 'UserController@changeUserAvatar']);
    $app->post('complete-registration', ['uses' => 'UserController@completeRegistration']);
	$app->post('generate-random-password', ['uses' => 'UserController@generateRandomPassword']);
	$app->get('reset-password-link/{verification_code}', ['uses' => 'UserController@getResetPasswordLink']);
	$app->post('update-user-profile', ['uses' => 'UserController@updateUserProfile']);

    /*===========================
    | User Management
    ===========================*/
    $app->get('system-roles', ['uses' => 'UserController@getSystemRoles']);
    $app->get('system-permissions', ['uses' => 'UserController@getSystemPermissions']);
    $app->get('users', ['uses' => 'UserController@getAllUsers']);
    $app->get('role-details/{role_id}', ['uses' => 'UserController@getRoleDetails']);
    $app->get('all-clients', ['uses' => 'CompanyController@getAllClients']);
    $app->post('role', ['uses' => 'UserController@addRole']);
    $app->get('user-details-with-roles/{user_mongo_id}',  ['uses' => 'UserController@getUserDetailsWithRoles']);
    $app->post('set-su-for-client', ['uses' => 'UserController@setSuperAdminForClient']);
    $app->post('set-permissions-for-role', ['uses' => 'UserController@setPermissionsForRole']);
    $app->post('set-roles-for-user', ['uses' => 'UserController@setRolesForUser']);
    $app->post('invite-bbi-user', ['uses' => 'UserController@sendInviteToBBIUser']);
    $app->get('user-single-record/{user_id}', ['uses' => 'UserController@userSingleRecord']);

    /*=========================== 
    |    Agency 
    ===========================*/
    $app->get('agencies', ['uses' => 'AgencyController@getAllAgencies']);
    $app->post('agencyByAdmin', ['uses' => 'AgencyController@addAgency']);

    /*=========================== 
    |  Companies 
    ===========================*/
    $app->get('companies', ['uses' => 'CompanyController@getCompanies']);
    $app->get('client-types', ['uses' => 'CompanyController@getClientTypes']);
    $app->post('client', ['uses' => 'CompanyController@registerClient']);
    //$app->post('subseller', ['uses' => 'CompanyController@registerSubSeller']);
    $app->post('company', ['uses' => 'CompanyController@addCompany']);
    $app->get('check-pwd-generation/{verification_code}', ['uses' => 'CompanyController@pwdGenerationCheck']);
    $app->post('resend-owner-invite', ['uses' => 'CompanyController@resendOwnerInviteEmail']);

    /*=========================== 
    |  Campaigns 
    ===========================*/
        
    $app->get('campaigns', ['uses' => 'CampaignController@getCampaigns']);
    $app->get('user-campaigns', ['uses' => 'CampaignController@getCampaigns']);
	$app->post('update-campaign', ['uses' => 'CampaignController@updateCampaign']);
 
    /* ========================= User specific campaign routes ======================= */
    $app->get('active-user-campaigns', ['uses' => 'CampaignController@getActiveUserCampaigns']);
    $app->post('user-campaign', ['uses' => 'CampaignController@saveUserCampaign']);
    $app->post('rfp-campaign-without-login', ['uses' => 'CampaignController@saveRFPCampaignWithoutLogin']);
    $app->post('rfp-user-campaign', ['uses' => 'CampaignController@saveRFPUserCampaign']);
    $app->get('rfp-campaign-records', ['uses' => 'CampaignController@getRFPRecords']);
    // adds a product into campaign as it is. | by user 
    $app->post('product-to-campaign', ['uses' => 'CampaignController@addProductToCampaign']);
    $app->post('suggestion-request', ['uses' => 'CampaignController@saveSuggestionRequest']);
    $app->get('export-all-campaigns', ['uses' => 'CampaignController@exportAllCampaigns']);
    $app->get('request-proposal/{campaign_id}', ['uses' => 'CampaignController@requestCampaignProposal']);
    $app->get('request-campaign-booking/{campaign_id}', ['uses' => 'CampaignController@requestCampaignBooking']);
    $app->post('request-quote-change', ['uses' => 'CampaignController@requestChangeQuote']);
    // this route points to deleteProductFromCampaign() same as it's copy also under UAO section.  
    // need to keep it as if there's only one (either user-campaign or campaign in route), 
    // it'll give the same permission for deleting campaign also. And we wouldn't be able to control 
    // access to type of campaigns.
    //$app->delete('user-campaign/{campaign_id}/product/{product_id}', ['uses' => 'CampaignController@deleteProductFromCampaign']);
    $app->delete('user-campaign/{campaign_id}/product/{product_id}/price/{price}', ['uses' => 'CampaignController@deleteProductFromCampaign']);
    $app->post('metro-campaign', ['uses' => 'CampaignController@saveMetroCampaign']);
    $app->get('metro-campaigns', ['uses' => 'CampaignController@getMetroCampaigns']);
    $app->get('checkout-metro-campaign/{metro_campaign_id}/{flag}/{gst}', ['uses' => 'CampaignController@checkoutMetroCampaign']);
    $app->get('metro-campaign/{metro_camp_id}', ['uses' => 'CampaignController@getMetroCampaignDetails']);   
    $app->post('update-metro-campaigns-status', ['uses' => 'CampaignController@updateMetroCampaignStatus']);
    $app->get('launch-metro-campaign/{metro_campaign_id}', ['uses' => 'CampaignController@launchMetroCampaign']);
    $app->get('launch-campaign/{campaign_id}', ['uses' => 'CampaignController@launchCampaign']);
    $app->get('get-offers', ['uses' => 'CampaignController@getAllOffers']);
    $app->post('rfp-campaign/{campaign_id}', ['uses' => 'CampaignController@RFPCampaign']);
    //$app->post('offer-price/{campaign_id}', ['uses' => 'CampaignController@offerForPrice']);
    $app->post('offer-price', ['uses' => 'CampaignController@offerForPrice']);
	$app->get('user-offer/{campaign_id}', ['uses' => 'CampaignController@getOfferDetails']);
	$app->post('accept-reject-offer', ['uses' => 'CampaignController@acceptRejectOffer']);
	$app->get('offers-count', ['uses' => 'CampaignController@getOffersCount']); 
    $app->post('find-for-me', ['uses' => 'CampaignController@findForMe']);
    $app->get('find-for-me-list', ['uses' => 'CampaignController@getFinForMe']); 
    $app->get('find-for-me-count', ['uses' => 'CampaignController@getFinForMeCount']);
    $app->post('cancel-campaign', ['uses' => 'CampaignController@requestForCancelCampaign']);
	$app->get('get-requested-campaigns', ['uses' => 'CampaignController@getRequestedCampaigns']);
	$app->post('request-delete-product-from-campaign', ['uses' => 'CampaignController@requestForDeleteProductFromCampaign']);
	$app->get('delete-products-list-from-campaign', ['uses' => 'CampaignController@getDeleteRequestedProductsFromCampaign']);
	$app->get('get-product-status-in-campaign/{campaign_id}', ['uses' => 'CampaignController@getProductStatusInCampaign']);
  
    /* ========================= User specific campaign routes end ======================= */
     
    /* ========================= Admin specific campaign routes ======================== */
    $app->get('get-all-campaigns', ['uses' => 'CampaignController@getAllCampaignsForAdmin']);
    $app->get('get-admin-campaigns', ['uses' => 'CampaignController@getAdminCampaigns']);
    $app->get('all-campaign-requests', ['uses' => 'CampaignController@getAllCampaignRequests']);
    $app->get('campaign-suggestion-request-details/{campaign_id}', ['uses' => 'CampaignController@campaignSuggestionDetails']);
    $app->get('close-campaign/{campaign_id}', ['uses' => 'CampaignController@closeCampaign']);
    $app->post('floating-campaign-pdf', ['uses' => 'CampaignController@floatingCampaignPdf']);
    $app->get('campaign-payments/{campaign_id}', ['uses' => 'CampaignController@getCampaignPayments']);
    $app->post('campaign-payment', ['uses' => 'CampaignController@updateCampaignPayment']);
    $app->get('quote-change-request-history/{campaign_id}', ['uses' => 'CampaignController@getQuoteChangeHistory']);
    $app->post('package-to-metro-campaign', ['uses' => 'CampaignController@addPackageToMetroCampaign']);
    /* ========================= Admin specific campaign routes end ======================== */
    
    /* ======================= Owner Campaign routes ========================== */
    $app->get('owner-campaigns', ['uses' => 'CampaignController@getOwnerCampaigns']);
    $app->get('user-campaigns-for-owner', ['uses' => 'CampaignController@getUserCampaignsForOwner']);
    $app->get('campaign-for-owner/{campaign_id}', ['uses' => 'CampaignController@getCampaignDetailsForOwner']);
    $app->get('campaigns-with-payments-owner', ['uses' => 'CampaignController@getCampaignWithPaymentsForOwner']);
        $app->get('campaign-payment-details-owner/{campaign_id}', ['uses' => 'CampaignController@getCampaignPaymentDetailsForOwner']);
    $app->post('update-campaign-payment-owner', ['uses' => 'CampaignController@updateCampaignPaymentByOwner']);
    $app->get('owner-feeds', ['uses' => 'CampaignController@getOwnerFeeds']);
    /* ======================= Owner Campaign routes end ========================== */

      /*========================================
     * -Comments Section for Campaign
     */
      $app->post('post-campaign-comment', ['uses' => 'ProductController@commentPost']);
      $app->post('get-campaign-comment', ['uses' => 'ProductController@CommentsView']);
    
    /*-------------------Comments Section for Campaign End-----------------*/
    /* =================== U A ================== */
        $app->get('user-campaign/{campaign_id}', ['uses' => 'CampaignController@getCampaignDetails']);
        $app->get('user-campaign-rfp/{campaign_id}', ['uses' => 'CampaignController@getRFPCampaignDetails']);
        $app->get('rfp-campaign-no-login/{campaign_id}', ['uses' => 'CampaignController@getRFPwithoutloginCampaignDetails']);
    // adds a product in campaign with from/to date and price | by admin/owner | suggest-product page
    $app->post('propose-product-for-campaign', ['uses' => 'CampaignController@proposeProductForCampaign']);
    $app->delete('campaign/{campaign_id}', ['uses' => 'CampaignController@deleteCampaign']);
    $app->post('share-metro-campaign', ['uses' => 'CampaignController@shareMetroCampaign']);
    /* =================== U A ends ================== */

    /* =================== O A ================== */
    $app->get('quote-campaign/{campaign_id}/{flag}/{gst}', ['uses' => 'CampaignController@quoteCampaign']);
    $app->get('confirm-campaign-booking/{campaign_id}', ['uses' => 'CampaignController@confirmCampaignBooking']);
    $app->get('book-non-user-campaign/{campaign_id}', ['uses' => 'CampaignController@bookNonUserCampaign']);
	//$app->get('book-non-user-campaign/{campaign_id}/{flag}/{gst}', ['uses' => 'CampaignController@bookNonUserCampaign']);
    $app->post('non-user-campaign', ['uses' => 'CampaignController@saveNonUserCampaign']);
    $app->put('proposed-product-for-campaign/{campaign_id}', ['uses' => 'CampaignController@editProposedProductForCampaign']);
    $app->delete('non-user-campaign/{campaign_id}', ['uses' => 'CampaignController@deleteNonUserCampaign']);
    $app->delete('delete-campaign/{campaign_id}', ['uses' => 'CampaignController@deleteUserCampaign']);
    $app->delete('delete-admin-owner-campaign/{campaign_id}', ['uses' => 'CampaignController@deleteAdminOwnerCampaign']);
    /* =================== O A ends ================== */
    
    /* =================== U A O ================ */
    $app->delete('campaign/{campaign_id}/product/{product_id}', ['uses' => 'CampaignController@deleteProductFromCampaign']);
    $app->get('non-user-campaign/{campaign_id}', ['uses' => 'CampaignController@getNonUserCampaignDetails']);
    $app->post('share-campaign', ['uses' => 'CampaignController@shareCampaign']);
	$app->post('shareCampaigndownloadQuote', ['uses' => 'CampaignController@shareCampaigndownloadQuote']);
    $app->get('search-campaigns/{searchTerm}', ['uses' => 'CampaignController@searchCampaigns']);
    /* =================== U A O ends ================ */



    /*===========================
    |   Notifications
    ===========================*/
    $app->get('all-notifications/last-notif/{last_notif_timestamp}', ['uses' => 'NotificationController@getAllNotifications']);
    $app->get('all-admin-notifications/last-notif/{last_notif_timestamp}', ['uses' => 'NotificationController@getAllAdminNotifications']);
    $app->get('all-owner-notifications/last-notif/{last_notif_timestamp}', ['uses' => 'NotificationController@getAllOwnerNotifications']);
    $app->get('update-notification-read/{notification_id}', ['uses' => 'NotificationController@changeNotificationStatusToRead']);

    /*=========================== 
    |  Customer Support 
    ===========================*/
    $app->post('subscription', ['uses' => 'CustomerSupportController@createSubscription']);
    $app->post('request-callback', ['uses' => 'CustomerSupportController@requestCallback']);
    $app->post('user-query', ['uses' => 'CustomerSupportController@userQuery']);
    $app->put('update-customer-data/{updateID}', ['uses' => 'CustomerSupportController@update_customer_data']);
    $app->get('customer-query/{type}', ['uses' => 'CustomerSupportController@customer_query']);
    $app->get('customer-query-count', ['uses' => 'CustomerSupportController@getCustomerQueryCount']);

    /*=========================== 
    |   Mails 
    ===========================*/ 
    $app->get('test-mail', ['uses' => 'MailController@testMail']);

    /*==================================================
    |   ----Test Routes----
    ==================================================*/
    $app->get('loginRequired', 'RbacTestController@loginRequired');
    $app->get('isOwner', 'RbacTestController@isOwner');
    $app->get('isAdmin', 'RbacTestController@isAdmin');
    $app->get('isAdminOrOwner', 'RbacTestController@isAdminOrOwner');
    $app->get('test-pdf', 'RbacTestController@generatePdf');
    $app->get('test-view/{view_name}', ['uses' => 'RbacTestController@loadTestView']);

    $app->get('get-notifications', 'NotificationController@getNotifications');
	
	$app->get('update-notification-status/{notificationid}', 'NotificationController@updateNotificationstatus');
	
	$app->post('update-notifications-status', 'NotificationController@updateNotificationsStatus');
	
	$app->get('cancel-campaign-product/{campaign_id}/product/{product_id}', ['uses' => 'CampaignController@cancelProductFromCampaign']);
	
	$app->get('download-quote/{campaign_id}', ['uses' => 'CampaignController@downloadCampaignQuote']);
	
    $app->get('download-metro-quote/{campaign_id}', ['uses' => 'CampaignController@downloadMetroCampaignQuote']);
     
     $app->get('test-noti', function () {
            event(new App\Events\CampaignLaunchEvent('Someone'));
            return "Event has been sent!";
        });
		
		
//APIs For Product Adding.
 $app->post('save-product-details', ['uses' => 'ProductController@saveProductDetails']);	
 
 $app->post('save-bulk-product-details', ['uses' => 'ProductController@addBulkUpload']);	
 
 $app->post('pay-launch-campaign', ['uses' => 'CampaignController@payAndLaunchCampaign']);	
 
 $app->post('digital-product-unavailable-dates',['uses' =>'ProductController@getDigitalProductUnavailableDates']);	
 
 $app->get('generate-pop/{campaign_id}', ['uses' => 'CampaignController@generatePop']);
 
  $app->get('payments-info-download/{campaign_id}', ['uses' => 'CampaignController@paymentsinfoDownload']);
  
    $app->get('delete-soldout-product-campaign/{campaign_id}/{product_id}', ['uses' => 'CampaignController@getDeleteSoldoutProductCampaign']);
  
  //For Sub Seller
   $app->post('subseller', ['uses' => 'SubSellerController@registerSubSeller']);
   $app->post('save-subseller-details', ['uses' => 'SubSellerController@addSubSeller']);
   $app->post('invite-sub-seller', ['uses' => 'SubSellerController@sendInviteToSubSeller']);
   $app->get('get-subseller-details', ['uses' => 'SubSellerController@getSubSellerDetails']);
   $app->delete('delete-subseller/{subseller_id}', ['uses' => 'SubSellerController@deleteSubSeller']);
   $app->post('subseller-generate-password', ['uses' => 'SubSellerController@subsellerGeneratePassword']);
   
   
   /*===========================
    |   Payment APIS
    ===========================*/ 
   $app->post('stripePost', ['uses' => 'StripePaymentController@stripePayment']);
   $app->post('stripeRefund', ['uses' => 'StripePaymentController@stripeRefund']);
   $app->post('stripeRefund-for-delete-product', ['uses' => 'StripePaymentController@stripeRefundForDeletedProduct']);

   $app->get('test_elastic', ['uses' => 'TestElasticController@get_data']);
   $app->get('test_elastic1', ['uses' => 'TestElasticController@get_data1']);
});
