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
	
	<!--<hr style="width:100%;">
	
	<div class="row">
		<div class="" style="float: left;width:50%;line-height:17px;">
			<h4 style="margin-bottom:10px;color:#990100;">To,</h4>
			<p style="font-size:12px;">James, <br>Zomato Pvt Ltd,<br>
				james@email.com<br>9876543210<p>
		</div>
		<div class="" style="float:right;">
			<h4 style="margin-bottom:10px;color:#990100;letter-spacing:1px;">Invoice</h4>
			<p style="font-size:12px;"><b>Invoice No:</b> #0215 / <b>Date:</b> 13-06-2019</p>
		</div>
	</div>-->
	
	<div class="row">
		<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
		<br/><br/><br/><br/>
			<br/><br/><br />
			<table>
				<tr>
				 <th>S No.</th>
					<th>Product ID</th>
					<th>Type</th>
					<th>DMA</th>
					<th>Size</th>
					<th>Direction</th>
					<th>Lighting</th>
					<th>Impressions/Per Week</th>
				</tr>
					<?php
						$i = 1;
							foreach($products as $product){ 
					?>
				<tr align="center" style="font-size: 11px;">
				    <td><?php echo $i; ?></td>
					<td><?php echo $product->siteNo; ?></td>
					<td><?php echo $product->type; ?></td>
					<!--<td><?php //echo isset($product->format_name) && !empty($product->format_name) ? $product->format_name : "";?></td>-->
					<td><?php echo $product->address; ?></td>
					<td><?php echo $product->panelSize; ?></td>
					<td><?php echo $product->direction; ?></td>
					<td><?php if($product['lighting']!=''){ ?> <?php echo $product['lighting'];  } ?></td>
					<!--<td><?php //echo ($product->lighting == 1) ? "Yes" : "No"; ?></td>-->
					<td><?php if($product['impressions']!=''){ ?> <?php echo $product['impressions'];  } ?></td>
					<!--<td><?php //echo $product->impressions; ?></td>-->
				</tr>
				 <?php
                $i++; 
							}
						?>
			</table>
		</div>
	</div>
	
	<div class="row"> 
					  <div style="padding-left: 25px;padding-top: 30px;padding-bottom:15px;">
					<p>Total number of areas covered:<strong><?php echo $areas_covered; ?></strong></p>
					<p>No. of Mediums Types used: <strong><?php echo $format_types; ?></strong>, No. of Mediums Covered: <strong><?php echo $mediums_covered; ?></strong></p>
					<p>Reach Of Audiences: <strong><?php echo $audience_reach; ?></strong>/week approx. (<strong><?php echo $audience_reach * 4; ?></strong>/Month approx.)</p>
					<p>Repeated Audiences: <strong><?php echo $repeated_audience; ?></strong>/week approx. (<strong><?php echo $repeated_audience * 4; ?></strong>/Month approx.)</p>
				</div>
		</div>

		<div class="row">
			
			
				<?php
					$j = 0;
					foreach ($products as $product) {
						$rateCard=$product['rateCard'];
						//$prod_addr = isset($product->address) && !empty($product->address) ? $product->address : ""; 
						$prod_addr = isset($product->addressone) && !empty($product->addressone) ? $product->addressone : "";
						?>
						<!--<p style="text-align:center;"><?php //echo ($j + 1) . ". " .ucfirst($prod_addr); ?> </p>--> 
						<h4 style="margin-bottom:10px;color:#990100;">Product Images</h4>
				<?php
                    if (is_array($product->image)) {
                        foreach ($product->image as $k => $v) {
                            $img_src = base_path('html') . $v;
                            ?>
								<p>
									<img style="width:400px;height:400px;float:right;" src="<?php echo $img_src; ?>">  
								</p>
                            <?php
                        }
                    } else {
                        $img_src = base_path('html') . $product->image;
                        ?>
						<p>
							 <img style="width:400px;height:400px;float:right;" src="<?php echo $img_src; ?>"/>  
						</p>
                        <?php
                    }
                    ?>

				<?php
					$j++;
				}
				?>
		</div>
	<!--<p class="mt-3 text-center">The price provided in this Quotation has been tailor-made for your requirements.</p>--> 
	<!--<p class="">For further queries, Contact <b style="color:#990100;">[ Chanikya, CEO, Call: 9550224488 ]</b></p>-->
	
	<!--<p style="color:#990100;">BBI Advertising Pvt Ltd.</p>--> 
	<p style="color:#990100;">Advertising Marketplace, LLC</p>
	<p>All Rights Reserved</p>
	
</div>
</body>
</html>
