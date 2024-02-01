"use strict";

// Declare app level module which depends on views, and components
var app = angular
	.module("bbManager", [
		"ngRoute",
		"ui.router",
		"ngMap",
		"ngMaterial",
		"ngMessages",
		"slickCarousel",
		"vsGoogleAutocomplete",
		// 'ui.bootstrap',
		"ngFileUpload",
		"satellizer",
		"toastr",
		"ui.grid",
		"ui.grid.edit",
		"ui.grid.pagination",
		"ngFileSaver",
		"googlechart",
		"ui.grid.selection",
		"daterangepicker",
		"angular-inview",
		"ngSanitize",
		"oc.lazyLoad",
	])
	.config([
		"$locationProvider",
		"$urlRouterProvider",
		"$mdThemingProvider",
		"$mdAriaProvider",
		"$authProvider",
		"$stateProvider",
		"$httpProvider",
		"config",
		function (
			$locationProvider,
			$urlRouterProvider,
			$mdThemingProvider,
			$mdAriaProvider,
			$authProvider,
			$stateProvider,
			$httpProvider,
			config
		) {
			$mdThemingProvider
				.theme("default")
				.primaryPalette("red", {
					default: "800",
					"hue-1": "500",
					"hue-2": "700",
				})
				.accentPalette("orange", {
					default: "800",
					"hue-1": "600",
					"hue-2": "400",
				});

			$locationProvider.html5Mode(true);

			$stateProvider
				.state("index", {
					abstract: true,
					url: "/",
					templateUrl: `layouts/default.html?v=${Date.now()}`,
					controller: "bbMngrCtrl",
					resolve: {
						contactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/ContactService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						locationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/LocationService.js"
								);
							},
						],
						notificationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/NotificationService.js"
								);
							},
						],
						bbMngrCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/BbMngrCtrl.js"
								);
							},
						],
					},
				})
				.state("index.home", {
					url: "home",
					templateUrl: `views/home.html?v=${Date.now()}`,
				})
				.state("index.register", {
					url: "register",
					templateUrl: `views/flyer-register.html?v=${Date.now()}`,
				})
				.state("index.aboutbbi", {
					url: "about",
					templateUrl: `views/about_bbi.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				// For the Save Product API documentation static page //
                .state("index.saveProductAPI", {
                    url: "api-documentation",
                    templateUrl: `views/save-product-api.html?v=${Date.now()}`,
                    controller: "linkCtrl",
                    resolve: {
                        js: [
                            "$ocLazyLoad",
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    "./controllers/linkCtrl.js"
                                );
                            },
                        ],
                    },
                })
				// For the Update Product API documentation static page //
                .state("index.updateProductAPI", {
                    url: "update-product-api",
                    templateUrl: `views/update-product-api.html?v=${Date.now()}`,
                    controller: "linkCtrl",
                    resolve: {
                        js: [
                            "$ocLazyLoad",
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    "./controllers/linkCtrl.js"
                                );
                            },
                        ],
                    },
                })
				// For the Clone Product API documentation static page //
                .state("index.cloneProductAPI", {
                    url: "clone-product-api",
                    templateUrl: `views/clone-product-api.html?v=${Date.now()}`,
                    controller: "linkCtrl",
                    resolve: {
                        js: [
                            "$ocLazyLoad",
                            function ($ocLazyLoad) {
                                return $ocLazyLoad.load(
                                    "./controllers/linkCtrl.js"
                                );
                            },
                        ],
                    },
                })
				.state("index.policy", {
					url: "policy",
					templateUrl: `views/policy_AMP.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("index.ourproduct", {
					url: "ourproduct",
					templateUrl: `views/our_product.html?v=${Date.now()}`,
				})
				.state("index.faq", {
					url: "faq",
					templateUrl: `views/faq_product.html?v=${Date.now()}`,
				})
				.state("index.listing_policies", {
					url: "listing_policies",
					templateUrl: `views/listing_policies.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("index.member_behavior", {
					url: "member_behavior",
					templateUrl: `views/member_behavior.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("index.privacy_notice", {
					url: "privacy_notice",
					templateUrl: `views/privacy_notice.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("index.prohibited_restricted", {
					url: "prohibited_restricted",
					templateUrl: `views/prohibited_restricted.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("index.rules_buyers", {
					url: "rules_buyers",
					templateUrl: `views/rules_buyers.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("index.user_agreement", {
					url: "user_agreement",
					templateUrl: `views/user_agreement.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("index.suggestme", {
					url: "suggestme",
					templateUrl: `views/suggest_me.html?v=${Date.now()}`,
				})
				.state("index.ourteam", {
					url: "ourteam",
					templateUrl: `views/our_team.html?v=${Date.now()}`,
				})
				.state("index.fullservices", {
					url: "fullservices",
					templateUrl: `views/full_services.html?v=${Date.now()}`,
				})
				.state("index.joincareers", {
					url: "joincareers",
					templateUrl: `views/join_careers.html?v=${Date.now()}`,
				})
				.state("index.formats", {
					url: "formats",
					templateUrl: `views/formats.html?v=${Date.now()}`,
					controller: "ProductCtrl",
					resolve: {
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminLocationService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						companyService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CompanyService.js"
								);
							},
						],
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/ProductCtrl.js"
								);
							},
						],
					},
				})
				.state("index.user-notifications", {
					url: "user-notifications",
					templateUrl: `views/user-notifications.html?v=${Date.now()}`,
				})
				.state("index.user-saved-campaigns", {
					url: "user-saved-campaigns",
					templateUrl: `views/user-saved-campaigns.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				});
			$stateProvider
				.state("index.suggest", {
					url: "suggest",
					templateUrl: `views/suggest-a-campaign.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				})
				// nested states
				// each of these sections will have their own view
				// url will be suggest-Product-Detail
				.state("index.suggest.product-detail", {
					url: "/product-detail",
					templateUrl: `views/suggest-campaign-one.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				})
				// url will be suggest-market-Detail
				.state("index.suggest.marketing-objectives", {
					url: "/marketing-objectives",
					templateUrl: `views/suggest-campaign-two.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				})
				// url will be suggest-Advertising-Detail
				.state("index.suggest.advertising-objectives", {
					url: "/advertising-objectives",
					templateUrl: `views/suggest-campaign-three.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				})
				// url will be suggest-Advertising-Detail
				.state("index.suggest.other-info", {
					url: "/other-info",
					templateUrl: `views/suggest-campaign-four.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("index.Map-ListView", {
					url: "Map-ListView",
					templateUrl: `views/Map-ListView.html?v=${Date.now()}`,
					controller: "GmapCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						locationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/LocationService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						managementReportService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ManagementReportService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/GmapCtrl.js"
								);
							},
						],
					},
				})
				.state("index.location", {
					url: "location/:campaignId",
					templateUrl: `views/map-home.html?v=${Date.now()}`,
					// templateUrl: `views/newMap-home.html?v=${Date.now()}`,
					controller: "GmapCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						locationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/LocationService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						managementReportService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ManagementReportService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/GmapCtrl.js"
								);
							},
						],
					},
					params: {
						campaignId: { squash: true, value: null },
					},
				})
				.state("index.product-list", {
					url: "product-list",
					templateUrl: `views/product-list.html?v=${Date.now()}`,
					controller: "ProductlistCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						managementReportService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ManagementReportService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/ProductlistCtrl.js"
								);
							},
						],
					},
				})
				.state("index.shortlisted-products", {
					url: "shortlisted-products/:campaignId",
					templateUrl: `views/shortlisted-products.html?v=${Date.now()}`,
					controller: "UserProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/ProductCtrl.js"
								);
							},
						],
					},
					params: {
						campaignId: { squash: true, value: null },
					},
				})
				.state("index.campaign", {
					url: "campaign",
					templateUrl: `views/campaign.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("index.campaigns", {
					url: "campaigns",
					templateUrl: `views/campaigns.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("index.view_campaign", {
					url: "view-campaign/{campaignId}",
					templateUrl: `views/campaign.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("index.campaign-details", {
					url: "campaign-details/{campaignId}/{id}",
					templateUrl: `views/campaign-details.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				})
				/*.state("index.user-payment", {
					url: "user-payment/{campaignId}",
					templateUrl: `views/user-payment.html?v=${Date.now()}`,
					controller: "userPaymentCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/userPaymentCtrl.js"
								);
							},
						],
					},
				})*/
				// URL changed for payment method type based on online or offline
				.state("index.user-payment", {
					url: "user-payment/{campaignId}/{method}",
					templateUrl: `views/user-payment.html?v=${Date.now()}`,
					controller: "userPaymentCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/userPaymentCtrl.js"
								);
							},
						],
					},
				})
				.state("index.product-camp-details", {
					url: "product-camp-details/:productId",
					templateUrl: `views/product-camp-details.html?v=${Date.now()}`,
					controller: "CampaignCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						timezoneService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./utils/TimezoneService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("index.user-payments", {
					url: "user-payments",
					templateUrl: `views/user-payments.html?v=${Date.now()}`,
					controller: "UserPaymentCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/UserPaymentsCtrl.js"
								);
							},
						],
					},
				})
				.state("index.update-user-payments", {
					url: "update-user-payments/:id",
					templateUrl: `views/update-user-payments.html?v=${Date.now()}`,
					controller: "UserPaymentCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/UserPaymentsCtrl.js"
								);
							},
						],
					},
				})
				.state("index.profile", {
					url: "profile",
					templateUrl: `views/user-profile.html?v=${Date.now()}`,
					controller: "UserProfileCtrl",
					resolve: {
						userService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/UserService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/UserProfileCtrl.js"
								);
							},
						],
					},
				})
				.state("index.verify_email", {
					url: "verify_email/:code",
					templateUrl: `views/home.html?v=${Date.now()}`,
					controller: function (
						$scope,
						$stateParams,
						$mdDialog,
						UserService,
						toastr
					) {
						UserService.isMailVerified($stateParams.code).then(
							function (result) {
								if (result.status == 1) {
									/*$mdDialog.show({
                  templateUrl: 'views/verification-success.html',
                  fullscreen: $scope.customFullscreen,
                  clickOutsideToClose: true
                });
                */
									toastr.success(result.message);
								} else {
									toastr.error(result.message);
								}
							}
						);
						$scope.goToLogin = function () {
							$location.path("/");
							$mdDialog.show({
								templateUrl: `views/sign-in.html?v=${Date.now()}`,
								fullscreen: true,
							});
						};
					},
					resolve: {
						userService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/UserService.js"
								);
							},
						],
					},
				})
				.state("index.complete_registration", {
					url: "complete_registration/:code",
					templateUrl: `views/complete_registration.html?v=${Date.now()}`,
					controller: "UserSettingsCtrl",
					params: {
						code: { squash: true, value: null },
					},
					resolve: {
						userService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/UserService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									`./controllers/UserSettingsCtrl.js?v=${Date.now()}`
								);
							},
						],
					},
				})
				.state("index.generate_password", {
					url: "generate_password/:code",
					templateUrl: `views/reset-password.html?v=${Date.now()}`,
					controller: "UserSettingsCtrl",
					resolve: {
						userService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/UserService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									`./controllers/UserSettingsCtrl.js?v=${Date.now()}`
								);
							},
						],
					},
					params: {
						code: { squash: true, value: null },
					},
				})
				.state("index.reset-password", {
					url: "reset_password/:code",
					templateUrl: `views/reset-password.html?v=${Date.now()}`,
					controller: "UserSettingsCtrl",
					resolve: {
						userService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/UserService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									`./controllers/UserSettingsCtrl.js?v=${Date.now()}`
								);
							},
						],
					},
					params: {
						code: { squash: true, value: null },
					},
				})
				.state("index.report", {
					url: "report",
					templateUrl: `views/dashboard-reportNew.html?v=${Date.now()}`,
					controller: "buyerNewDashReportCtrl",
					// templateUrl: `views/management-report.html?v=${Date.now()}`,
					// controller: "buyerReportCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						locationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/LocationService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						managementReportService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ManagementReportService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									// `./controllers/buyerReportCtrl.js?v=${Date.now()}`
									`./controllers/buyerNewDashReportCtrl.js?v=${Date.now()}`
								);
							},
						],
					},
				})
				.state("index.advertising-marketplace-platform", {
					url: "Advertising-Marketplace-Platform",
					templateUrl: `views/advertising-marketplace-platform.html?v=${Date.now()}`,
					controller: "RegistrationCtrl",
					resolve: {
						userService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/UserService.js"
								);
							},
						],
						companyService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CompanyService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/RegistrationCtrl.js"
								);
							},
						],
					},
				})
				.state("index.buyer-product-detail", {
					url: "buyer-product-detail",
					templateUrl: `views/buyer-product-detail.html?v=${Date.now()}`,
					controller: "ProductlistCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						managementReportService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ManagementReportService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/ProductlistCtrl.js"
								);
							},
						],
					},
				})
				.state("index.contracts", {
					url: "contracts",
					templateUrl: `views/contracts.html?v=${Date.now()}`,
					controller: "AdminContractsCtrl",
					resolve: {
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminContractsCtrl.js"
								);
							},
						],
					},
				})
				.state("index.invoicing", {
					url: "invoicing",
					templateUrl: `views/invoicing.html?v=${Date.now()}`,
					controller: "AdminContractsCtrl",
					resolve: {
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminContractsCtrl.js"
								);
							},
						],
					},
				})
				.state("agency", {
					url: "/agency",
					templateUrl: `layouts/agency.html?v=${Date.now()}`,
				})
				.state("admin", {
					abstract: true,
					url: "/admin",
					templateUrl: `layouts/admin.html?v=${Date.now()}`,
					controller: "AdminMgrAppCtrl",
					resolve: {
						notificationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/NotificationService.js"
								);
							},
						],
						adminNotificationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/NotificationService.js"
								);
							},
						],
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminMgrAppCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.aboutbbi", {
					url: "/about",
					templateUrl: `views/about_bbi.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.policy", {
					url: "/policy",
					templateUrl: `views/policy_AMP.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.home", {
					url: "/home/:campSuggReqId",
					templateUrl: `views/admin/home.html?v=${Date.now()}`,
					controller: "AdminFeedsCtrl",
					resolve: {
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						helperService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/HelperService.js"
								);
							},
						],
						adminFeedsCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/FeedsCtrl.js"
								);
							},
						],
					},
					title: "Feeds",
					params: {
						campSuggReqId: { squash: true, value: null },
					},
				})
				.state("admin.suggest-products", {
					url: "/suggest-products",
					templateUrl: `views/admin/suggest-products.html?v=${Date.now()}`,
					controller: "CampaignProposalCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignProposalCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignProposalCtrl.js"
								);
							},
						],
					},
					title: "Feeds",
				})
				.state("admin.campaign", {
					url: "/campaigns",
					templateUrl: `views/admin/campaign-list.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
					title: "Campaign",
				})
				.state("admin.admincampaign", {
					url: "/admin-campaigns",
					templateUrl: `views/admin/campaign-admin-list.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
					title: "Campaign",
				})
				.state("admin.bulkupload", {
					url: "/admin-bulkupload",
					templateUrl: `views/admin/admin-bulkupload.html?v=${Date.now()}`,
					controller: "AdminBulkUploadCtrl",
					resolve: {
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						adminBulkUploadCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminBulkUploadCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.campaign-proposal-summary", {
					url: "/campaign-proposal-summary/{campaignId}/{from}",
					templateUrl: `views/admin/campaign-proposal-summary.html?v=${Date.now()}`,
					controller: "CampaignProposalCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignProposalCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignProposalCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.user-management", {
					url: "/user-management/:clientID",
					templateUrl: `views/admin/user-management.html?v=${Date.now()}`,
					controller: "UserMgmtCtrl",
					resolve: {
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						adminUserMgmtService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserMgmtService.js"
								);
							},
						],
						userMgmtCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/UserMgmtCtrl.js"
								);
							},
						],
					},
					params: {
						clientID: { squash: true, value: null },
					},
					title: "User Management",
				})
				.state("admin.contracts", {
					url: "/contracts",
					templateUrl: `views/admin/admin-contracts.html?v=${Date.now()}`,
					controller: "AdminContractsCtrl",
					resolve: {
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						adminContractsCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminContractsCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.invoicing", {
					url: "/invoicing",
					templateUrl: `views/admin/admin-invoicing.html?v=${Date.now()}`,
					controller: "AdminContractsCtrl",
					resolve: {
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						adminContractsCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminContractsCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.companies", {
					url: "/companies",
					templateUrl: `views/admin/companies.html?v=${Date.now()}`,
					controller: "CompanyCtrl",
					resolve: {
						companyService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CompanyService.js"
								);
							},
						],
						companyCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CompanyCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.hoarding-list", {
					url: "/hoarding-list",
					templateUrl: `views/admin/hoarding-list.html?v=${Date.now()}`,
					controller: "ProductCtrl",
					resolve: {
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminLocationService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						companyService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CompanyService.js"
								);
							},
						],
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/ProductCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.cloneproduct-details", {
					url: "/cloneproduct-details/:id",
					templateUrl: `views/admin/cloneproduct-details.html?v=${Date.now()}`,
					controller: "OwnerProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerProductCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.product-camp-details", {
					url: "/product-camp-details/:productId",
					templateUrl: `views/admin/product-camp-details.html?v=${Date.now()}`,
					controller: "ProductCtrl",
					resolve: {
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminLocationService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						companyService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CompanyService.js"
								);
							},
						],
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/ProductCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.formats", {
					url: "/formats",
					templateUrl: `views/admin/formats.html?v=${Date.now()}`,
					controller: "ProductCtrl",
					resolve: {
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminLocationService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						companyService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CompanyService.js"
								);
							},
						],
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/ProductCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.requested-hoardings", {
					url: "/requested-hoardings/:productId",
					templateUrl: `views/admin/requested-hoardings.html?v=${Date.now()}`,
					controller: "ProductCtrl",
					resolve: {
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminLocationService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						companyService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CompanyService.js"
								);
							},
						],
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/ProductCtrl.js"
								);
							},
						],
					},
					params: {
						productId: { squash: true, value: null },
					},
				})
				.state("admin.locations", {
					url: "/locations",
					templateUrl: `views/admin/locations.html?v=${Date.now()}`,
					controller: "AdminLocationCtrl",
					resolve: {
						adminLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminLocationService.js"
								);
							},
						],
						adminLocationCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminLocationCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.locations-country", {
					url: "/locations-country",
					templateUrl: `views/admin/location-country.html?v=${Date.now()}`,
					controller: "AdminLocationCtrl",
					resolve: {
						adminLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminLocationService.js"
								);
							},
						],
						adminLocationCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminLocationCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.locations-state", {
					url: "/locations-state",
					templateUrl: `views/admin/location-state.html?v=${Date.now()}`,
					controller: "AdminLocationCtrl",
					resolve: {
						adminLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminLocationService.js"
								);
							},
						],
						adminLocationCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminLocationCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.locations-city", {
					url: "/locations-city",
					templateUrl: `views/admin/location-city.html?v=${Date.now()}`,
					controller: "AdminLocationCtrl",
					resolve: {
						adminLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminLocationService.js"
								);
							},
						],
						adminLocationCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminLocationCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.locations-area", {
					url: "/locations-area",
					templateUrl: `views/admin/location-area.html?v=${Date.now()}`,
					controller: "AdminLocationCtrl",
					resolve: {
						adminLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminLocationService.js"
								);
							},
						],
						adminLocationCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminLocationCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.subscribers", {
					url: "/subscribers",
					templateUrl: `views/admin/subscribers.html?v=${Date.now()}`,
					controller: "subscriberCtrl",
					resolve: {
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						subscriberCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/subscriberCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.uploadedData", {
					url: "/uploadedData",
					templateUrl: `views/admin/uploadedData.html?v=${Date.now()}`,
					controller: "uploadDataCtrl",
					resolve: {
						UploadDataService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/uploadedDataService.js"
								);
							},
						],
						uploadDataCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/uploadDataCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.queries", {
					url: "/queries",
					templateUrl: `views/admin/queries.html?v=${Date.now()}`,
					controller: "customerQueriesCtrl",
					resolve: {
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						customerQueriesCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/customerQueriesCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.callcenterinfo", {
					url: "/callcenterinfo",
					templateUrl: `views/admin/callcenterinfo.html?v=${Date.now()}`,
					controller: "callCenterCtrl",
					resolve: {
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						callCenterCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/callcenterinfo.js"
								);
							},
						],
					},
				})
				.state("admin.floating-campaign", {
					url: "/floating-campaign",
					templateUrl: `views/admin/floating-campaign.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.paid-payment", {
					url: "/paid-payment",
					templateUrl: `views/admin/paid-payment.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.admin-payment", {
					url: "/admin-payment/:campaign_id",
					templateUrl: `views/admin/admin-payment.html?v=${Date.now()}`,
					controller: "AdminPaymentCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.received-payment", {
					url: "/received-payment",
					templateUrl: `views/admin/received-payment.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.paymentview-details", {
					url: "/paymentview-details/:campaignId",
					templateUrl: `views/admin/paymentview-details.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.campaign-payment-details", {
					url: "/campaign-payment-details/:campaign_id",
					templateUrl: `views/admin/campaign-payment-details.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.received-payment-details", {
					url: "/received-payment-details/:campaign_id",
					templateUrl: `views/admin/received-payment-details.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.admin-feeds", {
					url: "/admin-feeds",
					templateUrl: `views/admin/admin-feeds.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.offers", {
					url: "/offers",
					templateUrl: `views/admin/offers.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.RFP-Requset-Without-Login", {
					url: "/RFP-Requset-Without-Login",
					templateUrl: `views/admin/RFP-Requset-Without-Login.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.findForMe", {
					url: "/findForMe",
					templateUrl: `views/admin/find-for-me.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.referredby", {
					url: "/referredby",
					templateUrl: `views/admin/referredby.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.enquiries", {
					url: "/enquiries",
					templateUrl: `views/admin/enquiries.html?v=${Date.now()}`,
					controller: "EnquiriesCtrl",
					resolve: {
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						enquiriesCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/EnquiriesCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.report", {
					url: "/report",
					templateUrl: `views/admin/management-report.html?v=${Date.now()}`,
					controller: "ManagementReportCtrl",
					resolve: {
						managementReportService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ManagementReportService.js"
								);
							},
						],
						managementReportCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/ManagementReportCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.detailpage", {
					url: "/report-details/:type",
					templateUrl: `views/admin/report-details.html?v=${Date.now()}`,
					controller: "ReportsDetailPageCtrl",
					resolve: {
						managementReportService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ManagementReportService.js"
								);
							},
						],
						reportsDetailPageCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/ReportsDetailPageCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.feedback-view", {
					url: "/feedback-view",
					templateUrl: `views/admin/feedback-view.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.productfull-details", {
					url: "/productfull-details",
					templateUrl: `views/admin/productfull-details.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.add-campagin-product", {
					url: "/add-campagin-product/{campaignId}",
					templateUrl: `views/admin/add-campagin-product.html?v=${Date.now()}`,
					controller: "AdminCampaignCtrl",
					resolve: {
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminContactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/AdminContactService.js"
								);
							},
						],
						adminCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("admin.campaign-details", {
					url: "/campaign-details",
					templateUrl: `views/admin/campaign-details.html?v=${Date.now()}`,
					// controller: 'OwnerCampaignCtrl'
				})
				.state("admin.product-shortlist-campagin", {
					url: "/product-shortlist-campagin/:productId",
					templateUrl: `views/admin/product-shortlist-campagin.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})

				.state("admin.admin-notifications", {
					url: "/admin-notifications",
					templateUrl: `views/admin/admin-notifications.html?v=${Date.now()}`,
					// controller: 'AdminMgrAppCtrl'
				})

				.state("admin.reset-password", {
					url: "/reset_password/:code",
					templateUrl: `views/reset-password.html?v=${Date.now()}`,
					controller: "UserSettingsCtrl",
					resolve: {
						userService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/UserService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									`./controllers/UserSettingsCtrl.js?v=${Date.now()}`
								);
							},
						],
					},
					params: {
						code: { squash: true, value: null },
					},
				})
				.state("admin.profile", {
					url: "/profile",
					templateUrl: `views/user-profile.html?v=${Date.now()}`,
					controller: "UserProfileCtrl",
					resolve: {
						userService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/UserService.js"
								);
							},
						],
						userProfileCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/UserProfileCtrl.js"
								);
							},
						],
					},
				})
				.state("owner", {
					abstract: true,
					url: "/owner/:client_slug",
					templateUrl: `layouts/owner.html?v=${Date.now()}`,
					controller: "OwnerMngrCtrl",
					resolve: {
						ownerNotificationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/NotificationService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						notificationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/NotificationService.js"
								);
							},
						],
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						ownerMngrCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerMngrCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.aboutbbi", {
					url: "about",
					templateUrl: `views/about_bbi.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						linkCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.contracts", {
					url: "contracts",
					templateUrl: `views/owner/owner-contracts.html?v=${Date.now()}`,
					controller: "AdminContractsCtrl",
					resolve: {
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						adminContractsCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminContractsCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.invoicing", {
					url: "invoicing",
					templateUrl: `views/owner/owner-invoicing.html?v=${Date.now()}`,
					controller: "AdminContractsCtrl",
					resolve: {
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						adminContractsCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/admin/AdminContractsCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.policy", {
					url: "policy",
					templateUrl: `views/policy_AMP.html?v=${Date.now()}`,
					controller: "linkCtrl",
					resolve: {
						linkCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/linkCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.owner-notifications", {
					url: "/owner-notifications",
					templateUrl: `views/owner/owner-notifications.html?v=${Date.now()}`,
				})
				.state("owner.report", {
					url: "/report",
					templateUrl: `views/owner/management-report.html?v=${Date.now()}`,
					controller: "ownerReportCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						locationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/LocationService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						managementReportService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ManagementReportService.js"
								);
							},
						],
						ownerReportCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/ownerReportCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.bulk-upload", {
					url: "/bulk-upload",
					templateUrl: `views/owner/bulk-upload.html?v=${Date.now()}`,
					//controller: "OwnerBulkUploadCtrl",
					controller: "OwnerProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerProductCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.feeds", {
					url: "/feeds",
					templateUrl: `views/owner/feeds.html?v=${Date.now()}`,
					controller: "OwnerFeedsCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						helperService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/HelperService.js"
								);
							},
						],
						ownerFeedsCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/FeedsCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.campaigns", {
					url: "/campaigns",
					templateUrl: `views/owner/campaigns.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.bbi-campaigns", {
					url: "/bba-campaigns",
					templateUrl: `views/owner/bbi-campaigns.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.saved-campaigns", {
					url: "/saved-campaigns",
					templateUrl: `views/owner/saved-campaigns.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.campaign-details", {
					url: "/campaign-details/:campaignId/:campaignType",
					templateUrl: `views/owner/campaign-details.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.product-camp-details", {
					url: "/product-camp-details/:productId",
					templateUrl: `views/owner/product-camp-details.html?v=${Date.now()}`,
					controller: "OwnerProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerProductCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.requested-hoardings", {
					url: "/requested-hoardings",
					templateUrl: `views/owner/requested-hoardings.html?v=${Date.now()}`,
					controller: "OwnerProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerProductCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.hoarding-list", {
					url: "/hoarding-list",
					templateUrl: `views/owner/hoarding-list.html?v=${Date.now()}`,
					controller: "OwnerProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerProductCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.seller_accounts", {
					url: "/seller_accounts",
					templateUrl: `views/owner/seller_accounts.html?v=${Date.now()}`,
					controller: "OwnerProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerProductCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.my_account", {
					url: "/my_account",
					templateUrl: `views/owner/my_account.html?v=${Date.now()}`,
					controller: "OwnerProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerProductCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.add-campagin-product", {
					url: "/add-campagin-product",
					templateUrl: `views/owner/add-campagin-product.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.product-details", {
					url: "/product-details/:productId",
					templateUrl: `views/owner/product-details.html?v=${Date.now()}`,
					controller: "OwnerProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerProductCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.settings", {
					url: "/settings",
					templateUrl: `views/owner/accountsetting.html?v=${Date.now()}`,
					controller: "",
				})
				.state("owner.notifications", {
					url: "/notifications",
					templateUrl: `views/owner/owne-notifications.html?v=${Date.now()}`,
					controller: "OwnerMngrCtrl",
					resolve: {
						ownerNotificationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/NotificationService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						notificationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/NotificationService.js"
								);
							},
						],
						adminUserService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/UserService.js"
								);
							},
						],
						ownerMngrCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerMngrCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.bbisupport", {
					url: "/bbisupport",
					templateUrl: `views/owner/bbisupport.html?v=${Date.now()}`,
					controller: "feedback",
					resolve: {
						contactService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/ContactService.js"
								);
							},
						],
						feedback: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/feedbackCtrl.js"
								);
							},
						],
					},
				})
				
				.state("owner.rfp-search-criteria", {
					url: "/rfp-search-criteria",
					templateUrl: `views/owner/rfp-search-criteria.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						adminCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/CampaignService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.updatepayment", {
					url: "/updatepayment/:id",
					templateUrl: `views/owner/updatepayment.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.editproduct-details", {
					url: "/editproduct-details/:id",
					templateUrl: `views/owner/editproduct-details.html?v=${Date.now()}`,
					controller: "OwnerProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerProductCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.cloneproduct-details", {
					url: "/cloneproduct-details/:id",
					templateUrl: `views/owner/cloneproduct-details.html?v=${Date.now()}`,
					controller: "OwnerProductCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						ownerLocationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/LocationService.js"
								);
							},
						],
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						ownerProductCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/OwnerProductCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.forgotpassword", {
					url: "/forgotpassword",
					templateUrl: `views/owner/forgotpassword.html?v=${Date.now()}`,
					controller: "",
				})
				.state("owner.reset-password", {
					url: "/reset_password/:code",
					templateUrl: `views/reset-password.html?v=${Date.now()}`,
					controller: "UserSettingsCtrl",
					resolve: {
						userService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/UserService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									`./controllers/UserSettingsCtrl.js?v=${Date.now()}`
								);
							},
						],
					},
					params: {
						code: { squash: true, value: null },
					},
				})
				.state("owner.location", {
					url: "/location",
					templateUrl: `views/map-home.html?v=${Date.now()}`,
					controller: "GmapCtrl",
					resolve: {
						mapService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/MapService.js"
								);
							},
						],
						locationService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/LocationService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						managementReportService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ManagementReportService.js"
								);
							},
						],
						js: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/GmapCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.profile", {
					url: "/profile",
					templateUrl: `views/user-profile.html?v=${Date.now()}`,
					controller: "UserProfileCtrl",
					resolve: {
						userService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/UserService.js"
								);
							},
						],
						userProfileCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/UserProfileCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.resetlogin", {
					url: "/resetlogin",
					templateUrl: `views/owner/resetlogin.html?v=${Date.now()}`,
					controller: "",
				})
				.state("owner.payments", {
					url: "/payments",
					templateUrl: `views/owner/campaign-payments.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.update-payments", {
					url: "/update-payments",
					templateUrl: `views/owner/add-payment.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("owner.product-shortlist-campagin", {
					url: "/product-shortlist-campagin/:productId",
					templateUrl: `views/owner/product-shortlist-campagin.html?v=${Date.now()}`,
					controller: "OwnerCampaignCtrl",
					resolve: {
						ownerCampaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/CampaignService.js"
								);
							},
						],
						ownerProductService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/owner/ProductService.js"
								);
							},
						],
						campaignService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/CampaignService.js"
								);
							},
						],
						productService: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./services/admin/ProductService.js"
								);
							},
						],
						ownerCampaignCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/owner/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state("agency.acampagins", {
					url: "/acampagins",
					templateUrl: `views/agency/campagins.html?v=${Date.now()}`,
					controller: "agencyCampaginsCtrl",
					resolve: {
						agencyCampaginsCtrl: [
							"$ocLazyLoad",
							function ($ocLazyLoad) {
								return $ocLazyLoad.load(
									"./controllers/agency/CampaignCtrl.js"
								);
							},
						],
					},
				})
				.state('locationDmaSearch', {
					url: '/location/dma_search/:dma/:startDate/:endDate',
					templateUrl: `views/dashboard-reportNew.html?v=${Date.now()}`,
					controller: 'buyerNewDashReportCtrl'
				})

			$urlRouterProvider.when("/", "/home");
			$urlRouterProvider.when("/admin", "/admin/home");
			$urlRouterProvider.when("/owner", "/owner/:client_slug/feeds");
			// $urlRouterProvider.when('/agency', '/home');
			$urlRouterProvider.otherwise("/");

			$authProvider.baseUrl = config.apiPath;
			$authProvider.loginUrl = "/login";
			$authProvider.logoutUrl = "/logout";
			$authProvider.signupUrl = "/signup";
			$authProvider.storageType = "localStorage";
			// $authProvider.unlinkUrl = '/auth/unlink/';

			$mdAriaProvider.disableWarnings();
			$httpProvider.interceptors.push("LoadingInterceptor");
		},
	]);

/*===================
Slick Carasoul Config
====================*/
app.config([
	"slickCarouselConfig",
	function (slickCarouselConfig) {
		slickCarouselConfig.dots = true;
		slickCarouselConfig.autoplay = false;
	},
]);

/*app.config(['datepickerConfig', 'datepickerPopupConfig', function (datepickerConfig, datepickerPopupConfig) {
  datepickerConfig.startingDay = "Today";
  datepickerConfig.showWeeks = false;
  datepickerPopupConfig.datepickerPopup = "MM/dd/yyyy";
  // datepickerPopupConfig.currentText = "Now";
  // datepickerPopupConfig.clearText = "Erase";
  // datepickerPopupConfig.closeText = "Close";
}]);*/

/*========================
  Toastr Config
========================*/
app.config([
	"toastrConfig",
	function (toastrConfig) {
		angular.extend(toastrConfig, {
			autoDismiss: false,
			containerId: "toast-container",
			maxOpened: 0,
			newestOnTop: true,
			positionClass: "toast-bottom-right",
			preventDuplicates: false,
			preventOpenDuplicates: false,
			target: "body",
		});
	},
]);

app.run([
	"$rootScope",
	"$location",
	"$http",
	"$auth",
	"$mdDialog",
	"$transitions",
	"toastr",
	function (
		$rootScope,
		$location,
		$http,
		$auth,
		$mdDialog,
		$transitions,
		toastr
	) {
		$transitions.onStart({}, function (transition) {
			/*===========================================
          Restricting routes to Authenticated Users
        ===========================================*/
			var adminRoutes = [
				"admin.html",
				"admin.products",
				"admin.add-products",
				"admin.home",
				"admin.Feeds",
				"admin.offers",
				"admin.findForMe",
				"admin.campaign-suggestion",
				"admin.campaign",
				"admin.campaign-proposal-summary",
				"admin.campaign-running-summary",
				"admin.campaign-closed-summary",
				"admin.user-management",
				"admin.companies",
				"admin.hoarding-list",
				"admin.formats",
				"admin.locations",
				"admin.locations-country",
				"admin.locations-state",
				"admin.locations-city",
				"admin.locations-area",
				"admin.subscribers",
				"admin.queries",
				"admin.callcenterinfo",
				"admin.referredby",
			];
			var ownerRoutes = [
				// 'owner.home',
				"owner.feeds",
				"owner.campaigns",
				"owner.campaign-details",
				"owner.requested-hoardings",
				//'owner.suggest-products',
				"owner.hoarding-list",
				"owner.seller_accounts",
				"owner.my_account",
				"owner.product-details",
				"owner.settings",
				"owner.profile",
				"owner.payments",
				"owner.update-payments",
				"owner.rfp-search-criteria",
			];
			var requiresLogin = ["index.location", "index.report"];
			var userRoutes = [
				"index.suggest",
				"index.suggest.product-detail",
				"index.suggest.marketing-objectives",
				"index.suggest.advertising-objectives",
				"index.suggest.other-info",
				"index.update-user-payments",
				"index.user-payments",
			];
			// routes for authenticated Users
			if (_.indexOf(requiresLogin, transition.to().name) != -1) {
				if (!$auth.isAuthenticated()) {
					$rootScope.postLoginState = transition.to().name;
					$location.path("/");
					$mdDialog.show({
						templateUrl: `views/sign-in.html?v=${Date.now()}`,
						fullscreen: true,
						// clickOutsideToClose: true,
						// preserveScope: true,
						scope: $scope,
						controller: AuthCtrl,
					});
					return false;
				}
			} else if (_.indexOf(adminRoutes, transition.to().name) != -1) {
				if (!$auth.isAuthenticated()) {
					$rootScope.postLoginState = transition.to().name;
					$location.path("/");
					$mdDialog.show({
						templateUrl: `views/sign-in.html?v=${Date.now()}`,
						fullscreen: true,
						clickOutsideToClose: true,
						// preserveScope: true,
						scope: $scope,
						controller: AuthCtrl,
					});
					return false;
				} else if ($auth.getPayload().userMongo.user_type != "bbi") {
					toastr.error(
						"You don't have the rights to access this page. Please contact the owner.",
						"Error"
					);
					console.log("error....978");
					return false;
				}
			} else if (_.indexOf(ownerRoutes, transition.to().name) != -1) {
				if (!$auth.isAuthenticated()) {
					$rootScope.postLoginState = transition.to().name;
					$location.path("/");
					$mdDialog.show({
						templateUrl: `views/sign-in.html?v=${Date.now()}`,
						fullscreen: true,
						clickOutsideToClose: true,
						// preserveScope: true,
						scope: $scope,
						controller: AuthCtrl,
					});
				}
				// else if($auth.isAuthenticated() && $auth.getPayload().userMongo.user_type == 'owner'){
				//     var locationArray = $location.$$url.split('/')
				//     var lastValue = locationArray[locationArray.length-1]
				//     console.log('last value',lastValue)
				//     var ownerChildRoute = "";
				//     for(var routeOwner in ownerRoutes){
				//       var arr = ownerRoutes[routeOwner].split('.');
				//       ownerChildRoute = arr[arr.length-1]
				//       if(ownerChildRoute == lastValue){
				//         return true
				//       }
				//     }
				//     $location.path('/owner');
				// }
				else if ($auth.getPayload().userMongo.user_type != "owner") {
					toastr.error(
						"You don't have the rights to access this page. Please contact the admin.",
						"Error"
					);
					console.log("error....1010....");
					return false;
				}
			} else if (_.indexOf(userRoutes, transition.to().name) != -1) {
				if (!$auth.isAuthenticated()) {
					$rootScope.postLoginState = transition.to().name;
					$location.path("/");
					$mdDialog.show({
						templateUrl: `views/sign-in.html?v=${Date.now()}`,
						fullscreen: true,
						clickOutsideToClose: true,
						// preserveScope: true,
						scope: $scope,
						controller: AuthCtrl,
					});
				} else if ($auth.getPayload().userMongo.user_type != "basic") {
					toastr.error(
						"You don't have the rights to access this page.",
						"Error"
					);
					console.log("error....1028....");
					return false;
				}
			}

			// Get all URL parameter index.user-notifications
			$rootScope.currentTitle = transition.to().title;
			$rootScope.currStateName = transition.to().name;
			if (
				(transition.to().name == "index.location" ||
					transition.to().name == "index.reset-password" ||
					transition.to().name == "index.product-list" ||
					transition.to().name == "index.update-user-payments" ||
					transition.to().name == "index.profile" ||
					transition.to().name == "index.user-payments" ||
					transition.to().name == "index.shortlisted-products" ||
					transition.to().name == "index.user-saved-campaigns" ||
					transition.to().name == "index.campaign-details" ||
					transition.to().name == "index.campaigns" ||
					transition.to().name == "index.suggest.product-detail" ||
					transition.to().name == "index.user-notifications" ||
					transition.to().name == "index.Map-ListView") &&
				$auth.isAuthenticated()
			) {
				$rootScope.footerhide = true;
			} else {
				$rootScope.footerhide = false;
			}

			// Redirect to login page when session expired

			const currentTimeMs = new Date().getTime();
			let expTimeMs = 0;
			const payload = $auth.getPayload();
			if (payload && payload.exp) {
				expTimeMs = payload.exp * 1000;
			}
			if (
				(!expTimeMs || currentTimeMs > expTimeMs) &&
				$rootScope.isAuthenticated
			) {
				console.log("session expired");
				$rootScope.isAuthenticated = false;
				localStorage.clear();
				toastr.error("Your session has timed out, please login again");
				$location.path("/home");
			}
		});
	},
]);
