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
				<h4 style="text-align:center;;width:100%;color:#990100;letter-spacing:1px;font-size: 15px;">Bulk Upload Data</h4>
				<div style="width:100%;background-color:#cc0000;letter-spacing:1px; margin-bottom:8px; padding:1px 5px;"><b>Errors with Products</b></div>
				<table style="padding:0px">
					<tr>
						<th>S No.</th> 
						<th style="text-align: left;padding:5px;">Row No</th>
						<th style="text-align: left;padding:5px;">Title</th>
						<th style="text-align: left;padding:5px;">Errors</th>
					</tr>
					<?php
						$i = 1;
						foreach ($errors as $key => $errors) {
							$row_first = @explode('-',$errors);
							$row_num = @explode(' ',$row_first[0]);
							?>
							<tr align="center" style="font-size: 10px;">
								<td><?php echo $i; ?></td>
								<td style="text-align: left;"><?php echo $row_num[1]; ?></td>
								<td style="text-align: left;"><?php echo $errors_pdf[$row_num[1]]; ?></td>
								<td style="text-align: left;"><?php echo $errors; ?></td>
							</tr>
							<?php
							$i++;
						}
						?>
					</table>
				</div>
				<br/><br/><br />
				<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
				
					<div style="width:100%;background-color:rgba(67,171,55,1);letter-spacing:1px; margin-bottom:8px; padding:1px 5px"><b>Uploaded Products</b></div>
				<table style="padding:0px">
					<tr>
						<th>S No.</th> 
						<th style="text-align: left;padding:5px;">Row No</th>
						<th style="text-align: left;padding:5px;">Title</th>
					</tr>
					<?php
						$i = 1;
						foreach ($success as $key => $success) {
							?>
							<tr align="center" style="font-size: 10px;">
								<td><?php echo $i; ?></td>
								<td style="text-align: left;"><?php echo $key; ?></td>
								<td style="text-align: left;"><?php echo $success['title']; ?></td>
							</tr>
							<?php
							$i++;
						}
						?>
					</table>
			</div>
			</div>
		</div>	
	</div>
</body>
</html>
