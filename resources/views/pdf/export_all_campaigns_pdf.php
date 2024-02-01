<!DOCTYPE html>
<html>
<head>
<title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
    body { height: 100% !important; margin: 0 !important; padding: 0 !important; }

    body{
      background-color: #f4f4f4; 
      margin: 0 !important; 
      padding: 0 !important; 
      width:720px;
    }

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
    @media screen and (max-width:800px){
      h1 {
          font-size: 32px !important;
          line-height: 32px !important;
      }
    }
    th{text-align:center;}

    /* ANDROID CENTER FIX */
    div[style*="margin: 16px 0;"] { margin: 0 !important; }
 
    .page-break {
      page-break-after: always;
    }

  </style>
</head>


<body>

  <!-- <div style="background:#2196f3; margin:auto; padding: 0px 10px 0px 10px; width:720px;">
    <div style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: 'Roboto', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;background:#ffffff;border-bottom: 1px solid #E0E0E0;">
      <img src="http://localhost:8001/assets/images/logo.jpg" style="margin:auto;display:block;">
    </div>
  </div> -->
  <div style="width:680px;background:#fff; margin:auto;padding: 0px 10px 0px 10px;">
    <div style="width:200px; height:70px; margin:25px auto; background:#000;">
        <!--<img src="<?php echo base_path('html'); ?>/assets/images/logo.jpg" style="width:200px; height:70px;margin:auto;" alt="Billboards India"/>-->
	<img src="<?php echo base_path('html'); ?>/assets/images/Logo.png" style="width:200px; height:70px;margin:auto;" alt="AMP"/>
      </div>
    <div style="padding-top:30px;padding-left:20px;padding-right:20px;background:#fff;">
      <?php
        foreach($all_campaign_report as $campaign_report){
      ?>
        <div style="margin-top:20px; margin-bottom:15px;">
          <p style="margin: 0;font-weight: bold">Campaign Name: <?php echo $campaign_report['campaign']->name; ?></p><br>  
          
          <table class="table table-responsive" style="margin-bottom: 0px;box-shadow:2px -2px 30px #ede9e9; #ede9e9;border: 2px solid #E0E0E0;padding-left: 30px;padding-right: 30px;padding-top: 20px;">
            <thead>
              <tr style="font-size: 12px;">
                <th>Tab ID</th>
                <th>Sitetype</th>
                <th>Address</th>
                <th>Size</th>
                <th>Direction</th>
                <th>Lighting</th>
                <th>Impressions / Per Week</th>
              </tr>
            </thead>
            <tbody>
              <?php
                foreach($campaign_report['products'] as $product){
              ?>
                  <tr align="center" style="font-size: 11px;">
                    <td><?php echo $product->siteNo; ?></td>
                    <td><?php echo $product->format_name; ?></td>
                    <td><?php echo $product->address; ?></td>
                    <td><?php echo $product->panelSize; ?></td>
                    <td><?php echo $product->direction; ?></td>
                    <td><?php echo ($product->lighting == 1) ? "Yes" : "No"; ?></td>
                    <td><?php echo $product->impressions; ?></td>
                  </tr>
              <?php 
                }
              ?>
            </tbody>
          </table>
          <div style="padding-left: 25px;padding-top: 30px;padding-bottom:15px;">
            <p>Total number of areas covered:<strong><?php echo $campaign_report['areas_covered']; ?></strong></p>
            <p>No. of Mediums Types used: <strong><?php echo $campaign_report['format_types']; ?></strong>, No. of Mediums Covered: <strong><?php echo $campaign_report['mediums_covered']; ?></strong></p>
            <p>Reach Of Audiences: <strong><?php echo $campaign_report['audience_reach']; ?></strong>/week approx. (<strong><?php echo $campaign_report['audience_reach'] * 4; ?></strong>/Month approx.)</p>
            <p>Repeated Audiences: <strong><?php echo $campaign_report['repeated_audience']; ?></strong>/week approx. (<strong><?php echo $campaign_report['repeated_audience'] * 4; ?></strong>/Month approx.)</p>
          </div>
        </div>
      <?php
        }
      ?>
    </div>
  </div>

  <!-- <div style="background:#ffffff;padding-left:30px; padding-top: 40px;padding-bottom: 30px; color: #666666; font-family: 'Roboto', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;margin-top=-15px;">
    <p style="font-weight: bold;">Thanks & Regards</p>
    <!--<p style="font-weight: bold;">Billboards India Team</p>-->
    <p style="font-weight: bold;">AMP Team</p>
  </div> -->

</body>
</html>
