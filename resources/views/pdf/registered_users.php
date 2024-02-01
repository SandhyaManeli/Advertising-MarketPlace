<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- Bootstrap CSS -->
    
  </head>
  <style>
  .container{

	font-family: sans-serif;
  }
   .for_scroll{
    height: 393px;
    overflow: auto;
 }
	table {
    		width:100%;
			font-weight:500;
			font-size:11px;
			border-collapse: collapse;
		}
		table, th {
    		border: 1px solid black;
			padding: 0px;
			text-align: center;
		}
		table, td {
    		border: 1px solid black;
			padding: 5px;
		}
		td{
		    padding: 5px;
			text-align: center;
		}
		.border_1{
			border: none!important;
		}
		.pay_now {
		  background-color: #BC2535;
		  border: none;
		  color: white;
		  padding: 10px 32px;
		  text-align: center;
		  text-decoration: none;
		  display: inline-block;
		  font-size: 12px;
		  margin: 4px 2px;
		  cursor: pointer;
		  font-weight:500;
		}
		footer {
	        position: fixed; 
	        bottom: -20px; 
	        left: 0px; 
	        right: 0px;
	        height: 80px; 
	    }
	    .page-break {
		    page-break-before: always;
		}
  </style>
<body>
	<div class="container">
		<div class="row">
			<div class="" style="float: left;width:50%;">
				<img width="250px" src="<?php echo base_path('html'); ?>/assets/images/BBA.png" alt="" style="">
			</div>
			<div class="" style="float:right;line-height:17px;">
				<!--<h4 style="margin-bottom:0px;color:#990100;">BBA Advertising Pvt Ltd.</h4>-->
				<h4 style="margin-bottom:0px;color:#990100;">Advertising Marketplace</h4>
				<!--<p style="font-size:12px;">US West Coast 1110 112th Avenue NE <br>Suite 300C, Bellevue, WA. 98004<br>
					 Email: reach@billboardsamerica.com<br>Phone : 9550224488</p>-->
				<!--<p style="font-size:11px;">629 Terminal Way Suite 1, <br>Costa Mesa, CA 92627<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>-->
				 
				<!--<p style="font-size:11px;">28832 Via Buena Vista, <br>San Juan Capistrano CA 92675<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>-->
			</div>
		</div>
		<br />
		<br />
		<div class="row">
			<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
				<br/><br/><br/><br/>
				<h4 style="float:left;width:100%;color:#990100;letter-spacing:1px;">Users</h4>
				<br/><br/><br /> 
						
							<?php $total_records = count($user_mongo);
		                        $i = 1;
		                        $results_per_page = 15; 
		                        $number_of_pages = ceil ($total_records / $results_per_page); 
								if($user_mongo->isNotEmpty()){
		                        for ($page_no=1; $page_no <= $number_of_pages; $page_no++) {
									$pb_class = "page-break";
									if ( $page_no == 1 ) {
										$pb_class = "";
									}
									?>
							<table class="<?php echo $pb_class; ?>">
								<tr>
									<th>S No.</th>
									<th>First Name</th>
									<th>Last Name</th>
									<th>Email</th>
									<th>Phone</th> 
									<th>Company</th>
									<th>User Type</th>
									<th>Register From</th>
								</tr>
									<?php $s = 1;  while ( ($i <= $total_records) && ($s <= $results_per_page) ) {
										$index = $i-1;
											$user = $user_mongo[$index];
										?>
								<tr>
									<td style="width: auto;"><?php echo $i; ?></td>
									<td style="width: auto;word-wrap: break-word;"> <?php  echo $user['first_name'];  ?></td>
									<td style="width: auto;word-wrap: break-word;"> <?php  echo $user['last_name'];  ?></td>
									<td style="width: auto;word-wrap: break-word;"> <?php echo $user['email']; ?></td>
									<td style="width: auto;word-wrap: break-word;"> <?php echo $user['phone']; ?></td>
									<td style="width: auto;word-wrap: break-word;"> <?php echo $user['company_name']; ?></td>
									<td style="width: auto;word-wrap: break-word;"> <?php if($user['company_type'] != '') { echo $user['company_type']; }else{ echo'buyer'; } ?></td>
									<td style="width: auto;word-wrap: break-word;"> <?php if($user['register_from'] == 1){ echo 'AMP Static Home Page'; }else{ echo 'AMP Home Page'; } ?></td>
								</tr>
									<?php  $i++; $s++; } ?>
							
							
							</table>
							<footer><p style="color:#990100;">Advertising Market Place, LLC</p><p>All Rights Reserved</p></footer>
								<?php } }else{ ?>  No users data found in this week.  <?php } ?>
				<!--</div>
		</div>-->
	</div>

			</div>
		</div>	

		
	</div>
</body>
</html>
