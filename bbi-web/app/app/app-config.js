var env = 1;
var config = {};

switch (env) {
	case 1: //Dev Environment
		config.serverUrl = "http://54.163.252.149:8080";
		config.apiPath = "http://54.163.252.149:8080/api";
		config.apiPathES = "http://52.21.230.182:5000/es";
		config.pusherApiKey = "e47e6f2d30bf3fe9f6c7";
		config.pusherCluster = "ap2";
		config.mobileUrl = "http://54.163.252.149:8080";
		break;
	case 2: //QA Environment
		config.serverUrl = "http://52.21.230.182:8080";
		config.apiPath = "http://52.21.230.182:8080/api";
		config.apiPathES = "http://52.21.230.182:5000/es";
		config.pusherApiKey = "e47e6f2d30bf3fe9f6c7";
		config.pusherCluster = "ap2";
		config.mobileUrl = "http://52.21.230.182:8080";
		break;
	case 3:   //Production Environment
		config.serverUrl = "advertismentmarketplace.com";
		config.apiPath = "advertismentmarketplace.com/api";
		config.apiPathES = "advertismentmarketplace.com/es";
		config.pusherApiKey = "e47e6f2d30bf3fe9f6c7";
		config.pusherCluster = "ap2";
		config.mobileUrl = "advertismentmarketplace.com";
    break;
}

app.constant("config", {
	serverUrl: config.serverUrl,
	apiPath: config.apiPath,
	apiPathES: config.apiPathES,
	pusherApiKey: config.pusherApiKey,
	pusherCluster: config.pusherCluster,
});
