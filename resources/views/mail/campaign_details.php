<!DOCTYPE html>
<html>
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
  	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

		<style type="text/css">
			/* FONTS */
			@media screen {
				@font-face {
					font-family: 'Roboto';
					font-style: normal;
					font-weight: 400;
					src: local('Roboto Regular'), local('Roboto-Regular'), url(https://fonts.gstatic.com/s/Roboto/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format('woff');
				}
				
				@font-face {
					font-family: 'Roboto';
					font-style: normal;
					font-weight: 700;
					src: local('Roboto Bold'), local('Roboto-Bold'), url(https://fonts.gstatic.com/s/Roboto/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format('woff');
				}
				
				@font-face {
					font-family: 'Roboto';
					font-style: italic;
					font-weight: 400;
					src: local('Roboto Italic'), local('Roboto-Italic'), url(https://fonts.gstatic.com/s/Roboto/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format('woff');
				}
				
				@font-face {
					font-family: 'Roboto';
					font-style: italic;
					font-weight: 700;
					src: local('Roboto Bold Italic'), local('Roboto-BoldItalic'), url(https://fonts.gstatic.com/s/Roboto/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format('woff');
				}
			}
			
			/* CLIENT-SPECIFIC STYLES */
			body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
			table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
			img { -ms-interpolation-mode: bicubic; }

			/* RESET STYLES */
			img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
			table { border-collapse: collapse !important; }
			body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }

			/* iOS BLUE LINKS */
			a[x-apple-data-detectors] {
					color: inherit !important;
					text-decoration: none !important;
					font-size: inherit !important;
					font-family: inherit !important;
					font-weight: inherit !important;
					line-height: inherit !important;
			}
			
			/* MOBILE STYLES */
			@media screen and (max-width:600px){
				h1 {
						font-size: 32px !important;
						line-height: 32px !important;
				}
			}
			
			th{text-align:center;}
			
			/* ANDROID CENTER FIX */
			div[style*="margin: 16px 0;"] { margin: 0 !important; }
		</style>
	</head>
	
	
	<body style="margin: 0 !important; padding: 0 !important;">
		<h3>&nbsp;&nbsp;Hi <?php echo $receiver_name; ?>,</h3>
		<br/>
		&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $sender_email; ?></span> has shared Campaign Details with you, see below PDF For more information.
		<br/><br/>
		<!--&nbsp;&nbsp;&nbsp;&nbsp;To create your own list and search for billboards, visit: <a href="http://billboardsindia.com/" style="color: cornflowerblue;font-size: 12px;">www.billboardsindia.com</a>-->
		&nbsp;&nbsp;&nbsp;&nbsp;To create your own list and search for Advertising Marketplace, visit: <a href="<?php echo config("app.client_app_path"); ?>" style="color: cornflowerblue;font-size: 12px;">www.advertisingmarketplace.com</a>
		<br/>
		<br/>
		<p style="margin: 0;font-weight: bold;">&nbsp;&nbsp;Thanks & Regards</p>
		<!--<p style="margin: 0;font-weight: bold;">&nbsp;&nbsp;Billboards India Team</p>-->
		<p style="margin: 0;font-weight: bold;">&nbsp;&nbsp;AMP Team</p>
	</body>
</html>
